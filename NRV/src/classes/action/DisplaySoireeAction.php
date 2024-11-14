<?php
declare(strict_types=1);

namespace nrv\action;

use nrv\exception\InvalidPropertyNameException;
use nrv\festival\Soiree;
use nrv\renderer\SoireeRenderer;
use nrv\repository\NrvRepository;
use Exception;

/**
 * Display a party
 */
class DisplaySoireeAction extends Action {

    /**
     * Displays the details of a party
     * @return string The HTML code of the party
     * @throws InvalidPropertyNameException
     */
    public function executeGet(): string {
        // Check if the party ID is set
        if (!isset($_GET['id'])) {
            http_response_code(400);
            return <<<FIN
            <div class="container d-flex flex-column justify-content-center align-items-center h3">
                        <h2 class="h1">Erreur 400</h2>
                        ID de la soirée manquant
                    </div>
            FIN;
        }

        $soireeId = (int)$_GET['id'];

        // Get the details of the party
        try {
            $repository = NrvRepository::getInstance();
            $soireeDetails = $repository->getSoireeDetails($soireeId);
            $lieuDetails = $repository->getLieuDetails($soireeDetails['id_lieu']);
            $artistes = $repository->getArtistsFromSpectacle($soireeId);
            $spectacles = $repository->getSpectaclesFromSoiree($soireeId);
        } catch (Exception $e) {
            return "<p>Erreur lors de la récupération des informations de la soirée : {$e->getMessage()}</p>";
        }

        // Create a party object
        $soireeObjet = new Soiree($soireeDetails['nom'], $soireeDetails['thematique'], $soireeDetails['date'], $soireeDetails['horaire_debut'], $soireeDetails['horaire_fin'], $soireeDetails['id_lieu'], $soireeDetails['tarif']);
        $soireeObjet->setId($soireeId);

        // Render the party
        $soireeRenderer = new SoireeRenderer($soireeObjet);
        return $soireeRenderer->renderAsLong($lieuDetails, $spectacles,$artistes);
    }

    /**
     * Displays the details of a party
     * @return string The HTML code of the party
     * @throws InvalidPropertyNameException
     */
    public function executePost(): string {
        return $this->executeGet();
    }
}
