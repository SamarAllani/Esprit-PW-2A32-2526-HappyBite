<?php

require_once __DIR__ . '/../Config.php';

class SuiviJournalierController
{
    private $db;

    public function __construct()
    {
        $this->db = (new Config())->getConnexion();
    }

    private function redirect($id_utilisateur)
    {
        header("Location: index.php?action=userHealthSpace&id_utilisateur=" . $id_utilisateur);
        exit();
    }

    private function getProfilId($id_utilisateur)
    {
        $stmt = $this->db->prepare("SELECT id FROM profil_sante WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $id_utilisateur]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row['id'] : null;
    }

    /* =========================
       CREATE
    ========================= */
    public function create($id_utilisateur)
    {
        $id_profil = $this->getProfilId($id_utilisateur);

        if (!$id_profil) {
            die("Crée un profil santé d'abord.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $date = $_POST['date_jour'];

            // check doublon
            $check = $this->db->prepare("
                SELECT id FROM suivi_journalier
                WHERE id_profil_sante = :p AND date_jour = :d
            ");

            $check->execute([
                'p' => $id_profil,
                'd' => $date
            ]);

            if ($check->fetch()) {
                $this->redirect($id_utilisateur);
            }

            $stmt = $this->db->prepare("
                INSERT INTO suivi_journalier
                (id_profil_sante, date_jour, poids, calories, sommeil_heures, nbr_pas, nbr_activites_sport, hydratation_litre)
                VALUES
                (:p, :d, :poids, :cal, :som, :pas, :sport, :hydr)
            ");

            $stmt->execute([
                'p' => $id_profil,
                'd' => $date,
                'poids' => $_POST['poids'] ?? null,
                'cal' => $_POST['calories'] ?? null,
                'som' => $_POST['sommeil_heures'] ?? null,
                'pas' => $_POST['nbr_pas'] ?? null,
                'sport' => $_POST['nbr_activites_sport'] ?? null,
                'hydr' => $_POST['hydratation_litre'] ?? null
            ]);

            $this->redirect($id_utilisateur);
        }

        include __DIR__ . '/../Views/FrontOffice/createSuivi.php';
    }

    /* =========================
       LIST USER
    ========================= */
public function getUser($id)
{
    $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE id = :id");
    $stmt->execute(['id' => $id]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Utilisateur introuvable");
    }

    return $user;
}

    public function getSuiviUser($id_utilisateur)
    {
        $stmt = $this->db->prepare("
            SELECT sj.*
            FROM suivi_journalier sj
            JOIN profil_sante ps ON sj.id_profil_sante = ps.id
            WHERE ps.id_utilisateur = :id
            ORDER BY sj.date_jour DESC
        ");

        $stmt->execute(['id' => $id_utilisateur]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       EDIT (AFFICHAGE FORM)
    ========================= */
    public function edit($id)
{
    $stmt = $this->db->prepare("SELECT * FROM suivi_journalier WHERE id = :id");
    $stmt->execute(['id' => $id]);

    $suivi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$suivi) {
        die("Suivi introuvable");
    }

    $id_utilisateur = $_GET['id_utilisateur'] ?? null;
    

    include __DIR__ . '/../Views/FrontOffice/editSuivi.php';
}public function listUsersBackoffice()
{
    $stmt = $this->db->query("
        SELECT 
            u.id,
            u.prenom,
            u.nom,
            u.email,

            ps.taille,
            ps.poids_actuel,
            ps.objectif,
            ps.allergenes,
            ps.carences,
            ps.maladies,
            ps.date_mise_a_jour

        FROM utilisateur u
        INNER JOIN profil_sante ps 
            ON ps.id_utilisateur = u.id
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    /* =========================
       UPDATE (POST)
    ========================= */
public function update($id)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $stmt = $this->db->prepare("
            UPDATE suivi_journalier
            SET date_jour = :d,
                poids = :p,
                calories = :c,
                sommeil_heures = :s,
                nbr_pas = :n,
                nbr_activites_sport = :a,
                hydratation_litre = :h
            WHERE id = :id
        ");

        $stmt->execute([
            'd' => $_POST['date_jour'],
            'p' => $_POST['poids'],
            'c' => $_POST['calories'],
            's' => $_POST['sommeil_heures'],
            'n' => $_POST['nbr_pas'],
            'a' => $_POST['nbr_activites_sport'],
            'h' => $_POST['hydratation_litre'],
            'id' => $id
        ]);

        $id_utilisateur = $_GET['id_utilisateur'] ?? null;

        header("Location: index.php?action=userHealthSpace&id_utilisateur=" . $id_utilisateur);
        exit();
    }
}
 public function delete($id, $id_utilisateur)
{
    $sql = "DELETE FROM suivi_journalier WHERE id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);

   header("Location: index.php?action=userHealthSpace&id_utilisateur=" . $id_utilisateur);
    exit();
}  
public function list($id_utilisateur)
{
    // USER
    $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE id = ?");
    $stmt->execute([$id_utilisateur]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // PROFIL
    $stmt = $this->db->prepare("SELECT * FROM profil_sante WHERE id_utilisateur = ?");
    $stmt->execute([$id_utilisateur]);
    $profil = $stmt->fetch(PDO::FETCH_ASSOC);

    // 👇 THIS IS WHAT YOU FORGOT (again)
    if ($profil) {
            foreach (['allergenes', 'carences', 'maladies'] as $key) {

                $data = json_decode($profil[$key], true);

                if (!is_array($data)) {
                    $data = [];
                }

                $profil[$key] = implode(', ', $data);
            }
        }
    // SUIVIS
    $suivis = $this->getSuiviUser($id_utilisateur);

    require __DIR__ . '/../Views/FrontOffice/user_health_space.php';
}

    public function searchUsersBackoffice($search)
{
    $search = strtolower(trim($search));

    $stmt = $this->db->prepare("
        SELECT 
            u.id,
            u.prenom,
            u.nom,
            u.email,

            ps.taille,
            ps.poids_actuel,
            ps.objectif,
            ps.allergenes,
            ps.carences,
            ps.maladies,
            ps.date_mise_a_jour

        FROM utilisateur u
        INNER JOIN profil_sante ps 
            ON ps.id_utilisateur = u.id

        WHERE 
            LOWER(u.prenom) LIKE :search
            OR LOWER(u.nom) LIKE :search
            OR LOWER(u.email) LIKE :search
            OR CAST(u.id AS CHAR) LIKE :search
    ");

    $stmt->execute([
        'search' => '%' . $search . '%'
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function getStatsProfilsVsNon()
{
    // utilisateurs avec profil
    $stmt1 = $this->db->query("
        SELECT COUNT(DISTINCT id_utilisateur) as total 
        FROM profil_sante
    ");
    $avec = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];

    // total utilisateurs
    $stmt2 = $this->db->query("
        SELECT COUNT(*) as total 
        FROM utilisateur
    ");
    $total = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];

    $sans = $total - $avec;

    return [
        'avec' => $avec,
        'sans' => $sans
    ];
}

public function searchSuiviAjax($id_utilisateur, $date)
{
    $stmt = $this->db->prepare("
        SELECT sj.*
        FROM suivi_journalier sj
        JOIN profil_sante ps ON sj.id_profil_sante = ps.id
        WHERE ps.id_utilisateur = :id
        AND sj.date_jour = :date
    ");

    $stmt->execute([
        'id' => $id_utilisateur,
        'date' => $date
    ]);

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}
public function listAjax($id_utilisateur)
{
    $stmt = $this->db->prepare("
        SELECT sj.*
        FROM suivi_journalier sj
        JOIN profil_sante ps ON sj.id_profil_sante = ps.id
        WHERE ps.id_utilisateur = :id
        ORDER BY sj.date_jour DESC
    ");

    $stmt->execute(['id' => $id_utilisateur]);

    header('Content-Type: application/json');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit();
}
}