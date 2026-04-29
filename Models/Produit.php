<?php

declare(strict_types=1);

class Produit
{
    private string $nom;
    private float $prix;
    private string $image;
    private string $allergene;
    private string $benefices;
    private ?int $calories;
    private string $dateAjout;
    private int $idUtilisateur;
    private int $idCategorie;

    public function __construct(
        string $nom,
        float $prix,
        string $image,
        string $allergene,
        string $benefices,
        ?int $calories,
        string $dateAjout,
        int $idUtilisateur,
        int $idCategorie
    ) {
        $this->nom = $nom;
        $this->prix = $prix;
        $this->image = $image;
        $this->allergene = $allergene;
        $this->benefices = $benefices;
        $this->calories = $calories;
        $this->dateAjout = $dateAjout;
        $this->idUtilisateur = $idUtilisateur;
        $this->idCategorie = $idCategorie;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getPrix(): float
    {
        return $this->prix;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getAllergene(): string
    {
        return $this->allergene;
    }

    public function getBenefices(): string
    {
        return $this->benefices;
    }

    public function getCalories(): ?int
    {
        return $this->calories;
    }

    public function getDateAjout(): string
    {
        return $this->dateAjout;
    }

    public function getIdUtilisateur(): int
    {
        return $this->idUtilisateur;
    }

    public function getIdCategorie(): int
    {
        return $this->idCategorie;
    }
}
