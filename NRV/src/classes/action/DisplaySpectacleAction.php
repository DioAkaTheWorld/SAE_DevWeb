<?php
declare(strict_types=1);

namespace nrv\action;

use nrv\auth\User;
use nrv\exception\InvalidPropertyNameException;
use nrv\festival\Spectacle;
use nrv\renderer\SpectacleRenderer;
use nrv\renderer\SpectaclesListRenderer;
use nrv\repository\NrvRepository;
use Exception;

/**
 * Class to display a spectacle
 */
class DisplaySpectacleAction extends Action {

    /**
     * Displays the details of a spectacle
     * @return string The HTML code of the spectacle
     * @throws InvalidPropertyNameException
     */
    public function executeGet(): string {
        // Check if the spectacle ID is specified
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

        // Get the details of the spectacle
        try {
            $repository = NrvRepository::getInstance();
            $date = $repository->getDateSpectacle($spectacleId);
            $spectacleByDate = $repository->findSpectaclesByDate($date);
            $spectacleDetails = $repository->getSpectacleDetails($spectacleId );
            $artistes = $repository->getArtistsFromSpectacle($spectacleId);
            $images = $repository->getSpectacleImages($spectacleId);
            $style =$repository ->getStyleFromSpectacleId($spectacleId);
            $spectacleByStyle = $repository->findSpectaclesByStyle($style);
            $lieu = $repository ->getLieuFromSpectacleId($spectacleId);
            $spectacleByLieu = $repository->findSpectaclesByLieu($lieu);

            // Get the ID of the party associated with the spectacle
            $soireeId = $repository->getSoireeIdBySpectacleId($spectacleId);
        } catch (Exception $e) {
            return "<p>Erreur lors de la récupération des informations du spectacle: {$e->getMessage()}</p>";
        }

        $spectacleObjet = new Spectacle($spectacleDetails['titre'], $spectacleDetails['description'], $spectacleDetails['horaire'], $spectacleDetails['duree'], $spectacleDetails['style'], $spectacleDetails['chemin_video']);
        $spectacleObjet->setId($spectacleId);
        $spectacleRenderer = new SpectacleRenderer($spectacleObjet);

        // Create the link to the party
        $soireeLink = "";
        if ($soireeId) {
            $soireeLink = "<a class='btn btn-primary my-3' href='?action=display-soiree&id=$soireeId'>Voir la soirée associée</a>";
        }

        // Check if the user is connected and has the right role to display the button to modify the spectacle
        if ($this->checkUser(User::ADMIN) == "") {
            return $spectacleRenderer->renderAsLong($artistes, $images) . "<a class='btn btn-primary m-3' href='?action=modify-spectacle&id=$spectacleId'>Modifier ce spectacle</a>" . $soireeLink;
        }

        $spectacleListRenderer = new SpectaclesListRenderer();

        // Display the spectacle
        return $spectacleRenderer->renderAsLong($artistes, $images) . $soireeLink ."<h2 class='my-5'>Voir les spectacles de la même date :</h2>".
            $spectacleListRenderer->renderSpectacleList($spectacleByDate). "<h2 class='my-5'>Voir les spectacles du même style :</h2>".
            $spectacleListRenderer->renderSpectacleList($spectacleByStyle). "<h2 class='my-5'>Voir les spectacles du même lieu : </h2>".
            $spectacleListRenderer->renderSpectacleList($spectacleByLieu);
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

    /**
     * Displays the details of a spectacle
     * @return string The HTML code of the spectacle
     * @throws InvalidPropertyNameException
     */
    public function executePost(): string {
        return $this->executeGet();
    }
}
