<?php

namespace nrv\action;

use nrv\festival\Spectacle;
use nrv\renderer\SpectacleFiltersListRenderer;
use nrv\renderer\SpectacleRenderer;
use nrv\repository\NrvRepository;

/**
 * Action to display all the spectacles by location
 */
class DisplaySpectaclesByLocation extends Action {

    /**
     * Displays the list of all the spectacles by location
     * @return string The HTML code of the list
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
            <h2 class="p-2">Spectacles pour le lieu : $lieu</h2>
            <div class="col-3">
                {$filtersRenderer->render()}
            </div>
            <hr>
            <div class='row row-cols-sm-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 g-4'>
        FIN;

        // Create the list of spectacles
        foreach ($spectacles as $spectacle) {
            $s = new Spectacle($spectacle['titre'], $spectacle['description'], $spectacle['horaire'], $spectacle['duree'], $spectacle['style'], $spectacle['chemin_video']);
            $s->setId($spectacle['id_spectacle']);
            $image = $repository->getImagesSpectacle($spectacle['id_spectacle']);
            $spectacleRenderer = new SpectacleRenderer($s);
            if(!empty($image)) {
                $html .= $spectacleRenderer->renderAsCompact($lieu, $image[0]['chemin_fichier']); // Display the first image
            } else {
                $html .= $spectacleRenderer->renderAsCompact($lieu, "pas d'image");
            }
        }
        return $html . "</div>";
    }

    public function executePost(): string {
        return $this->executeGet();
    }
}