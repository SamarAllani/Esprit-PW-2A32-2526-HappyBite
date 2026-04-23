<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class CommandeController
{
    private PDO $pdo;

    /**
     * Cached per request: which datetime column stores order creation (`date` or `date_commande`), or null.
     *
     * @var string|null
     */
    private static ?string $commandeDateColumnName = null;

    private static bool $commandeDateColumnNameResolved = false;

    public const FRAIS_LIVRAISON = 2.00;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * @param array<int, array{id_produit: int, quantite: int, prix_unitaire: float, nom: string}> $lignes
     * @return array{id_commande: int, noms_produits: string, total: float}
     */
    public function creerCommandeDepuisPanier(array $lignes): array
    {
        if ($lignes === []) {
            throw new InvalidArgumentException('Panier vide');
        }

        $sousTotal = 0.0;
        foreach ($lignes as $l) {
            $sousTotal += (float) $l['prix_unitaire'] * (int) $l['quantite'];
        }
        $total = round($sousTotal + self::FRAIS_LIVRAISON, 2);

        $noms = array_map(static fn (array $l): string => (string) $l['nom'], $lignes);
        $nomsProduits = implode(', ', $noms);

        $this->pdo->beginTransaction();
        try {
            $dateCol = $this->resolveCommandeDateColumnName();
            if ($dateCol !== null) {
                $colSql = $dateCol === 'date' ? '`date`' : 'date_commande';
                $stmt = $this->pdo->prepare(
                    "INSERT INTO commande ({$colSql}, total, modePaiement, reduction, id_livraison)
                     VALUES (NOW(), ?, NULL, 0, NULL)"
                );
            } else {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO commande (total, modePaiement, reduction, id_livraison)
                     VALUES (?, NULL, 0, NULL)'
                );
            }
            $stmt->execute([$total]);
            $idCommande = (int) $this->pdo->lastInsertId();

            $stmtL = $this->pdo->prepare(
                'INSERT INTO commande_produit (id_commande, id_produit, quantite, prix_unitaire)
                 VALUES (?, ?, ?, ?)'
            );
            foreach ($lignes as $l) {
                $stmtL->execute([
                    $idCommande,
                    (int) $l['id_produit'],
                    (int) $l['quantite'],
                    round((float) $l['prix_unitaire'], 2),
                ]);
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return [
            'id_commande' => $idCommande,
            'noms_produits' => $nomsProduits,
            'total' => $total,
        ];
    }

    public function finaliserCommande(int $idCommande, string $modePaiement, float $reduction): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE commande SET modePaiement = ?, reduction = ? WHERE id_commande = ?'
        );
        $stmt->execute([$modePaiement, $reduction, $idCommande]);
    }

    /** @return array<string, mixed>|null */
    public function getCommandeById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM commande WHERE id_commande = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function supprimerCommande(int $idCommande): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM commande WHERE id_commande = ?');
        $stmt->execute([$idCommande]);
    }

    /** @return array<int, array<string, mixed>> */
    public function listCommandes(): array
    {
        $sql = 'SELECT * FROM commande ORDER BY id_commande DESC';
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reconstruit la liste des noms depuis commande_produit (pour affichage).
     */
    public function getNomsProduitsCommande(int $idCommande): string
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.nom FROM commande_produit cp
             INNER JOIN produit p ON p.id_produit = cp.id_produit
             WHERE cp.id_commande = ?
             ORDER BY cp.id_commande_produit ASC'
        );
        $stmt->execute([$idCommande]);
        $noms = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return implode(', ', $noms);
    }

    /**
     * @return 'date'|'date_commande'|null
     */
    private function resolveCommandeDateColumnName(): ?string
    {
        if (self::$commandeDateColumnNameResolved) {
            return self::$commandeDateColumnName;
        }
        self::$commandeDateColumnNameResolved = true;
        $stmt = $this->pdo->query(
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'commande'
               AND COLUMN_NAME IN ('date', 'date_commande')"
        );
        if ($stmt === false) {
            self::$commandeDateColumnName = null;
            return null;
        }
        $names = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('date', $names, true)) {
            self::$commandeDateColumnName = 'date';
            return 'date';
        }
        if (in_array('date_commande', $names, true)) {
            self::$commandeDateColumnName = 'date_commande';
            return 'date_commande';
        }
        self::$commandeDateColumnName = null;
        return null;
    }
}
