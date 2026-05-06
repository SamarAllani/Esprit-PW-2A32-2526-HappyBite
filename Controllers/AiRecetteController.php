<?php

class AiRecetteController
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = "cle";
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

        return $this->appelOpenAI($prompt);
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

    return $this->appelOpenAIVision($prompt, $imageData, $mimeType);
}

private function appelOpenAIVision($prompt, $imageData, $mimeType)
{
    if (empty($this->apiKey)) {
        return "Erreur : clé API manquante.";
    }

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

    return $this->appelOpenAI($prompt);
}
}