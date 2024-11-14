<?php

namespace nrv\action;

use nrv\exception\InvalidPropertyNameException;
use nrv\festival\Spectacle;
use nrv\renderer\SpectacleFiltersListRenderer;
use nrv\renderer\SpectacleRenderer;
use nrv\renderer\SpectaclesListRenderer;
use nrv\repository\NrvRepository;

/**
 * Action to display all the spectacles by location
 */
class DisplaySpectaclesByLocation extends Action {

    /**
     * Displays the list of all the spectacles by location
     * @return string The HTML code of the list
     * @throws InvalidPropertyNameException
     */
    public function executeGet(): string {
        // Get the location from the URL
        $lieu = $_GET['lieu'] ?? '';
        $repository = NrvRepository::getInstance();
        if (empty($lieu)) {
            return "<p>Veuillez spécifier un lieu.</p>";
        }

        $spectacles = $repository->findSpectaclesByLieu($lieu);
        $filtersRenderer = new SpectacleFiltersListRenderer();

        // Exit if no spectacles are found
        if (empty($spectacles)) {
            return <<<FIN
            <h2 class="p-2">Aucun spectacles pour le lieu : $lieu</h2>
            <div class="col-3">
                {$filtersRenderer->render()}
            </div>
            <hr>
            <ul>
        FIN;
        }

        $html = <<<FIN
            <h2 class="p-2">Spectacles dans le lieu $lieu</h2>
            <div class="col-3">
                {$filtersRenderer->render()}
            </div>
            <hr>

        FIN;

        // Create the list of spectacles
        $spectaclesListRenderer = new SpectaclesListRenderer();
        return $html . $spectaclesListRenderer->renderSpectacleList($spectacles);
    }

    /**
     * Displays the list of all the spectacles by location
     * @return string The HTML code of the list
     * @throws InvalidPropertyNameException
     */
    public function executePost(): string {
        return $this->executeGet();
    }
}