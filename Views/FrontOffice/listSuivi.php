<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Suivis Journaliers</title>
    <link rel="stylesheet" href="/Views/assets/css/sytle1.css">
</head>
<body>
   <table border="1" id="suiviTable">
    <thead>
        <tr>
            <th>Date</th>
            <th>Poids</th>
            <th>Calories</th>
            <th>Sommeil</th>
            <th>Pas</th>
            <th>Sport</th>
            <th>Hydratation</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
</body>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const userId = 1; // ou dynamique si tu veux

    fetch("index.php?" + new URLSearchParams({
        action: "listSuiviJournalierAjax",
        id_utilisateur: userId
    }))
    .then(res => res.json())
    .then(data => {

        const tbody = document.querySelector("#suiviTable tbody");
        tbody.innerHTML = "";

        data.forEach(item => {
            tbody.innerHTML += `
                <tr>
                    <td>${item.date_jour}</td>
                    <td>${item.poids}</td>
                    <td>${item.calories}</td>
                    <td>${item.sommeil_heures}</td>
                    <td>${item.nbr_pas}</td>
                    <td>${item.nbr_activites_sport}</td>
                    <td>${item.hydratation_litre}</td>
                </tr>
            `;
        });

    });

});
</script>
</html>