<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Models/Commentaire.php';

class CommentaireController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Crée un nouveau commentaire
     * Retourne l'ID du commentaire inséré ou false en cas d'erreur.
     */
    public function create(string $contenu, int $post_id)
    {
        try {
            $query = "INSERT INTO Commentaire (contenu, dateCommentaire, post_id) 
                      VALUES (:contenu, NOW(), :post_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':contenu', $contenu);
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return (int) $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Récupère tous les commentaires d'un post
     */
    public function getByPostId(int $post_id): array
    {
        try {
            $query = "SELECT * FROM Commentaire WHERE post_id = :post_id ORDER BY dateCommentaire ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Récupère un commentaire par son ID
     */
    public function getById(int $id): ?array
    {
        try {
            $query = "SELECT * FROM Commentaire WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Met à jour un commentaire
     */
    public function update(int $id, string $contenu): bool
    {
        try {
            $query = "UPDATE Commentaire SET contenu = :contenu WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':contenu', $contenu);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Supprime un commentaire
     */
    public function delete(int $id): bool
    {
        try {
            $query = "DELETE FROM Commentaire WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Supprime tous les commentaires d'un post
     */
    public function deleteByPostId(int $post_id): bool
    {
        try {
            $query = "DELETE FROM Commentaire WHERE post_id = :post_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Récupère tous les commentaires
     */
    public function getAll(): array
    {
        try {
            $query = "SELECT * FROM Commentaire ORDER BY dateCommentaire DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
