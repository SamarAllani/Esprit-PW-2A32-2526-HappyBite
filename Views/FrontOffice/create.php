<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer Profil Santé</title>
<link rel="stylesheet" href="/Views/assets/css/sytle.css">
    <style>
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 5px;
            display: none;
        }
        
    </style>
    <head>
    <meta charset="UTF-8">
    <title>Créer Profil Santé</title>

    <style>
        /* RESET */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #eef7f1, #ffffff);
            padding: 20px;
        }

        /* NAVBAR */
        .main-navbar {
            background: white;
            padding: 12px 0;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .nav-container {
            width: 100%;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #036612;
            font-weight: bold;
        }

        .nav-logo img {
            width: 40px;
            right: 100%;
        }

        /* FORM CONTAINER */
        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #036612;
        }

        /* LABEL */
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }

        /* INPUT */
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            transition: 0.3s;
        }

        input:focus,
        select:focus {
            border-color: #036612;
            outline: none;
            box-shadow: 0 0 5px rgba(39,174,96,0.3);
        }

        /* CHECKBOX */
        .checkbox-group label {
            display: block;
            font-weight: normal;
            margin-bottom: 5px;
        }

        .checkbox-group input {
            margin-right: 6px;
        }

        /* BUTTON */
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #036612, #036612);
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            transform: translateY(-2px);
        }

        /* ERROR */
        .error {
            background: #ffe6e6;
            color: #e74c3c;
            padding: 8px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 8px;
            display: none;
        }

    </style>
</head>
</head>
<body>
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="container">
<h1>Créer mon profil santé</h1>

<form method="POST" id="formProfil">

    <!-- Taille -->
    
    <label>Taille (cm) :</label>
    <input type="number" step="0.01" name="taille" id="taille"><br><br>
     <div class="error" id="error_taille"></div>
    <!-- Poids -->
    
    <label>Poids actuel (kg) :</label>
    <input type="number" step="0.01" name="poids_actuel" id="poids"><br><br>
    <div class="error" id="error_poids"></div>
    
    <!-- Objectif -->
    
    <label>Objectif :</label>
    <select name="objectif" id="objectif">
        <option value="">-- Choisir --</option>
        <option value="Perte de poids">Perte de poids</option>
        <option value="Prise de masse">Prise de masse</option>
        <option value="Maintien">Maintien</option>
    </select><br><br>
    <div class="error" id="error_objectif"></div>
    <!-- Allergènes -->
    <div class="error" id="error_allergenes"></div>
    <label>Allergènes :</label><br>
    <input type="checkbox" name="allergenes[]" value="Gluten"> Gluten<br>
    <input type="checkbox" name="allergenes[]" value="Lactose"> Lactose<br>
    <input type="checkbox" name="allergenes[]" value="Sucre"> Sucre<br>
    <input type="checkbox" name="allergenes[]" value="Fruits à coque"> Fruits à coque<br><br>

    <!-- Carences -->
    <div class="error" id="error_carences"></div>
    <label>Carences :</label><br>
    <input type="checkbox" name="carences[]" value="Fer"> Fer<br>
    <input type="checkbox" name="carences[]" value="Calcium"> Calcium<br>
    <input type="checkbox" name="carences[]" value="Vitamine C"> Vitamine C<br>
    <input type="checkbox" name="carences[]" value="Vitamine D"> Vitamine D<br><br>

    <!-- Maladies -->
    <div class="error" id="error_maladies"></div>
    <label>Maladies :</label><br>
    <input type="checkbox" name="maladies[]" value="Diabète"> Diabète<br>
    <input type="checkbox" name="maladies[]" value="Cholestérol"> Cholestérol<br>
    <input type="checkbox" name="maladies[]" value="Hypertension"> Hypertension<br><br>

    <button type="submit">Enregistrer</button>

</form>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("formProfil");

    form.addEventListener("submit", function (e) {

        let valid = true;

        // reset erreurs
        document.querySelectorAll(".error").forEach(el => {
            el.style.display = "none";
            el.innerText = "";
        });

        // récupération valeurs (sécurisée)
        const taille = Number(document.getElementById("taille").value);
        const poids = Number(document.getElementById("poids").value);
        const objectif = document.getElementById("objectif").value;

        console.log("DEBUG => taille:", taille, "poids:", poids);

        // TAILLE
        if (!Number.isFinite(taille) || taille < 55 || taille > 251) {
            const err = document.getElementById("error_taille");
            err.innerText = "Taille doit être entre 55 et 251 cm";
            err.style.display = "block";
            valid = false;
        }

        // POIDS
        if (!Number.isFinite(poids) || poids < 12 || poids > 700) {
            const err = document.getElementById("error_poids");
            err.innerText = "Poids doit être entre 12 et 700 kg";
            err.style.display = "block";
            valid = false;
        }

        // OBJECTIF
        if (!objectif) {
            const err = document.getElementById("error_objectif");
            err.innerText = "Veuillez choisir un objectif";
            err.style.display = "block";
            valid = false;
        }

        // ALLERGÈNES (optionnel)
        const allergenes = document.querySelectorAll('input[name="allergenes[]"]:checked');
        if (allergenes.length === 0) {
            document.getElementById("error_allergenes").style.display = "none";
        }

        // STOP FORM SUBMIT SI ERREUR
        if (!valid) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });

});
</script>

</body>
</html>