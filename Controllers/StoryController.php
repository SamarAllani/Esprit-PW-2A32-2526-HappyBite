<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Models/Story.php';

class StoryController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Crée une nouvelle story
     */
    public function create(string $image): bool
    {
        try {
            $query = "INSERT INTO Story (image, dateCreation) VALUES (:image, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':image', $image);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Récupère toutes les stories valides (moins de 24h)
     */
    public function getActiveStories(): array
    {
        try {
            // Stories créées il y a moins de 24 heures
            $query = "SELECT * FROM Story WHERE dateCreation >= NOW() - INTERVAL 1 DAY ORDER BY dateCreation DESC";
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Supprime une story
     */
    public function delete(int $id): bool
    {
        try {
            $query = "DELETE FROM Story WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
