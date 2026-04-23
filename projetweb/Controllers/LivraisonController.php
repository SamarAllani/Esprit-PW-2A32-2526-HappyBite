<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class LivraisonController
{
    private PDO $pdo;

    /** @var string|null cache du nom de colonne date dans `livraison` */
    private static ?string $colonneDate = null;

    private const STATUT_PREPARATION = 'En préparation';
    private const STATUT_EN_COURS = 'En cours';
    private const STATUT_LIVREE = 'Livrée';
    private const STATUT_ANNULEE = 'Annulée';

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Détecte la colonne de date réelle (SHOW COLUMNS, pas INFORMATION_SCHEMA : plus fiable).
     */
    private function nomColonneDateLivraison(): string
    {
        if (self::$colonneDate !== null) {
            return self::$colonneDate;
        }

        $stmt = $this->pdo->query('SHOW COLUMNS FROM livraison');
        if ($stmt === false) {
            throw new RuntimeException('Impossible de lire la structure de la table livraison.');
        }
        $fields = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if ($fields === []) {
            throw new RuntimeException('Table livraison introuvable ou vide.');
        }

        $priorite = ['livraison_date', 'date_prevue', 'date_livraison', 'date'];
        foreach ($priorite as $nom) {
            if (in_array($nom, $fields, true)) {
                self::$colonneDate = $nom;
                return self::$colonneDate;
            }
        }

        foreach ($fields as $nom) {
            if (stripos((string) $nom, 'date') !== false) {
                self::$colonneDate = (string) $nom;
                return self::$colonneDate;
            }
        }

        throw new RuntimeException(
            'Table livraison : aucune colonne date reconnue. Colonnes trouvées : ' . implode(', ', $fields)
        );
    }

    /** Valeur date pour affichage (ligne SELECT * FROM livraison). */
    public static function extraireDatePourAffichage(array $row): string
    {
        foreach (['livraison_date', 'date_prevue', 'date_livraison', 'date'] as $k) {
            if (array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') {
                return (string) $row[$k];
            }
        }
        foreach ($row as $k => $v) {
            if (is_string($k) && stripos($k, 'date') !== false && $v !== null && $v !== '') {
                return (string) $v;
            }
        }
        return '';
    }

    /**
     * date = aujourd’hui + 5 jours, statut par défaut.
     */
    public function creerEtLierCommande(int $idCommande, string $statut = 'En préparation'): int
    {
        $date = (new DateTimeImmutable('today'))->modify('+5 days')->format('Y-m-d');
        $colDate = $this->nomColonneDateLivraison();

        $this->pdo->beginTransaction();
        try {
            $sql = 'INSERT INTO livraison (`' . $colDate . '`, statut) VALUES (?, ?)';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$date, $statut]);
            $idLivraison = (int) $this->pdo->lastInsertId();

            $stmtU = $this->pdo->prepare(
                'UPDATE commande SET id_livraison = ? WHERE id_commande = ?'
            );
            $stmtU->execute([$idLivraison, $idCommande]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return $idLivraison;
    }

    /** @return array<string, mixed>|null */
    public function getLivraisonById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM livraison WHERE id_livraison = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $newStatut = $this->statutAutomatiqueDepuisDate($row);
        if ($newStatut !== null && (string) ($row['statut'] ?? '') !== $newStatut) {
            $this->setStatutLivraison($id, $newStatut);
            $row['statut'] = $newStatut;
        }

        return $row;
    }

    public function supprimerLivraison(int $idLivraison): void
    {
        $stmt = $this->pdo->prepare('UPDATE commande SET id_livraison = NULL WHERE id_livraison = ?');
        $stmt->execute([$idLivraison]);

        $stmtD = $this->pdo->prepare('DELETE FROM livraison WHERE id_livraison = ?');
        $stmtD->execute([$idLivraison]);
    }

    public function annulerLivraison(int $idLivraison): void
    {
        $this->setStatutLivraison($idLivraison, self::STATUT_ANNULEE);
    }

    /** @return array<int, array<string, mixed>> */
    public function listLivraisons(): array
    {
        $rows = $this->pdo->query('SELECT * FROM livraison ORDER BY id_livraison DESC')->fetchAll(PDO::FETCH_ASSOC);
        if ($rows === []) {
            return [];
        }

        foreach ($rows as &$row) {
            $id = (int) ($row['id_livraison'] ?? 0);
            if ($id < 1) {
                continue;
            }
            $newStatut = $this->statutAutomatiqueDepuisDate($row);
            if ($newStatut !== null && (string) ($row['statut'] ?? '') !== $newStatut) {
                $this->setStatutLivraison($id, $newStatut);
                $row['statut'] = $newStatut;
            }
        }
        unset($row);

        return $rows;
    }

    public function updateLivraison(int $idLivraison, string $dateYmd, string $statut): void
    {
        $col = $this->nomColonneDateLivraison();
        $sql = 'UPDATE livraison SET `' . $col . '` = ?, statut = ? WHERE id_livraison = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$dateYmd, $statut, $idLivraison]);
    }

    private function setStatutLivraison(int $idLivraison, string $statut): void
    {
        $stmt = $this->pdo->prepare('UPDATE livraison SET statut = ? WHERE id_livraison = ?');
        $stmt->execute([$statut, $idLivraison]);
    }

    /**
     * Statut auto basé sur `livraison_date` :
     * - Annulée : ne change jamais
     * - Livrée : si date livraison <= aujourd’hui
     * - En cours : si on est à J-2 (ou après) avant la date prévue
     * - En préparation : sinon
     *
     * @return string|null nouveau statut (ou null si pas calculable)
     */
    private function statutAutomatiqueDepuisDate(array $row): ?string
    {
        $statutActuel = trim((string) ($row['statut'] ?? ''));
        if ($statutActuel === self::STATUT_ANNULEE) {
            return null;
        }

        $rawDate = self::extraireDatePourAffichage($row);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
            return null;
        }

        $dateLiv = DateTimeImmutable::createFromFormat('Y-m-d', $rawDate);
        if (!$dateLiv instanceof DateTimeImmutable) {
            return null;
        }

        $today = new DateTimeImmutable('today');
        if ($today >= $dateLiv) {
            return self::STATUT_LIVREE;
        }

        $twoDaysBefore = $dateLiv->modify('-2 days');
        if ($today >= $twoDaysBefore) {
            return self::STATUT_EN_COURS;
        }

        return self::STATUT_PREPARATION;
    }
}
