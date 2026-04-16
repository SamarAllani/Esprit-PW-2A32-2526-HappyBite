<?php
require_once(__DIR__ . '/../Config.php');
require_once(__DIR__ . '/../Models/Produit.php');
class ProduitController
{
    public function addProduit(Produit $produit)
    {
        $sql = "INSERT INTO produit 
                (nom, prix, image, allergene, benefices, calories, date_ajout, id_utilisateur, id_categorie)
                VALUES 
                (:nom, :prix, :image, :allergene, :benefices, :calories, :date_ajout, :id_utilisateur, :id_categorie)";

        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $produit->getNom(),
                'prix' => $produit->getPrix(),
                'image' => $produit->getImage(),
                'allergene' => $produit->getAllergene(),
                'benefices' => $produit->getBenefices(),
                'calories' => $produit->getCalories(),
                'date_ajout' => $produit->getDateAjout(),
                'id_utilisateur' => $produit->getIdUtilisateur(),
                'id_categorie' => $produit->getIdCategorie()
            ]);
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
        }
    }

    public function listProduits()
    {
        $sql = "SELECT 
                    p.id_produit,
                    p.nom,
                    p.prix,
                    p.image,
                    p.allergene,
                    p.benefices,
                    p.calories,
                    p.date_ajout,
                    p.id_utilisateur,
                    p.id_categorie,
                    c.nom AS nom_categorie
                FROM produit p
                INNER JOIN categorie c ON p.id_categorie = c.id_categorie
                ORDER BY p.id_produit DESC";

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

    public function deleteProduit($id)
    {
        $sql = "DELETE FROM produit WHERE id_produit = :id";
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

    public function showProduit($id)
    {
        $sql = "SELECT * FROM produit WHERE id_produit = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id
            ]);

            $data = $query->fetch();

            if ($data) {
                $produit = new Produit(
                    $data['nom'],
                    (float)$data['prix'],
                    $data['image'],
                    $data['allergene'],
                    $data['benefices'],
                    $data['calories'] !== null ? (int)$data['calories'] : null,
                    $data['date_ajout'],
                    (int)$data['id_utilisateur'],
                    (int)$data['id_categorie']
                );

                $produit->setIdProduit((int)$data['id_produit']);
                return $produit;
            }

            return null;
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
            return null;
        }
    }

    public function updateProduit(Produit $produit, $id)
    {
        $sql = "UPDATE produit SET
                    nom = :nom,
                    prix = :prix,
                    image = :image,
                    allergene = :allergene,
                    benefices = :benefices,
                    calories = :calories,
                    date_ajout = :date_ajout,
                    id_utilisateur = :id_utilisateur,
                    id_categorie = :id_categorie
                WHERE id_produit = :id";

        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $produit->getNom(),
                'prix' => $produit->getPrix(),
                'image' => $produit->getImage(),
                'allergene' => $produit->getAllergene(),
                'benefices' => $produit->getBenefices(),
                'calories' => $produit->getCalories(),
                'date_ajout' => $produit->getDateAjout(),
                'id_utilisateur' => $produit->getIdUtilisateur(),
                'id_categorie' => $produit->getIdCategorie(),
                'id' => $id
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
            return false;
        }
    }
    public function rechercherProduitsIntelligents($motCle = "", $idCategorie = "", $allergie = "", $objectif = "", $budget = "")
{
    $sql = "SELECT 
                p.id_produit,
                p.nom,
                p.prix,
                p.image,
                p.allergene,
                p.benefices,
                p.calories,
                p.date_ajout,
                c.nom AS nom_categorie
            FROM produit p
            INNER JOIN categorie c ON p.id_categorie = c.id_categorie
            WHERE 1=1";

    // Recherche par nom
    if (!empty($motCle)) {
        $sql .= " AND p.nom LIKE :motCle";
    }

    // Filtre par catégorie
    if (!empty($idCategorie)) {
        $sql .= " AND p.id_categorie = :idCategorie";
    }

    // Filtre allergie : exclure les produits incompatibles
    if (!empty($allergie)) {
        $sql .= " AND (p.allergene IS NULL OR p.allergene = '' OR p.allergene NOT LIKE :allergie)";
    }

    // Filtre budget
    if (!empty($budget) && is_numeric($budget)) {
        $sql .= " AND p.prix <= :budget";
    }

    // Filtre objectif selon calories
    if (!empty($objectif)) {
        if ($objectif === 'perte de poids') {
            $sql .= " AND p.calories <= 120";
        } elseif ($objectif === 'maintien') {
            $sql .= " AND p.calories BETWEEN 121 AND 250";
        } elseif ($objectif === 'gain de poids') {
            $sql .= " AND p.calories > 250";
        }
    }

    $sql .= " ORDER BY p.calories ASC, p.prix ASC";

    $db = Config::getConnexion();

    try {
        $query = $db->prepare($sql);

        if (!empty($motCle)) {
            $motCle = "%" . $motCle . "%";
            $query->bindParam(':motCle', $motCle);
        }

        if (!empty($idCategorie)) {
            $query->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
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

    public function rechercherProduits($motCle = "", $idCategorie = "")
    {
        $sql = "SELECT 
                    p.id_produit,
                    p.nom,
                    p.prix,
                    p.image,
                    p.allergene,
                    p.benefices,
                    p.calories,
                    p.date_ajout,
                    c.nom AS nom_categorie
                FROM produit p
                INNER JOIN categorie c ON p.id_categorie = c.id_categorie
                WHERE 1=1";

        if (!empty($motCle)) {
            $sql .= " AND p.nom LIKE :motCle";
        }

        if (!empty($idCategorie)) {
            $sql .= " AND p.id_categorie = :idCategorie";
        }

        $sql .= " ORDER BY p.id_produit DESC";

        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);

            if (!empty($motCle)) {
                $motCle = "%" . $motCle . "%";
                $query->bindParam(':motCle', $motCle);
            }

            if (!empty($idCategorie)) {
                $query->bindParam(':idCategorie', $idCategorie);
            }

            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
            return [];
        }
    }

public function getProduitById($id)
{
    $sql = "SELECT * FROM produit WHERE id_produit = :id";
    $db = Config::getConnexion();
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();

    return $query->fetch(PDO::FETCH_ASSOC);
}



    public function showProduitDetails($id)
{
    $sql = "SELECT 
                p.id_produit,
                p.nom,
                p.prix,
                p.image,
                p.allergene,
                p.benefices,
                p.calories,
                p.date_ajout,
                p.id_utilisateur,
                p.id_categorie,
                c.nom AS nom_categorie
            FROM produit p
            INNER JOIN categorie c ON p.id_categorie = c.id_categorie
            WHERE p.id_produit = :id";

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
public function reassignProduitsToCategorie(int $ancienneCategorie, int $nouvelleCategorie): void
{
    $sql = "UPDATE produit 
            SET id_categorie = :nouvelleCategorie 
            WHERE id_categorie = :ancienneCategorie";

    $db = config::getConnexion();
    $query = $db->prepare($sql);
    $query->bindValue(':nouvelleCategorie', $nouvelleCategorie, PDO::PARAM_INT);
    $query->bindValue(':ancienneCategorie', $ancienneCategorie, PDO::PARAM_INT);
    $query->execute();
}

public function getCategorieByNom(string $nom)
{
    $sql = "SELECT * FROM categorie WHERE nom = :nom LIMIT 1";
    $db = config::getConnexion();
    $query = $db->prepare($sql);
    $query->bindValue(':nom', $nom);
    $query->execute();

    return $query->fetch();
}
}
?>