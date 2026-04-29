<?php

declare(strict_types=1);

class Recette
{
    private string $nom;
    private string $description;
    private int $calories;

    public function __construct(string $nom, string $description, int $calories)
    {
        $this->nom = $nom;
        $this->description = $description;
        $this->calories = $calories;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCalories(): int
    {
        return $this->calories;
    }
}
