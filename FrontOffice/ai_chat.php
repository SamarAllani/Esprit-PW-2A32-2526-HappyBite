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

$containsAll = static function (string $normalizedText, array $needles): bool {
    foreach ($needles as $needle) {
        if ($needle === '') {
            continue;
        }
        if (!str_contains($normalizedText, $needle)) {
            return false;
        }
    }
    return true;
};

$detectLanguage = static function (string $normalizedText): string {
    $englishSignals = [
        'where', 'order', 'track', 'shipping', 'delivery', 'arrive', 'cancel',
        'payment', 'paypal', 'card', 'cash', 'secure', 'help', 'my', 'i',
        'what', 'about', 'thanks', 'thank you', 'bye', 'goodbye',
    ];
    $frenchSignals = [
        'ou', 'commande', 'suivi', 'livraison', 'arrivera', 'annuler',
        'paiement', 'carte', 'cash', 'securise', 'aide', 'ma', 'mon',
        'merci', 'au revoir', 'salut',
    ];
    $enScore = 0;
    $frScore = 0;
    foreach ($englishSignals as $word) {
        if (preg_match('/\b' . preg_quote($word, '/') . '\b/u', $normalizedText)) {
            $enScore++;
        }
    }
    foreach ($frenchSignals as $word) {
        if (preg_match('/\b' . preg_quote($word, '/') . '\b/u', $normalizedText)) {
            $frScore++;
        }
    }
    return $enScore > $frScore ? 'en' : 'fr';
};

$questionNorm = $normalize($questionRaw);
$lang = $detectLanguage($questionNorm);

// API badword (local moderation layer).
$badWords = [
    'con', 'connard', 'idiot', 'imbecile', 'pute', 'merde', 'salope', 'encule',
    'fuck', 'shit', 'bitch', 'asshole',
];
if ($containsOneOf($questionNorm, $badWords)) {
    echo json_encode([
        'answer' => $lang === 'en'
            ? 'Please keep it respectful. Rephrase your question without offensive language.'
            : 'Merci de rester respectueux. Reformulez votre question sans termes offensants.'
    ]);
    exit;
}

// Business priority rule requested by teacher/user: tracking link.
$isTrackingIntent = $containsOneOf($questionNorm, [
    'ou est ma commande',
    'quand ma commande arrivera',
    'suivre ma commande',
    'ou en est ma commande',
    'where is my order',
    'where s my order',
    'when will my order arrive',
    'track my order',
    'order status',
]) || (
    $containsOneOf($questionNorm, ['commande', 'order']) &&
    $containsOneOf($questionNorm, ['ou', 'where', 'suivre', 'track', 'status', 'arrive', 'arrivera'])
);

if ($isTrackingIntent) {
    $answer = $lang === 'en'
        ? ($page !== 'commande.php'
            ? 'Here is your order tracking link:'
            : 'Please complete your order first, then use this tracking link:')
        : ($page !== 'commande.php'
            ? 'Voici le lien de suivi de votre commande :'
            : 'Finalisez d abord votre commande, puis utilisez ce lien :');
    $linkLabel = $lang === 'en' ? 'Track my order' : 'Suivre ma commande';
    echo json_encode([
        'answer' => $answer,
        'link' => [
            'url' => 'track.php',
            'label' => $linkLabel
        ],
    ]);
    exit;
}

// API externe / API chat / H.Face (Hugging Face Inference API).
$hfApiKey = getenv('HF_API_KEY');
$hfModel = getenv('HF_MODEL') ?: 'google/flan-t5-base';
$externalAnswer = null;

if (is_string($hfApiKey) && trim($hfApiKey) !== '') {
    $langInstruction = $lang === 'en'
        ? 'Reply in English only, concise and helpful.'
        : 'Reponds en francais uniquement, de maniere breve et utile.';
    $prompt = "You are the HappyBite assistant. " . $langInstruction . "\nUser question: " . $questionRaw;
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
if (
    $containsOneOf($questionNorm, [
        'paypal est il securise', 'paiement avec carte est il securise', 'payement avec carte est il securise',
        'is paypal secure', 'is card payment secure', 'is payment secure',
        'is paypal safe', 'paypal safe', 'is card safe',
    ]) ||
    ($containsOneOf($questionNorm, ['paypal', 'payment', 'paiement', 'card', 'carte']) &&
        $containsOneOf($questionNorm, ['secure', 'securise']))
) {
    echo json_encode([
        'answer' => $lang === 'en'
            ? 'Yes, payment is secure on HappyBite.'
            : 'Oui, le paiement est securise sur HappyBite.'
    ]);
    exit;
}
if (
    $containsOneOf($questionNorm, ['annuler ma commande', 'possible d annuler', 'cancel my order', 'can i cancel my order']) ||
    ($containsOneOf($questionNorm, ['annuler', 'cancel']) && $containsOneOf($questionNorm, ['commande', 'order']))
) {
    echo json_encode([
        'answer' => $lang === 'en'
            ? 'Yes, you can cancel an order as long as it has not been shipped.'
            : 'Oui, vous pouvez annuler une commande tant qu elle n est pas expediee.'
    ]);
    exit;
}
if (
    $containsOneOf($questionNorm, [
        'mode de paiement', 'payment method', 'payment methods',
        'which payment method', 'what payment method', 'quel mode de paiement',
    ]) ||
    (
        $containsOneOf($questionNorm, ['carte', 'cash', 'paypal', 'card']) &&
        $containsOneOf($questionNorm, ['mode', 'method', 'paiement', 'payment', 'choose', 'choisir'])
    )
) {
    echo json_encode([
        'answer' => $lang === 'en'
            ? 'You can choose Card, Cash, or PayPal depending on your preference.'
            : 'Vous pouvez choisir Carte, Cash ou PayPal selon votre preference.'
    ]);
    exit;
}

echo json_encode([
    'answer' => (function () use ($questionNorm, $lang, $containsOneOf, $containsAll): string {
        if ($containsOneOf($questionNorm, ['thanks', 'thank you', 'thx', 'merci'])) {
            return $lang === 'en'
                ? 'You are welcome. If you want, I can also help you with products, recipes, or tracking your order.'
                : 'Avec plaisir. Je peux aussi vous aider pour les produits, les recettes ou le suivi de commande.';
        }
        if ($containsOneOf($questionNorm, ['bye', 'goodbye', 'see you', 'au revoir', 'a bientot'])) {
            return $lang === 'en'
                ? 'Goodbye! Have a great day. I am here if you need help later.'
                : 'Au revoir ! Bonne journee. Je suis la si vous avez besoin d aide plus tard.';
        }
        if ($containsOneOf($questionNorm, ['recette', 'recettes', 'recipe', 'recipes', 'plat', 'meal'])) {
            return $lang === 'en'
                ? 'You can explore the recipes section, open details, and filter to find meals that match your needs.'
                : 'Vous pouvez explorer la section recettes, ouvrir les details et filtrer pour trouver des plats adaptes.';
        }
        if ($containsOneOf($questionNorm, ['produit', 'produits', 'product', 'products', 'categorie', 'category', 'categories'])) {
            return $lang === 'en'
                ? 'You can browse products by category, check details, and compare prices or promotions.'
                : 'Vous pouvez parcourir les produits par categorie, voir les details et comparer les prix ou promotions.';
        }
        if ($containsOneOf($questionNorm, ['frigo', 'refrigerateur', 'refrigerator', 'fridge'])) {
            return $lang === 'en'
                ? 'You can add items to your Frigo and review them from the Frigo page to manage what you already have.'
                : 'Vous pouvez ajouter des elements au Frigo puis les consulter depuis la page Frigo pour gerer ce que vous avez deja.';
        }
        if ($containsOneOf($questionNorm, ['allerg', 'allergen', 'allergene', 'allergenes', 'calorie', 'calories', 'sante', 'health'])) {
            return $lang === 'en'
                ? 'Check product/recipe details for allergens, calories, and health indicators before choosing.'
                : 'Consultez les details des produits/recettes pour les allergenes, calories et indicateurs sante avant de choisir.';
        }
        if ($containsOneOf($questionNorm, ['promo', 'promotion', 'discount', 'cheap', 'moins cher', 'budget'])) {
            return $lang === 'en'
                ? 'You can search promoted products and compare prices directly from the product list.'
                : 'Vous pouvez rechercher les produits en promotion et comparer les prix directement dans la liste produits.';
        }
        if ($containsOneOf($questionNorm, ['bonjour', 'salut', 'hello', 'hi', 'hey'])) {
            return $lang === 'en'
                ? 'Hi! I can help with products, recipes, Frigo, payments, delivery, and order tracking.'
                : 'Bonjour ! Je peux vous aider pour les produits, recettes, frigo, paiement, livraison et suivi de commande.';
        }
        if ($containsAll($questionNorm, ['comment', 'utiliser']) || $containsOneOf($questionNorm, ['how to use', 'how does it work', 'guide'])) {
            return $lang === 'en'
                ? 'Quick guide: choose products/recipes, add to panier, validate commande, then follow delivery from track.'
                : 'Guide rapide : choisissez produits/recettes, ajoutez au panier, validez la commande puis suivez la livraison via track.';
        }
        return $lang === 'en'
            ? 'I do not understand that question. I am programmed to answer about products, recipes, frigo, orders, delivery, payments, and tracking.'
            : 'Je ne comprends pas cette question. Je suis programme pour repondre sur les produits, recettes, frigo, commande, livraison, paiement et suivi.';
    })()
], JSON_UNESCAPED_UNICODE);
