<?php

declare(strict_types=1);

/**
 * Panier en session : id_produit => ['quantite' => int, 'prix_unitaire' => float]
 */
function panier_ensure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['panier']) || !is_array($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }
}

/** @return array<int, array{quantite: int, prix_unitaire: float}> */
function panier_get_items(): array
{
    panier_ensure_session();
    $out = [];
    foreach ($_SESSION['panier'] as $key => $row) {
        $id = (int) $key;
        if ($id < 1 || !is_array($row)) {
            continue;
        }
        $q = (int) ($row['quantite'] ?? 0);
        if ($q < 1) {
            continue;
        }
        $out[$id] = [
            'quantite' => $q,
            'prix_unitaire' => (float) ($row['prix_unitaire'] ?? 0),
        ];
    }
    return $out;
}

function panier_add_product(int $idProduit, float $prixUnitaire): void
{
    panier_ensure_session();
    if (!isset($_SESSION['panier'][$idProduit])) {
        $_SESSION['panier'][$idProduit] = ['quantite' => 0, 'prix_unitaire' => $prixUnitaire];
    }
    $_SESSION['panier'][$idProduit]['quantite'] = (int) $_SESSION['panier'][$idProduit]['quantite'] + 1;
    $_SESSION['panier'][$idProduit]['prix_unitaire'] = $prixUnitaire;
}

function panier_remove_product(int $idProduit): void
{
    panier_ensure_session();
    unset($_SESSION['panier'][$idProduit]);
}

function panier_clear(): void
{
    panier_ensure_session();
    $_SESSION['panier'] = [];
}
