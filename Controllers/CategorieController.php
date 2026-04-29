<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Models/Categorie.php';

class CategorieController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /** @return Categorie[] */
    public function listCategories(): array
    {
        $stmt = $this->pdo->query('SELECT id_categorie, nom, description FROM categorie ORDER BY nom ASC');
        $rows = $stmt->fetchAll();
        $list = [];
        foreach ($rows as $r) {
            $list[] = new Categorie(
                (string) $r['nom'],
                (string) ($r['description'] ?? ''),
                (int) $r['id_categorie']
            );
        }
        return $list;
    }

    /** @return Categorie[] */
    public function rechercherCategories(string $motCle): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_categorie, nom, description FROM categorie WHERE nom LIKE :m ORDER BY nom ASC'
        );
        $stmt->execute(['m' => '%' . $motCle . '%']);
        $rows = $stmt->fetchAll();
        $list = [];
        foreach ($rows as $r) {
            $list[] = new Categorie(
                (string) $r['nom'],
                (string) ($r['description'] ?? ''),
                (int) $r['id_categorie']
            );
        }
        return $list;
    }

    public function showCategorie(int $id): ?Categorie
    {
        $stmt = $this->pdo->prepare('SELECT id_categorie, nom, description FROM categorie WHERE id_categorie = ?');
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) {
            return null;
        }
        return new Categorie(
            (string) $r['nom'],
            (string) ($r['description'] ?? ''),
            (int) $r['id_categorie']
        );
    }

    public function addCategorie(Categorie $c): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO categorie (nom, description) VALUES (?, ?)');
        $stmt->execute([$c->getNom(), $c->getDescription()]);
    }

    public function updateCategorie(Categorie $c, int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE categorie SET nom = ?, description = ? WHERE id_categorie = ?');
        $stmt->execute([$c->getNom(), $c->getDescription(), $id]);
    }

    public function deleteCategorie(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM categorie WHERE id_categorie = ?');
        $stmt->execute([$id]);
    }
}
