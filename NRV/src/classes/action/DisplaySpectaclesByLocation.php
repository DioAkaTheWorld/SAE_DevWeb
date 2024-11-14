<?php

namespace nrv\action;

use nrv\festival\Spectacle;
use nrv\renderer\SpectacleFiltersListRenderer;
use nrv\renderer\SpectacleRenderer;
use nrv\repository\NrvRepository;

class DisplaySpectaclesByLocation extends Action {

    public function executeGet(): string {
        $lieu = $_GET['lieu'] ?? '';
        $repository = NrvRepository::getInstance();

        if (empty($lieu)) {
            return "<p>Veuillez spécifier un lieu.</p>";
        }

        $spectacles = $repository->findSpectaclesByLieu($lieu);
        $filtersRenderer = new SpectacleFiltersListRenderer();

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
            <ul>
        FIN;
        foreach ($spectacles as $spectacle) {
            $s = new Spectacle($spectacle['titre'], $spectacle['description'], $spectacle['horaire'], $spectacle['duree'], $spectacle['style'], $spectacle['chemin_video']);
            $s->setId($spectacle['id_spectacle']);
            $image = $repository->getImagesSpectacle($spectacle['id_spectacle']);
            $spectacleRenderer = new SpectacleRenderer($s);
            if(!empty($image)) {
                $html .= $spectacleRenderer->renderAsCompact($lieu, $image[0]['chemin_fichier']); // On prend la première image
            } else {
                $html .= $spectacleRenderer->renderAsCompact($lieu, "pas d'image");
            }
        }
        $html .= "</ul>";

        return $html;
    }

    public function executePost(): string {
        return $this->executeGet();
    }
}