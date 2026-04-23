<?php

declare(strict_types=1);

class Categorie
{
    private ?int $idCategorie;
    private string $nom;
    private string $description;

    public function __construct(string $nom, string $description = '', ?int $idCategorie = null)
    {
        $this->nom = $nom;
        $this->description = $description;
        $this->idCategorie = $idCategorie;
    }

    public function getIdCategorie(): ?int
    {
        return $this->idCategorie;
    }

    public function setIdCategorie(int $id): void
    {
        $this->idCategorie = $id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
