<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Models/Post.php';

class PostController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Crée un nouveau post
     */
    public function create(string $contenu, ?string $image = null): bool
    {
        try {
            $query = "INSERT INTO Post (contenu, datePublication, image, nombreLikes) 
                      VALUES (:contenu, NOW(), :image, 0)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':contenu', $contenu);
            $stmt->bindParam(':image', $image);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Récupère tous les posts triés par date décroissante
     */
    public function getAll(): array
    {
        try {
            $query = "SELECT * FROM Post ORDER BY datePublication DESC";
            $stmt = $this->db->query($query);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Récupère un post par son ID
     */
    public function getById(int $id): ?array
    {
        try {
            $query = "SELECT * FROM Post WHERE id = :id";
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
     * Met à jour un post
     */
    public function update(int $id, string $contenu, ?string $image = null): bool
    {
        try {
            if ($image !== null) {
                $query = "UPDATE Post SET contenu = :contenu, image = :image WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':contenu', $contenu);
                $stmt->bindParam(':image', $image);
            } else {
                $query = "UPDATE Post SET contenu = :contenu WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':contenu', $contenu);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Supprime un post et ses commentaires associés
     */
    public function delete(int $id): bool
    {
        try {
            $query = "DELETE FROM Post WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Incrémente le nombre de likes
     */
    public function addLike(int $id): bool
    {
        try {
            $query = "UPDATE Post SET nombreLikes = nombreLikes + 1 WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Décrémente le nombre de likes
     */
    public function removeLike(int $id): bool
    {
        try {
            $query = "UPDATE Post SET nombreLikes = GREATEST(nombreLikes - 1, 0) WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Récupère un post avec ses commentaires pour l'admin
     */
    public function getPostWithComments(int $id): ?array
    {
        try {
            $post = $this->getById($id);
            if (!$post) {
                return null;
            }

            // Récupérer les commentaires
            require_once __DIR__ . '/CommentaireController.php';
            $commentController = new CommentaireController();
            $comments = $commentController->getByPostId($id);

            return [
                'post' => $post,
                'comments' => $comments
            ];
        } catch (PDOException $e) {
            return null;
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $controller = new PostController();

    switch ($_GET['action']) {
        case 'get_post':
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $data = $controller->getPostWithComments($id);
                if ($data) {
                    echo json_encode(['success' => true] + $data);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Post not found']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'ID required']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    exit;
}
