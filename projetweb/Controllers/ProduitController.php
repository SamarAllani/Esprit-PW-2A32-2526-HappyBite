<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Models/Produit.php';

class ProduitController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    private function baseSelect(): string
    {
        return 'SELECT p.*, c.nom AS nom_categorie
                FROM produit p
                INNER JOIN categorie c ON c.id_categorie = p.id_categorie';
    }

    /** @return array<int, array<string, mixed>> */
    public function listProduits(): array
    {
        $sql = $this->baseSelect() . ' ORDER BY p.nom ASC';
        return $this->pdo->query($sql)->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function rechercherProduits(string $motCle, string $idCategorie): array
    {
        $sql = $this->baseSelect() . ' WHERE 1=1';
        $params = [];
        if ($motCle !== '') {
            $sql .= ' AND p.nom LIKE :mc';
            $params['mc'] = '%' . $motCle . '%';
        }
        if ($idCategorie !== '') {
            $sql .= ' AND p.id_categorie = :ic';
            $params['ic'] = (int) $idCategorie;
        }
        $sql .= ' ORDER BY p.nom ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Filtre simple : allergène évité, prix ≤ budget, objectif perte de poids → calories modérées.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rechercherProduitsIntelligents(
        string $motCle,
        string $idCategorie,
        string $allergie,
        string $objectif,
        $budget
    ): array {
        $sql = $this->baseSelect() . ' WHERE 1=1';
        $params = [];

        if ($motCle !== '') {
            $sql .= ' AND p.nom LIKE :mc';
            $params['mc'] = '%' . $motCle . '%';
        }
        if ($idCategorie !== '') {
            $sql .= ' AND p.id_categorie = :ic';
            $params['ic'] = (int) $idCategorie;
        }
        if ($allergie !== '') {
            $sql .= ' AND (p.allergene IS NULL OR p.allergene = \'\' OR p.allergene NOT LIKE :alg)';
            $params['alg'] = '%' . $allergie . '%';
        }
        $budgetNum = is_numeric($budget) ? (float) $budget : 0.0;
        if ($budgetNum > 0) {
            $sql .= ' AND p.prix <= :bud';
            $params['bud'] = $budgetNum;
        }
        if (stripos($objectif, 'perte') !== false) {
            $sql .= ' AND (p.calories IS NULL OR p.calories <= 500)';
        }

        $sql .= ' ORDER BY p.prix ASC, p.calories ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|false */
    public function showProduitDetails(int $id)
    {
        $stmt = $this->pdo->prepare($this->baseSelect() . ' WHERE p.id_produit = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** @return array<string, mixed>|false */
    public function getProduitById(int $id)
    {
        $stmt = $this->pdo->prepare($this->baseSelect() . ' WHERE p.id_produit = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addProduit(Produit $p): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO produit (nom, prix, image, allergene, benefices, calories, date_ajout, id_utilisateur, id_categorie)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $p->getNom(),
            $p->getPrix(),
            $p->getImage(),
            $p->getAllergene(),
            $p->getBenefices(),
            $p->getCalories(),
            $p->getDateAjout(),
            $p->getIdUtilisateur(),
            $p->getIdCategorie(),
        ]);
    }

    public function updateProduit(Produit $p, int $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE produit SET nom = ?, prix = ?, image = ?, allergene = ?, benefices = ?, calories = ?,
             date_ajout = ?, id_utilisateur = ?, id_categorie = ? WHERE id_produit = ?'
        );
        $stmt->execute([
            $p->getNom(),
            $p->getPrix(),
            $p->getImage(),
            $p->getAllergene(),
            $p->getBenefices(),
            $p->getCalories(),
            $p->getDateAjout(),
            $p->getIdUtilisateur(),
            $p->getIdCategorie(),
            $id,
        ]);
    }

    public function deleteProduit(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM produit WHERE id_produit = ?');
        $stmt->execute([$id]);
    }
}
