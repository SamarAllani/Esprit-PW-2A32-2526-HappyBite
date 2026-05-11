<?php

require_once __DIR__ . '/../config/openai_key.php';

class AiRecetteController
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = hb_openai_api_key();
    }

    public function genererMenuSemaine($produitsFrigo, $profilSante = null)
    {
        if (empty($produitsFrigo)) {
            return "[]";
        }

        usort($produitsFrigo, function ($a, $b) {
            return strtotime($a['date_ajout'] ?? 'now') - strtotime($b['date_ajout'] ?? 'now');
        });

        $ingredientsAvecDates = [];

        foreach ($produitsFrigo as $produit) {
            if (!empty($produit['nom'])) {
                $ingredientsAvecDates[] = $produit['nom'] . " depuis le " . ($produit['date_ajout'] ?? 'date inconnue');
            }
        }

        $ingredientsAvecDates = array_slice(array_unique($ingredientsAvecDates), 0, 12);

        $objectif = $profilSante['objectif'] ?? 'non précisé';
        $allergenes = $profilSante['allergenes'] ?? 'aucune allergie';
        $maladies = $profilSante['maladies'] ?? 'aucune maladie';
        $carences = $profilSante['carences'] ?? 'aucune carence';

        $prompt = "Tu es ChefBot, un assistant alimentaire intelligent.

Crée exactement 7 recettes différentes pour une semaine.

Profil santé :
Objectif : $objectif
Maladies : $maladies
Allergènes : $allergenes
Carences : $carences

Produits du frigo du plus ancien au plus récent :
" . implode("\n", $ingredientsAvecDates) . "

Règles :
- Une recette pour chaque jour : Lundi, Mardi, Mercredi, Jeudi, Vendredi, Samedi, Dimanche.
- Chaque recette utilise seulement 2 à 4 produits du frigo.
- Tu peux ajouter seulement : sel, poivre, huile, eau.
- Priorise les aliments les plus anciens pour limiter le gaspillage.
- Respecte l'état de santé du client.
- Si un produit ne convient pas à son état de santé, évite-le.
- Réponds uniquement en JSON valide, sans texte avant ni après.

Format exact :
[
  {
    \"jour\": \"Lundi\",
    \"objectif\": \"perte de poids\",
    \"sante\": \"Diabète, allergènes : Gluten,Lactose, carence : Fer\",
    \"produits_prioritaires\": \"pomme depuis le 2026-04-23, carotte depuis le 2026-04-23\",
    \"titre\": \"...\",
    \"ingredients\": [\"...\", \"...\"],
    \"etapes\": [\"...\", \"...\"],
    \"pourquoi\": \"...\"
  }
]";

        if (empty($this->apiKey)) {
            return $this->genererMenuSemaineLocal($produitsFrigo, $profilSante);
        }

        $reponse = $this->appelOpenAI($prompt);
        $menuDecode = json_decode((string) $reponse, true);

        if (!is_array($menuDecode) || empty($menuDecode)) {
            return $this->genererMenuSemaineLocal($produitsFrigo, $profilSante);
        }

        return $reponse;
    }

    private function genererMenuSemaineLocal(array $produitsFrigo, ?array $profilSante = null): string
    {
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        usort($produitsFrigo, function ($a, $b) {
            return strtotime($a['date_ajout'] ?? 'now') - strtotime($b['date_ajout'] ?? 'now');
        });

        $nomsProduits = [];
        foreach ($produitsFrigo as $produit) {
            $nom = trim((string) ($produit['nom'] ?? ''));
            if ($nom !== '') {
                $nomsProduits[] = $nom;
            }
        }

        $nomsProduits = array_values(array_unique($nomsProduits));
        if (empty($nomsProduits)) {
            return "[]";
        }

        $objectif = trim((string) ($profilSante['objectif'] ?? 'équilibre alimentaire'));
        $santeResume = trim((string) ($profilSante['maladies'] ?? 'aucune maladie'));
        $allergenes = trim((string) ($profilSante['allergenes'] ?? 'aucun'));
        $carences = trim((string) ($profilSante['carences'] ?? 'aucune'));

        $menu = [];
        $nbProduits = count($nomsProduits);

        foreach ($jours as $index => $jour) {
            $choisis = [];
            $taille = min(3, $nbProduits);

            for ($i = 0; $i < $taille; $i++) {
                $choisis[] = $nomsProduits[($index + $i) % $nbProduits];
            }

            $produitsTexte = implode(', ', $choisis);
            $titre = 'Assiette healthy de ' . $choisis[0] . (isset($choisis[1]) ? ' et ' . $choisis[1] : '');

            $menu[] = [
                'jour' => $jour,
                'objectif' => $objectif !== '' ? $objectif : 'équilibre alimentaire',
                'sante' => 'Maladies: ' . $santeResume . ', allergènes: ' . $allergenes . ', carences: ' . $carences,
                'produits_prioritaires' => $produitsTexte,
                'titre' => $titre,
                'ingredients' => $choisis,
                'etapes' => [
                    'Laver et préparer les ingrédients: ' . $produitsTexte . '.',
                    'Cuire doucement avec un peu d\'huile, sel et poivre.',
                    'Assembler dans une assiette équilibrée et servir chaud.',
                ],
                'pourquoi' => 'Recette basée sur les produits déjà présents dans le frigo pour limiter le gaspillage.',
            ];
        }

        return json_encode($menu, JSON_UNESCAPED_UNICODE);
    }

    private function appelOpenAI($prompt)
    {
        if (empty($this->apiKey)) {
            return "Erreur : clé API manquante.";
        }

        $data = [
            "model" => "gpt-4.1-mini",
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ],
            "temperature" => 0.7
        ];

        $ch = curl_init("https://api.openai.com/v1/chat/completions");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return "Erreur cURL : " . $error;
        }

        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['error']['message'])) {
            return "Erreur OpenAI : " . $result['error']['message'];
        }

        return $result['choices'][0]['message']['content'] ?? "Erreur : réponse vide.";
    }

    public function analyserPlatPhoto($imagePath, $profilSante = null)
{
    $profilSante = is_array($profilSante) ? $profilSante : [];
    $objectif = $profilSante['objectif'] ?? 'non précisé';
    $allergenes = $profilSante['allergenes'] ?? 'aucune allergie';
    $maladies = $profilSante['maladies'] ?? 'aucune maladie';
    $carences = $profilSante['carences'] ?? 'aucune carence';

    $imageData = base64_encode(file_get_contents($imagePath));
    $mimeType = mime_content_type($imagePath);

    $prompt = "Tu es NutriVision, un assistant nutritionnel intelligent.

Analyse le plat sur la photo.

Profil santé :
Objectif : $objectif
Maladies : $maladies
Allergènes : $allergenes
Carences : $carences

Règles :
- Détecte les ingrédients visibles.
- Estime les calories.
- Estime protéines, glucides et lipides.
- Donne un score santé sur 10.
- Explique si le plat est équilibré ou non.
- Propose comment le rééquilibrer selon le profil santé.
- Propose une activité physique adaptée à l’objectif.
- Si objectif = prise de poids, ne propose pas de cardio intense pour brûler les calories.
- Si objectif = perte de poids, propose une activité modérée.
- Si maladie ou allergie détectée, adapte les conseils.
- Réponds uniquement en JSON valide.

Format exact :
{
  \"ingredients_detectes\": [\"...\"],
  \"calories_estimees\": 0,
  \"proteines\": \"0g\",
  \"glucides\": \"0g\",
  \"lipides\": \"0g\",
  \"score_sante\": 0,
  \"niveau\": \"vert/orange/rouge\",
  \"analyse\": \"...\",
  \"reequilibrage\": [\"...\", \"...\"],
  \"sport_conseille\": \"...\",
  \"avertissement_sante\": \"...\"
}";

    if (empty($this->apiKey)) {
        return $this->analyserPlatPhotoLocal($profilSante);
    }

    return $this->appelOpenAIVision($prompt, $imageData, $mimeType);
}

    /**
     * Estimation locale (sans OpenAI) quand aucune clé API n'est configurée.
     */
    private function analyserPlatPhotoLocal(array $profilSante): string
    {
        $objectif = trim((string) ($profilSante['objectif'] ?? 'équilibre'));
        $allergenes = trim((string) ($profilSante['allergenes'] ?? 'aucune'));
        $maladies = trim((string) ($profilSante['maladies'] ?? 'aucune'));

        $data = [
            'ingredients_detectes' => ['Repas photographié (estimation locale)'],
            'calories_estimees' => 480,
            'proteines' => '22 g',
            'glucides' => '48 g',
            'lipides' => '16 g',
            'score_sante' => 6,
            'niveau' => 'orange',
            'analyse' => 'Analyse approximative sans vision IA : aucune clé OPENAI_API_KEY n\'est configurée sur le serveur. '
                . 'Ajoutez la variable d\'environnement OPENAI_API_KEY (ou la constante PHP OPENAI_API_KEY) pour une analyse photo réelle. '
                . 'Profil pris en compte : objectif « ' . $objectif . ' », allergies « ' . $allergenes . ' », pathologies « ' . $maladies . ' ».',
            'reequilibrage' => [
                'Compléter avec une portion de légumes verts ou une salade.',
                'Privilégier une cuisson simple (vapeur, four) et limiter les fritures.',
            ],
            'sport_conseille' => stripos($objectif, 'perte') !== false
                ? 'Marche rapide ou vélo doux 30–40 min, 4 fois par semaine.'
                : 'Marche quotidienne 20–30 min + renforcement léger 2 fois par semaine.',
            'avertissement_sante' => '',
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

private function appelOpenAIVision($prompt, $imageData, $mimeType)
{
    $data = [
        "model" => "gpt-4.1-mini",
        "messages" => [
            [
                "role" => "user",
                "content" => [
                    [
                        "type" => "text",
                        "text" => $prompt
                    ],
                    [
                        "type" => "image_url",
                        "image_url" => [
                            "url" => "data:$mimeType;base64,$imageData"
                        ]
                    ]
                ]
            ]
        ],
        "temperature" => 0.4
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $this->apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return "Erreur cURL : " . $error;
    }

    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['error']['message'])) {
        return "Erreur OpenAI : " . $result['error']['message'];
    }

    return $result['choices'][0]['message']['content'] ?? "Erreur : réponse vide.";
}
public function proposerAlternativeBudgetSante($produitCher, $budget, $profilSante = null)
{
    $profilSante = is_array($profilSante) ? $profilSante : [];
    $objectif = $profilSante['objectif'] ?? 'non précisé';
    $allergenes = $profilSante['allergenes'] ?? 'aucune allergie';
    $maladies = $profilSante['maladies'] ?? 'aucune maladie';
    $carences = $profilSante['carences'] ?? 'aucune carence';

    $prompt = "Tu es BudgetBot, un assistant alimentaire intelligent.

Produit jugé cher : $produitCher
Budget disponible : $budget DT

Profil santé :
Objectif : $objectif
Maladies : $maladies
Allergènes : $allergenes
Carences : $carences

Donne une alternative alimentaire moins chère et adaptée au profil santé.
Exemples :
- saumon -> thon ou sardine
- lait -> lait d'amande si lactose
- pain normal -> pain sans gluten si gluten

Réponds en 4 lignes maximum :
Produit remplacé :
Alternative proposée :
Pourquoi :
Attention santé :";

    if (empty($this->apiKey)) {
        return $this->proposerAlternativeBudgetSanteLocal($produitCher, $budget, $profilSante);
    }

    return $this->appelOpenAI($prompt);
}

    private function proposerAlternativeBudgetSanteLocal(string $produitCher, string $budget, array $profilSante): string
    {
        $budgText = trim($budget) !== '' ? trim($budget) . ' DT' : 'non renseigné';
        $objectif = trim((string) ($profilSante['objectif'] ?? 'non précisé'));
        $allergenes = trim((string) ($profilSante['allergenes'] ?? 'aucune allergie déclarée'));
        $maladies = trim((string) ($profilSante['maladies'] ?? 'aucune maladie déclarée'));
        $carences = trim((string) ($profilSante['carences'] ?? 'aucune carence déclarée'));

        return "Produit remplacé : " . $produitCher . "\n"
            . "Alternative proposée : privilégiez une option du même rayon moins chère (marque distributeur, format familial ou surgelé) en restant sous votre budget (" . $budgText . ").\n"
            . "Pourquoi : suggestion hors ligne : aucune clé OPENAI_API_KEY n'est configurée sur le serveur. Avec une clé API, l'assistant pourra affiner l'alternative.\n"
            . "Attention santé : objectif « " . $objectif . " », maladies : " . $maladies . ", allergies : " . $allergenes . ", carences : " . $carences . ". Vérifiez toujours les étiquettes.";
    }
}