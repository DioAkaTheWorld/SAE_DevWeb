<?php
declare(strict_types=1);

namespace nrv\action;

use nrv\auth\User;
use nrv\exception\InvalidPropertyNameException;
use nrv\festival\Spectacle;
use nrv\renderer\SpectacleRenderer;
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
        if ($this->checkUser(User::STANDARD_USER) !== "") {
            return $spectacleRenderer->renderAsLong($artistes, $images) . $soireeLink;
        }

        // Display the spectacle with the button to modify it
        return $spectacleRenderer->renderAsLong($artistes, $images) . "<a class='btn btn-primary m-3' href='?action=modify-spectacle&id=$spectacleId'>Modifier ce spectacle</a>" . $soireeLink;
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
