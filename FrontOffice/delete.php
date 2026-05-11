<form method="POST"
      action="/Controllers/SuiviJournalierController.php"
      style="display:inline;"
      onsubmit="return confirm('Supprimer ce suivi ?');">

    <input type="hidden" name="id" value="<?= $suivi['id'] ?>">
    <input type="hidden" name="id_utilisateur" value="<?= $user['id'] ?>">

    <button type="submit">Supprimer</button>
</form>