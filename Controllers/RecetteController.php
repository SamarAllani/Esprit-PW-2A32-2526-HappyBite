<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Models/Recette.php';

class RecetteController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /** @return array<int, array<string, mixed>> */
    public function listRecettes(): array
    {
        $sql = 'SELECT * FROM recette ORDER BY nom ASC';
        return $this->pdo->query($sql)->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function rechercherRecettes(string $motCle): array
    {
        if ($motCle === '') {
            return $this->listRecettes();
        }
        $stmt = $this->pdo->prepare(
            'SELECT * FROM recette WHERE nom LIKE :m OR description LIKE :m2 ORDER BY nom ASC'
        );
        $like = '%' . $motCle . '%';
        $stmt->execute(['m' => $like, 'm2' => $like]);
        return $stmt->fetchAll();
    }

    /**
     * Recettes sans produit contenant l’allergène, budget = somme des prix des produits de la recette.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rechercherRecettesIntelligentes(
        string $motCle,
        string $allergie,
        string $objectif,
        $budget
    ): array {
        $budgetNum = is_numeric($budget) ? (float) $budget : 0.0;

        $sql = 'SELECT r.* FROM recette r WHERE 1=1';
        $params = [];

        if ($motCle !== '') {
            $sql .= ' AND (r.nom LIKE :mc OR r.description LIKE :mc2)';
            $like = '%' . $motCle . '%';
            $params['mc'] = $like;
            $params['mc2'] = $like;
        }

        if ($allergie !== '') {
            $sql .= ' AND r.id_recette NOT IN (
                SELECT rp.id_recette FROM recette_produit rp
                INNER JOIN produit p ON p.id_produit = rp.id_produit
                WHERE p.allergene LIKE :alg
            )';
            $params['alg'] = '%' . $allergie . '%';
        }

        if (stripos($objectif, 'perte') !== false) {
            $sql .= ' AND r.calories <= 800';
        }

        if ($budgetNum > 0) {
            $sql .= ' AND r.id_recette IN (
                SELECT rp.id_recette FROM recette_produit rp
                INNER JOIN produit p ON p.id_produit = rp.id_produit
                GROUP BY rp.id_recette
                HAVING COALESCE(SUM(p.prix), 0) <= :bud
            )';
            $params['bud'] = $budgetNum;
        }

        $sql .= ' ORDER BY r.nom ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|false */
    public function showRecetteDetails(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM recette WHERE id_recette = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** @return array<int, array<string, mixed>> */
    public function getProduitsByRecette(int $idRecette): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.id_produit, p.nom, p.prix, p.calories
             FROM produit p
             INNER JOIN recette_produit rp ON rp.id_produit = p.id_produit
             WHERE rp.id_recette = ?
             ORDER BY p.nom ASC'
        );
        $stmt->execute([$idRecette]);
        return $stmt->fetchAll();
    }

    /** @param array<int|string, mixed> $produitsSelectionnes */
    public function calculerCaloriesRecette(array $produitsSelectionnes): int
    {
        $ids = array_values(array_filter(array_map('intval', $produitsSelectionnes)));
        if ($ids === []) {
            return 0;
        }
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(calories), 0) FROM produit WHERE id_produit IN ($ph)");
        $stmt->execute($ids);
        return (int) $stmt->fetchColumn();
    }

    public function addRecette(Recette $r): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO recette (nom, description, calories) VALUES (?, ?, ?)'
        );
        $stmt->execute([$r->getNom(), $r->getDescription(), $r->getCalories()]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<int|string, mixed> $produitsSelectionnes */
    public function ajouterProduitsRecette(int $idRecette, array $produitsSelectionnes): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO recette_produit (id_recette, id_produit) VALUES (?, ?)'
        );
        foreach ($produitsSelectionnes as $pid) {
            $id = (int) $pid;
            if ($id > 0) {
                $stmt->execute([$idRecette, $id]);
            }
        }
    }

    public function deleteRecette(int $id): void
    {
        $this->pdo->prepare('DELETE FROM recette_produit WHERE id_recette = ?')->execute([$id]);
        $this->pdo->prepare('DELETE FROM recette WHERE id_recette = ?')->execute([$id]);
    }
}
