<?php

declare(strict_types=1);

/**
 * Capture visage (JPEG) : enregistrement + comparaison simple (GD), même idée que commande.php.
 * La comparaison est heuristique (cosinus sur image réduite), pas de biométrie certifiée.
 */

const AUTH_FACE_GRID = 64;

function authFaceDecodeSnapshot(string $raw): ?string
{
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }
    if (preg_match('#^data:image/(?:jpeg|jpg);base64,(.+)$#i', $raw, $m)) {
        $bin = base64_decode($m[1], true);
        return $bin !== false && $bin !== '' ? $bin : null;
    }
    return null;
}

/** Grille luminance 64×64 (luminance 0–1), sans z-score — pour retournement puis normalisation. */
function authFaceRawLumaFromJpegBytes(string $jpegBytes): ?array
{
    if (!function_exists('imagecreatefromstring')) {
        return null;
    }
    $im = @imagecreatefromstring($jpegBytes);
    if ($im === false) {
        return null;
    }
    $tw = AUTH_FACE_GRID;
    $th = AUTH_FACE_GRID;
    $w = imagesx($im);
    $h = imagesy($im);
    $tmp = imagecreatetruecolor($tw, $th);
    if ($tmp === false) {
        imagedestroy($im);
        return null;
    }
    imagecopyresampled($tmp, $im, 0, 0, 0, 0, $tw, $th, $w, $h);
    imagedestroy($im);

    $vec = [];
    for ($y = 0; $y < $th; $y++) {
        for ($x = 0; $x < $tw; $x++) {
            $rgb = imagecolorat($tmp, $x, $y);
            $r = ($rgb >> 16) & 255;
            $g = ($rgb >> 8) & 255;
            $b = $rgb & 255;
            $vec[] = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255.0;
        }
    }
    imagedestroy($tmp);

    return $vec === [] ? null : $vec;
}

/** @param list<float> $vec */
function authFaceZscore(array $vec): array
{
    $n = count($vec);
    if ($n === 0) {
        return [];
    }
    $mean = array_sum($vec) / $n;
    $var = 0.0;
    foreach ($vec as $v) {
        $var += ($v - $mean) * ($v - $mean);
    }
    $std = sqrt($var / $n + 1e-8);
    $out = [];
    foreach ($vec as $v) {
        $out[] = ($v - $mean) / $std;
    }
    return $out;
}

/** Retournement horizontal sur une grille 64×64 (luminance brute). */
function authFaceFlipH64(array $vec): array
{
    $tw = AUTH_FACE_GRID;
    $th = AUTH_FACE_GRID;
    $out = [];
    for ($y = 0; $y < $th; $y++) {
        for ($x = 0; $x < $tw; $x++) {
            $out[] = $vec[$y * $tw + ($tw - 1 - $x)];
        }
    }
    return $out;
}

/** @return list<float>|null */
function authFaceVectorFromJpegBytes(string $jpegBytes): ?array
{
    $raw = authFaceRawLumaFromJpegBytes($jpegBytes);
    if ($raw === null) {
        return null;
    }
    $z = authFaceZscore($raw);
    return $z === [] ? null : $z;
}

function authFaceCosineSimilarity(array $a, array $b): float
{
    $n = min(count($a), count($b));
    if ($n < 1) {
        return 0.0;
    }
    $dot = 0.0;
    $na = 0.0;
    $nb = 0.0;
    for ($i = 0; $i < $n; $i++) {
        $dot += $a[$i] * $b[$i];
        $na += $a[$i] * $a[$i];
        $nb += $b[$i] * $b[$i];
    }
    if ($na < 1e-10 || $nb < 1e-10) {
        return 0.0;
    }
    return $dot / (sqrt($na) * sqrt($nb));
}

function authFaceMatchStored(string $absolutePath, string $newJpegBytes, float $minCosine = 0.38): bool
{
    if (!is_file($absolutePath)) {
        return false;
    }
    $stored = @file_get_contents($absolutePath);
    if ($stored === false || $stored === '') {
        return false;
    }
    $rawA = authFaceRawLumaFromJpegBytes($stored);
    $rawB = authFaceRawLumaFromJpegBytes($newJpegBytes);
    if ($rawA === null || $rawB === null) {
        return false;
    }

    $pairs = [
        [$rawA, $rawB],
        [$rawA, authFaceFlipH64($rawB)],
        [authFaceFlipH64($rawA), $rawB],
        [authFaceFlipH64($rawA), authFaceFlipH64($rawB)],
    ];

    $best = 0.0;
    foreach ($pairs as [$a, $b]) {
        $za = authFaceZscore($a);
        $zb = authFaceZscore($b);
        if ($za === [] || $zb === []) {
            continue;
        }
        $best = max($best, authFaceCosineSimilarity($za, $zb));
    }

    return $best >= $minCosine;
}

function authFaceSaveEnrollmentFile(int $userId, string $jpegBinary): ?string
{
    $root = dirname(__DIR__) . '/uploads/face_auth/';
    if (!is_dir($root)) {
        if (!@mkdir($root, 0755, true)) {
            return null;
        }
    }
    $rel = 'uploads/face_auth/' . $userId . '.jpg';
    $abs = dirname(__DIR__) . '/' . $rel;
    if (file_put_contents($abs, $jpegBinary) === false) {
        return null;
    }
    return $rel;
}
