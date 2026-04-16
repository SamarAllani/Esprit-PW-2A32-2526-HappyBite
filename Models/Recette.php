<?php

class Recette
{
    private ?int $id_recette = null;
    private ?string $nom = null;
    private ?string $description = null;
    private ?int $calories = null;

    public function __construct(?string $nom = null, ?string $description = null, ?int $calories = null)
    {
        $this->nom = $nom;
        $this->description = $description;
        $this->calories = $calories;
    }

    public function getIdRecette(): ?int
    {
        return $this->id_recette;
    }

    public function setIdRecette(int $id_recette): void
    {
        $this->id_recette = $id_recette;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCalories(): ?int
    {
        return $this->calories;
    }

    public function setCalories(?int $calories): void
    {
        $this->calories = $calories;
    }
}
?>