<?php

declare(strict_types=1);

/**
 * Charge config/secrets.php une seule fois (clé OpenAI pour XAMPP / Windows sans variable d’environnement).
 */
if (!defined('HB_OPENAI_BOOTSTRAP')) {
    define('HB_OPENAI_BOOTSTRAP', true);
    $secrets = __DIR__ . '/secrets.php';
    if (is_file($secrets)) {
        require_once $secrets;
    }
}

function hb_openai_api_key(): string
{
    $trim = static function ($v): string {
        if (!is_string($v)) {
            return '';
        }
        $t = trim($v);
        return $t;
    };

    $v = $trim(getenv('OPENAI_API_KEY') ?: '');
    if ($v !== '') {
        return $v;
    }
    if (isset($_ENV['OPENAI_API_KEY'])) {
        $v = $trim((string) $_ENV['OPENAI_API_KEY']);
        if ($v !== '') {
            return $v;
        }
    }
    if (isset($_SERVER['OPENAI_API_KEY'])) {
        $v = $trim((string) $_SERVER['OPENAI_API_KEY']);
        if ($v !== '') {
            return $v;
        }
    }

    // Fichier texte (une ligne) — pratique sous XAMPP sans toucher au PHP
    $keyFile = __DIR__ . DIRECTORY_SEPARATOR . 'openai.key';
    if (is_readable($keyFile)) {
        $raw = @file_get_contents($keyFile);
        if (is_string($raw) && $raw !== '') {
            foreach (preg_split('/\R/u', $raw) as $line) {
                $line = $trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                return $line;
            }
        }
    }

    if (defined('OPENAI_API_KEY')) {
        $v = $trim((string) constant('OPENAI_API_KEY'));
        if ($v !== '') {
            return $v;
        }
    }

    return '';
}
