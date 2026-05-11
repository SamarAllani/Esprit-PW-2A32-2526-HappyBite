<?php
declare(strict_types=1);

class GeminiController
{
    // Mettez votre clé API Gemini ici ou dans un fichier .env
    private string $apiKey = 'AIzaSyCUyU2_qiIDwd6w1BWUYsSqRy8SHRZmTlQ'; 
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function generatePostIdea(): ?string
    {
        $prompt = "Tu es un chef cuisinier expert. Rédige un court post accrocheur pour une communauté de passionnés de cuisine (HappyBite) sur une idée de recette créative ou une astuce culinaire. Ajoute quelques émojis. Sois créatif et engageant. Maximum 3 phrases.";
        return $this->callGemini($prompt);
    }

    public function translateText(string $text, string $targetLang): ?string
    {
        $supported = ['fr', 'en', 'ar', 'de'];
        if (!in_array($targetLang, $supported)) {
            return null;
        }

        $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl=" . $targetLang . "&dt=t&q=" . urlencode($text);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $data = json_decode($response, true);
            if (isset($data[0])) {
                $translated = '';
                foreach ($data[0] as $segment) {
                    if (isset($segment[0])) $translated .= $segment[0];
                }
                return trim($translated);
            }
        }

        return null;
    }

    private function callGemini(string $prompt): ?string
    {
        if ($this->apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
            return "Veuillez configurer votre clé API Gemini dans GeminiController.php.";
        }

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($this->apiUrl . "?key=" . $this->apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Pour éviter les problèmes SSL en local

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return "Erreur de connexion à l'API: $error";
        }

        $decoded = json_decode($response, true);
        
        if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($decoded['candidates'][0]['content']['parts'][0]['text']);
        }
        
        // Renvoyer l'erreur détaillée pour le débogage
        if (isset($decoded['error']['message'])) {
            return "Erreur API: " . $decoded['error']['message'];
        }

        return "Erreur lors de la génération. Réponse: " . substr($response, 0, 100);
    }
}

// Gérer les appels AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'generate_idea') {
        $gemini = new GeminiController();
        $text = $gemini->generatePostIdea();
        echo json_encode(['success' => true, 'text' => $text]);
        exit;
    }
    if ($_POST['action'] === 'translate' && isset($_POST['text']) && isset($_POST['lang'])) {
        $gemini = new GeminiController();
        $text = $gemini->translateText(trim($_POST['text']), $_POST['lang']);
        if ($text !== null) {
            echo json_encode(['success' => true, 'text' => $text]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Traduction impossible. Vérifiez votre connexion.']);
        }
        exit;
    }
}
