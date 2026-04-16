<?php
require_once(__DIR__ . '/../Config.php');
require_once(__DIR__ . '/../Models/Recette.php');

class RecetteController
{
    public function addRecette(Recette $recette)
    {
        $sql = "INSERT INTO recette (nom, description, calories) 
                VALUES (:nom, :description, :calories)";

        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $recette->getNom(),
                'description' => $recette->getDescription(),
                'calories' => $recette->getCalories()
            ]);

            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
            return false;
        }
    }

    public function listRecettes()
    {
        $sql = "SELECT * FROM recette ORDER BY id_recette DESC";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
            return [];
        }
    }

    public function showRecette($id)
    {
        $sql = "SELECT * FROM recette WHERE id_recette = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id
            ]);

            $data = $query->fetch();

            if ($data) {
                $recette = new Recette(
                    $data['nom'],
                    $data['description'],
                    isset($data['calories']) ? (int)$data['calories'] : 0
                );
                $recette->setIdRecette((int)$data['id_recette']);
                return $recette;
            }

            return null;
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
            return null;
        }
    }

    public function updateRecette(Recette $recette, $id)
    {
        $sql = "UPDATE recette
                SET nom = :nom,
                    description = :description,
                    calories = :calories
                WHERE id_recette = :id";

        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $recette->getNom(),
                'description' => $recette->getDescription(),
                'calories' => $recette->getCalories(),
                'id' => $id
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
            return false;
        }
    }

    public function deleteRecette($id)
    {
        $sql = "DELETE FROM recette WHERE id_recette = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function rechercherRecettes($motCle = "")
    {
        $sql = "SELECT * FROM recette WHERE nom LIKE :motCle ORDER BY id_recette DESC";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $motCle = "%" . $motCle . "%";
            $query->execute([
                'motCle' => $motCle
            ]);

            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
            return [];
        }
    }
    public function showRecetteDetails($id)
{
    $sql = "SELECT * FROM recette WHERE id_recette = :id";
    $db = Config::getConnexion();

    try {
        $query = $db->prepare($sql);
        $query->execute([
            'id' => $id
        ]);

        return $query->fetch();
    } catch (Exception $e) {
        echo 'Erreur : ' . $e->getMessage();
        return false;
    }
}

    public function ajouterProduitsRecette($idRecette, $produits)
    {
        $sql = "INSERT INTO recette_produit (id_recette, id_produit) VALUES (:id_recette, :id_produit)";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);

            foreach ($produits as $idProduit) {
                $query->execute([
                    'id_recette' => $idRecette,
                    'id_produit' => $idProduit
                ]);
            }

            return true;
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
            return false;
        }
    }

    public function supprimerProduitsRecette($idRecette)
    {
        $sql = "DELETE FROM recette_produit WHERE id_recette = :id_recette";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_recette' => $idRecette
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getProduitsByRecette($idRecette)
    {
        $sql = "SELECT p.*
                FROM produit p
                INNER JOIN recette_produit rp ON p.id_produit = rp.id_produit
                WHERE rp.id_recette = :id_recette";

        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_recette' => $idRecette
            ]);

            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
            return [];
        }
    }

    public function calculerCaloriesRecette($produitsIds)
    {
        if (empty($produitsIds)) {
            return 0;
        }

        $db = Config::getConnexion();
        $placeholders = implode(',', array_fill(0, count($produitsIds), '?'));
        $sql = "SELECT SUM(calories) AS total_calories FROM produit WHERE id_produit IN ($placeholders)";

        try {
            $query = $db->prepare($sql);
            $query->execute($produitsIds);
            $result = $query->fetch();

            return (int)($result['total_calories'] ?? 0);
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
            return 0;
        }
    }

   public function rechercherRecettesIntelligentes($motCle = "", $allergie = "", $objectif = "", $budget = "")
{
    $sql = "SELECT DISTINCT r.*
            FROM recette r
            LEFT JOIN recette_produit rp ON r.id_recette = rp.id_recette
            LEFT JOIN produit p ON rp.id_produit = p.id_produit
            WHERE 1=1";

    if (!empty($motCle)) {
        $sql .= " AND r.nom LIKE :motCle";
    }

    // Exclure les recettes contenant un produit avec allergène sensible
    if (!empty($allergie)) {
        $sql .= " AND (
                    p.allergene IS NULL 
                    OR p.allergene = '' 
                    OR p.allergene NOT LIKE :allergie
                  )";
    }

    // Budget : somme approximative des produits d'une recette
    if (!empty($budget) && is_numeric($budget)) {
        $sql .= " AND r.id_recette IN (
                    SELECT rp2.id_recette
                    FROM recette_produit rp2
                    INNER JOIN produit p2 ON rp2.id_produit = p2.id_produit
                    GROUP BY rp2.id_recette
                    HAVING SUM(p2.prix) <= :budget
                  )";
    }

    // Objectif selon calories recette
    if (!empty($objectif)) {
        if ($objectif === 'perte de poids') {
            $sql .= " AND r.calories <= 350";
        } elseif ($objectif === 'maintien') {
            $sql .= " AND r.calories BETWEEN 351 AND 650";
        } elseif ($objectif === 'gain de poids') {
            $sql .= " AND r.calories > 650";
        }
    }

    $sql .= " ORDER BY r.calories ASC, r.id_recette DESC";

    $db = Config::getConnexion();

    try {
        $query = $db->prepare($sql);

        if (!empty($motCle)) {
            $motCle = "%" . $motCle . "%";
            $query->bindParam(':motCle', $motCle);
        }

        if (!empty($allergie)) {
            $allergie = "%" . $allergie . "%";
            $query->bindParam(':allergie', $allergie);
        }

        if (!empty($budget) && is_numeric($budget)) {
            $query->bindParam(':budget', $budget);
        }

        $query->execute();
        return $query->fetchAll();
    } catch (Exception $e) {
        echo 'Erreur : ' . $e->getMessage();
        return [];
    }
} 
}
?>