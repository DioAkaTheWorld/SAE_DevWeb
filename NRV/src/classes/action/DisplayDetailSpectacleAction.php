<?php
declare(strict_types=1);

namespace nrv\action;

use nrv\repository\NrvRepository;
use Exception;

class DisplayDetailSpectacleAction extends Action {

    public function executeGet(): string {
        // Vérifier que l'utilisateur est authentifié
        $authCheck = $this->checkAuthentication();
        if ($authCheck) {
            return $authCheck;
        }

        if (!isset($_GET['id'])) {
            http_response_code(400);
            return <<<FIN
            <div class="container d-flex flex-column justify-content-center align-items-center h3">
                        <h2 class="h1">Erreur 400</h2>
                        ID de la playlist manquant
                    </div>
            FIN;
        }

        $spectacleId = (int)$_GET['id'];

        // Obtenir les détails du spectacle depuis le dépôt
        try {
            $repository = NrvRepository::getInstance();
            $spectacleDetails = $repository->getSpectacleDetails($spectacleId );
            $artists = $repository->getSpectacleArtists($spectacleId);
            $images = $repository->getSpectacleImages($spectacleId);
        } catch (Exception $e) {
            return "<p>Erreur lors de la récupération des informations du spectacle : {$e->getMessage()}</p>";
        }

        // Générer le HTML avec les informations du spectacle
        $html = "<h1>{$spectacleDetails['titre']}</h1>";
        $html .= "<p><strong>Style :</strong> {$spectacleDetails['style']}</p>";
        $html .= "<p><strong>Description :</strong> {$spectacleDetails['description']}</p>";
        $html .= "<p><strong>Durée :</strong> {$spectacleDetails['duree']}</p>";

        // Liste des artistes
        $html .= "<h2>Artistes</h2><ul>";
        foreach ($artists as $artist) {
            $html .= "<li>{$artist['nom']}</li>";
        }
        $html .= "</ul>";

        // Images du spectacle
        $html .= "<h2>Images</h2><div class='spectacle-images'>";
        foreach ($images as $image) {
            $html .= "<img src='{$image['chemin_fichier']}' alt='Image du spectacle' style='width: 150px; margin: 5px;'>";
        }
        $html .= "</div>";

        // Extrait vidéo
        if (!empty($spectacleDetails['chemin_fichier'])) {
            $html .= "<h2>Extrait Vidéo</h2>";
            $html .= "<video width='320' height='240' controls>
                        <source src='{$spectacleDetails['chemin_fichier']}' type='video/mp4'>
                      </video>";
        }

        return $html;
    }

    public function executePost(): string {
        return $this->executeGet();
    }
}
