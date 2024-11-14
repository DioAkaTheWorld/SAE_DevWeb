<?php
namespace nrv\action;

use nrv\festival\Spectacle;
use nrv\renderer\SpectacleFiltersListRenderer;
use nrv\renderer\SpectacleRenderer;
use nrv\repository\NrvRepository;

/**
 * Action to display all the spectacles by style
 */
class DisplaySpectacleByStyleAction extends Action{

    /**
     * Displays the list of all the spectacles by style
     * @return string The HTML code of the list
     */
    public function executeGet(): string{
        // Get the style from the URL
        $style = $_GET['style'] ?? '';
        $repository = NrvRepository::getInstance();
        if (empty($style)) {
            return "<p>Veuillez spécifier un style de musique.</p>";
        }

        $spectacles = $repository->findSpectaclesByStyle($style);
        $filtersRenderer = new SpectacleFiltersListRenderer();

        // Exit if no spectacles are found
        if (empty($spectacles)) {
            return <<<FIN
            <h2 class="p-2">Aucun Spectacles pour le style : $style</h2>
            <div class="col-3">
                {$filtersRenderer->render()}
            </div>
            <hr>
            <ul>
        FIN;
        }

        $html = <<<FIN
            <h2 class="p-2">Spectacles pour le style : $style</h2>
            <div class="col-3">
                {$filtersRenderer->render()}
            </div>
            <hr>
            <div class='row row-cols-sm-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 g-4'>

        FIN;

        // Create the list of spectacles
        foreach ($spectacles as $spectacle) {
            $s = new Spectacle($spectacle['titre'], $spectacle['description'], $spectacle['horaire'], $spectacle['duree'], $spectacle['style'], $spectacle['chemin_video']);
            $s->setId($spectacle['id']);
            $date = $repository->getDateSpectacle($spectacle['id']);
            $image = $repository->getImagesSpectacle($spectacle['id']);
            $spectacleRenderer = new SpectacleRenderer($s);
            if(!empty($image)) {
                $html .= $spectacleRenderer->renderAsCompact($date, $image[0]['chemin_fichier']); // Display the first image
            } else {
                $html .= $spectacleRenderer->renderAsCompact($date, "pas d'image");
            }
        }

        return $html . "</div>";
    }

    public function executePost(): string{
        return $this->executeGet();
    }
}
