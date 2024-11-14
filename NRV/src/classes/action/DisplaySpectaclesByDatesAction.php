<?php

namespace nrv\action;

use nrv\exception\InvalidPropertyNameException;
use nrv\festival\Spectacle;
use nrv\renderer\SpectacleFiltersListRenderer;
use nrv\renderer\SpectacleRenderer;
use nrv\renderer\SpectaclesListRenderer;
use nrv\repository\NrvRepository;

class DisplaySpectaclesByDatesAction extends Action {

    /**
     * @throws InvalidPropertyNameException
     */
    public function executeGet(): string {
        // Get the date from the URL
        $date = $_GET['date'] ?? '';
        $repository = NrvRepository::getInstance();
        $dateFormatted = date('d/m/Y', strtotime($date));
        if (empty($date)) {
            return "<p>Veuillez spécifier une date.</p>";
        }

        $spectacles = $repository->findSpectaclesByDate($date);
        $filtersRenderer = new SpectacleFiltersListRenderer();

        // Exit if no spectacles are found
        if (empty($spectacles)) {
            return <<<FIN
            <h2 class="p-2">Aucun spectacles pour la date : $dateFormatted</h2>
            <div class="col-3">
                {$filtersRenderer->render()}
            </div>
            <hr>
            <ul>
        FIN;
        }

        $html = <<<FIN
            <h2 class="p-2">Spectacles le $dateFormatted</h2>
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
     * Displays the list of all the spectacles by date
     * @return string The HTML code of the list
     * @throws InvalidPropertyNameException
     */
    public function executePost(): string {
        return $this->executeGet();
    }
}