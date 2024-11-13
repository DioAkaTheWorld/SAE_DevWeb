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

        $spectacleId = (int)$_GET['id'];

        // Obtenir les détails du spectacle depuis le dépôt
        try {
            $repository = NrvRepository::getInstance();
            $spectacleDetails = $repository->getSpectacleDetails($spectacleId);
            $artistes = $repository->getArtistsFromSpectacle($spectacleId);
            $images = $repository->getSpectacleImages($spectacleId);

            // Récupérer les informations nécessaires pour les liens similaires
            $date = $repository->getDateSpectacle($spectacleId);
            $style = $spectacleDetails['style'];
            $lieuId = $repository->getSoireeIdBySpectacleId($spectacleId);
        } catch (Exception $e) {
            return "<p>Erreur lors de la récupération des informations du spectacle : {$e->getMessage()}</p>";
        }

        $spectacleObjet = new Spectacle(
            $spectacleDetails['titre'],
            $spectacleDetails['description'],
            $spectacleDetails['horaire'],
            $spectacleDetails['duree'],
            $spectacleDetails['style'],
            $spectacleDetails['chemin_video']
        );
        $spectacleObjet->setId($spectacleId);
        $spectacleRenderer = new SpectacleRenderer($spectacleObjet);

        // Affichage détaillé du spectacle avec le lien vers les spectacles similaires
        return $spectacleRenderer->renderAsLong($artistes, $images)
            . $this->renderRelatedLinks($date, $style, $lieuId)
            . "<a href='?action=modify-spectacle&id={$spectacleId}' class='btn-modify'>Modifier ce spectacle</a>";
    }

    private function renderRelatedLinks(?string $date, ?string $style, ?int $lieuId): string {
        // Générer les URLs pour chaque filtre si les valeurs sont définies
        $dateUrl = $date ? "?action=display-spectacles-by-date&date=" . urlencode($date) : null;
        $styleUrl = $style ? "?action=display-spectacles-by-style&style=" . urlencode($style) : null;
        $lieuUrl = $lieuId ? "?action=display-spectacles-by-lieu&lieuId=" . urlencode((string)$lieuId) : null;

        // Afficher les liens vers les spectacles similaires
        return <<<FIN
            <hr>
            <h3>Voir d'autres spectacles similaires</h3>
            <ul class="related-links">
                <li><a href="{$dateUrl}">Autres spectacles à la même date : {$date}</a></li>
                <li><a href="{$styleUrl}">Autres spectacles du même style : {$style}</a></li>
                <li><a href="{$lieuUrl}">Autres spectacles au même lieu : </a></li>
            </ul>
        FIN;
    }

    public function executePost(): string {
        return $this->executeGet();
    }
}
