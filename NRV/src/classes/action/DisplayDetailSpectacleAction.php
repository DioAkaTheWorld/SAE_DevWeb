<?php
declare(strict_types=1);

namespace nrv\action;

use nrv\festivale\Spectacle;
use nrv\renderer\SpectacleRenderer;
use nrv\repository\NrvRepository;
use Exception;

class DisplayDetailSpectacleAction extends Action {

    public function executeGet(): string {
        // Vérifier si l'ID du spectacle est spécifié
        if (!isset($_GET['id'])) {
            http_response_code(400);
            return <<<FIN
            <div class="container d-flex flex-column justify-content-center align-items-center h3">
                        <h2 class="h1">Erreur 400</h2>
                        ID du spectacle manquant
                    </div>
            FIN;
        }

        // Vérifier si l'ID du spectacle est spécifié
        $spectacleId = (int)$_GET['id'];

        // Obtenir les détails du spectacle depuis le dépôt
        try {
            $repository = NrvRepository::getInstance();
            $spectacleDetails = $repository->getSpectacleDetails($spectacleId );
            $artistes = $repository->getArtistsFromSpectacle($spectacleId);
            $images = $repository->getSpectacleImages($spectacleId);

            // Récupérer l'ID de la soirée associée à ce spectacle
            $soireeId = $repository->getSoireeIdBySpectacleId($spectacleId);
        } catch (Exception $e) {
            return "<p>Erreur lors de la récupération des informations du spectacle : {$e->getMessage()}</p>";
        }

        $spectacleObjet = new Spectacle($spectacleDetails['titre'], $spectacleDetails['description'], $spectacleDetails['horaire'], $spectacleDetails['duree'], $spectacleDetails['style'], $spectacleDetails['chemin_video']);
        $spectacleObjet->setId($spectacleId);
        $spectacleRenderer = new SpectacleRenderer($spectacleObjet);

        // Créer le lien vers la soirée associée au spectacle
        $soireeLink = "";
        if ($soireeId) {
            $soireeLink = "<a href='?action=display-detail-soiree&id={$soireeId}'>Voir la soirée associée</a>";
        }

        // Affichage détaillé du spectacle avec le lien vers la soirée
        return $spectacleRenderer->renderAsLong($artistes, $images) . "<a href='?action=modify-spectacle&id={$spectacleId}'>Modifier ce spectacle</a>" . $soireeLink;
    }

    public function executePost(): string {
        return $this->executeGet();
    }
}
