document.addEventListener("DOMContentLoaded", function () {

    document.getElementById("formProfil").addEventListener("submit", function (e) {

        let valid = true;

        // reset erreurs
        document.querySelectorAll(".error").forEach(el => {
            el.style.display = "none";
            el.innerText = "";
        });

        let taille = parseFloat(document.getElementById("taille").value);
        let poids = parseFloat(document.getElementById("poids").value);
        let objectif = document.getElementById("objectif").value;

        // Taille
        if (isNaN(taille) || taille < 55 || taille > 251) {
            let err = document.getElementById("error_taille");
            err.innerText = "Taille doit être entre 55 et 251 cm";
            err.style.display = "block";
            valid = false;
        }

        // Poids
        if (isNaN(poids) || poids < 12 || poids > 700) {
            let err = document.getElementById("error_poids");
            err.innerText = "Poids doit être entre 12 et 700 kg";
            err.style.display = "block";
            valid = false;
        }

        // Objectif
        if (objectif === "") {
            let err = document.getElementById("error_objectif");
            err.innerText = "Veuillez choisir un objectif";
            err.style.display = "block";
            valid = false;
        }

        // Allergènes (optionnel → pas bloquant)
        let allergenes = document.querySelectorAll('input[name="allergenes[]"]:checked');
        if (allergenes.length === 0) {
            document.getElementById("error_allergenes").style.display = "none";
        }

        if (!valid) {
            e.preventDefault();
        }
    });

});