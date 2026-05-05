<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['answer' => 'Methode non autorisee.']);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '{}', true);
if (!is_array($payload)) {
    $payload = [];
}

$questionRaw = trim((string) ($payload['question'] ?? ''));
$page = strtolower(trim((string) ($payload['page'] ?? '')));

if ($questionRaw === '') {
    echo json_encode(['answer' => 'Veuillez ecrire une question.']);
    exit;
}

$normalize = static function (string $text): string {
    $text = mb_strtolower(trim($text), 'UTF-8');
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = preg_replace('/[^\pL\pN\s]/u', ' ', $text) ?? $text;
    $text = preg_replace('/\s+/', ' ', $text) ?? $text;
    return trim($text);
};

$containsOneOf = static function (string $normalizedText, array $needles): bool {
    foreach ($needles as $needle) {
        if ($needle !== '' && str_contains($normalizedText, $needle)) {
            return true;
        }
    }
    return false;
};

$questionNorm = $normalize($questionRaw);

// API badword (local moderation layer).
$badWords = [
    'con', 'connard', 'idiot', 'imbecile', 'pute', 'merde', 'salope', 'encule',
    'fuck', 'shit', 'bitch', 'asshole',
];
if ($containsOneOf($questionNorm, $badWords)) {
    echo json_encode([
        'answer' => 'Merci de rester respectueux. Reformulez votre question sans termes offensants.'
    ]);
    exit;
}

// Business priority rule requested by teacher/user: tracking link.
$trackingNeedles = [
    'ou est ma commande',
    'quand ma commande arrivera',
    'where is my order',
    'where is my commande',
    'when will my order arrive',
    'suivre ma commande',
];
if ($containsOneOf($questionNorm, $trackingNeedles)) {
    $answer = $page !== 'commande.php'
        ? 'Voici ou se trouve votre commande :'
        : 'Finalisez d abord votre commande, puis utilisez ce lien :';
    echo json_encode([
        'answer' => $answer,
        'link' => [
            'url' => 'track.php',
            'label' => 'Suivre ma commande'
        ],
    ]);
    exit;
}

// API externe / API chat / H.Face (Hugging Face Inference API).
$hfApiKey = getenv('HF_API_KEY');
$hfModel = getenv('HF_MODEL') ?: 'google/flan-t5-base';
$externalAnswer = null;

if (is_string($hfApiKey) && trim($hfApiKey) !== '') {
    $prompt = "Tu es l assistant HappyBite. Reponds en francais, de maniere breve et utile.\nQuestion utilisateur: " . $questionRaw;
    $url = 'https://api-inference.huggingface.co/models/' . rawurlencode($hfModel);
    $requestBody = json_encode([
        'inputs' => $prompt,
        'parameters' => [
            'max_new_tokens' => 120,
            'temperature' => 0.6,
            'return_full_text' => false,
        ],
    ], JSON_UNESCAPED_UNICODE);

    if ($requestBody !== false) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $hfApiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $requestBody,
            CURLOPT_TIMEOUT => 18,
            CURLOPT_CONNECTTIMEOUT => 8,
        ]);

        $responseBody = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (is_string($responseBody) && $statusCode >= 200 && $statusCode < 300) {
            $decoded = json_decode($responseBody, true);
            if (is_array($decoded)) {
                if (isset($decoded[0]['generated_text']) && is_string($decoded[0]['generated_text'])) {
                    $externalAnswer = trim($decoded[0]['generated_text']);
                } elseif (isset($decoded['generated_text']) && is_string($decoded['generated_text'])) {
                    $externalAnswer = trim($decoded['generated_text']);
                }
            }
        }
    }
}

if (is_string($externalAnswer) && $externalAnswer !== '') {
    echo json_encode(['answer' => $externalAnswer], JSON_UNESCAPED_UNICODE);
    exit;
}

// Deterministic fallback replies if external API unavailable.
if ($containsOneOf($questionNorm, ['paypal est il securise', 'paiement avec carte est il securise', 'payement avec carte est il securise'])) {
    echo json_encode(['answer' => 'Oui, le paiement est securise sur HappyBite.']);
    exit;
}
if ($containsOneOf($questionNorm, ['annuler ma commande', 'possible d annuler'])) {
    echo json_encode(['answer' => 'Oui, vous pouvez annuler une commande tant qu elle n est pas expediée.']);
    exit;
}
if ($containsOneOf($questionNorm, ['mode de paiement', 'carte', 'cash', 'paypal'])) {
    echo json_encode(['answer' => 'Vous pouvez choisir Carte, Cash ou PayPal selon votre preference.']);
    exit;
}

echo json_encode([
    'answer' => 'Je suis la pour vous aider. Posez-moi une question sur votre commande, livraison, paiement ou suivi.'
], JSON_UNESCAPED_UNICODE);
