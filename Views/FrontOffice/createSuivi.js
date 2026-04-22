document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("profilForm");
    if (!form) return;

    const get = (name) => form.querySelector(`[name="${name}"]`);

    const dateInput = get("date_jour");
    const poidsInput = get("poids");
    const caloriesInput = get("calories");
    const sommeilInput = get("sommeil_heures");
    const pasInput = get("nbr_pas");
    const sportInput = get("nbr_activites_sport");

    const err = {
        date: document.getElementById("err_date"),
        poids: document.getElementById("err_poids"),
        calories: document.getElementById("err_calories"),
        sommeil: document.getElementById("err_sommeil"),
        pas: document.getElementById("err_pas"),
        sport: document.getElementById("err_sport"),
        hydratation: document.getElementById("err_hydratation")
    };

    function showError(el, msg) {
        el.textContent = msg;
        el.style.display = "block";
    }

    function clearErrors() {
        Object.values(err).forEach(el => {
            el.textContent = "";
            el.style.display = "none";
        });
    }

    // ✅ FIX ICI
    function toNumber(input) {
        return input.value.trim() === "" ? null : Number(input.value);
    }

    function getHydratationValue() {
        const selected = form.querySelector('input[name="hydratation_litre"]:checked');
        return selected ? selected.value : null;
    }

    function validate() {
        let valid = true;
        clearErrors();

        const poids = toNumber(poidsInput);
        const calories = toNumber(caloriesInput);
        const sommeil = toNumber(sommeilInput);
        const pas = toNumber(pasInput);
        const sport = toNumber(sportInput);
        const hydra = getHydratationValue();
        const dateValue = dateInput.value;

        // DATE
        if (!dateValue) {
            showError(err.date, "Date obligatoire");
            valid = false;
        } else {
            const today = new Date();
            today.setHours(0,0,0,0);

            const selected = new Date(dateValue);
            selected.setHours(0,0,0,0);

            if (selected > today) {
                showError(err.date, "Date invalide");
                valid = false;
            }
        }

        // POIDS (optionnel mais valide si rempli)
        if (!poids) {
        showError(err.poids, "Poids obligatoire");
        valid = false;
    } else if (isNaN(poids) || poids <= 12 || poids > 300) {
        showError(err.poids, "Poids invalide");
        valid = false;
    }

    // CALORIES
    if (!calories) {
        showError(err.calories, "Calories obligatoires");
        valid = false;
    } else if (isNaN(calories) || calories <= 0 || calories > 10000) {
        showError(err.calories, "Calories invalides");
        valid = false;
    }

    // SOMMEIL
    if (!sommeil) {
        showError(err.sommeil, "Sommeil obligatoire");
        valid = false;
    } else if (isNaN(sommeil) || sommeil < 0 || sommeil > 24) {
        showError(err.sommeil, "Sommeil invalide");
        valid = false;
    }

    // PAS
    if (!pas) {
        showError(err.pas, "Nombre de pas obligatoire");
        valid = false;
    } else if (isNaN(pas) || pas <= 0) {
        showError(err.pas, "Pas invalides");
        valid = false;
    }

    // SPORT
    if (!sport) {
        showError(err.sport, "Champ sport obligatoire");
        valid = false;
    } else if (isNaN(sport) || sport < 0) {
        showError(err.sport, "Sport invalide");
        valid = false;
    }

    // HYDRATATION
    if (!hydra) {
        showError(err.hydratation, "Veuillez choisir votre hydratation");
        valid = false;
    }


        return valid;
    }

    form.addEventListener("submit", function (e) {
        if (!validate()) {
            e.preventDefault();
        }
    });

});