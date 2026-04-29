function validateCategoryForm(event) {
  // Empêche l'envoi du formulaire tant que la validation n'est pas réussie
  event.preventDefault();

  // Récupération des champs
  let titre = document.getElementById("title").value.trim();
  let description = document.getElementById("description").value.trim();

  // Validation du titre (obligatoire, au moins 3 caractères)
  if (titre === "") {
    alert("❌ Le titre est obligatoire.");
    return false;
  }
  if (titre.length < 3) {
    alert("❌ Le titre doit contenir au moins 3 caractères.");
    return false;
  }

  // Validation de la description (optionnelle, mais on peut vérifier une longueur max si souhaité)
  // Ici, on ne fait rien car c'est optionnel.

  // Si tout est correct, on soumet le formulaire
  // alert("✅ Catégorie ajoutée avec succès !"); // (optionnel)
  event.target.submit(); // Soumet le formulaire après validation
  return true;
}

// Attacher la validation au formulaire au chargement de la page
document.addEventListener("DOMContentLoaded", function () {
  let form = document.querySelector("form");
  if (form) {
    form.addEventListener("submit", validateCategoryForm);
  }
});