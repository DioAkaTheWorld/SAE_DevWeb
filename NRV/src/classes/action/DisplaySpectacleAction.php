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
            $spectacleDetails = $repository->getSpectacleDetails($spectacleId );
            $artistes = $repository->getArtistsFromSpectacle($spectacleId);
            $images = $repository->getSpectacleImages($spectacleId);

            $date = $repository->getDateSpectacle($spectacleId);
            $spectacleByDate = $repository->findSpectaclesByDate($date);
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

        // Render the related spectacles
        $spectacleListRenderer = new SpectaclesListRenderer();
        $relatedSpectacles = <<<FIN
        <h2 class='my-5'>Voir les spectacles de la même date :</h2>
        {$spectacleListRenderer->renderSpectacleList($spectacleByDate)}
        <h2 class='my-5'>Voir les spectacles du même style :</h2>
        {$spectacleListRenderer->renderSpectacleList($spectacleByStyle)}
        <h2 class='my-5'>Voir les spectacles du même lieu : </h2>
        {$spectacleListRenderer->renderSpectacleList($spectacleByLieu)}
        
        FIN;

        // Check if the user is connected and has the right role to display the button to modify the spectacle
        if ($this->checkUser(User::ADMIN) == "") {
            return $spectacleRenderer->renderAsLong($artistes, $images) . "<a class='btn btn-primary m-3' href='?action=modify-spectacle&id=$spectacleId'>Modifier ce spectacle</a>" . $soireeLink . $relatedSpectacles;
        }

        return $spectacleRenderer->renderAsLong($artistes, $images) . $soireeLink . $relatedSpectacles;
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
