<?php

namespace nrv\action;

use nrv\festival\Spectacle;
use nrv\renderer\SpectacleFiltersListRenderer;
use nrv\renderer\SpectacleRenderer;
use nrv\repository\NrvRepository;

class DisplaySpectaclesByDatesAction extends Action {

    public function executeGet(): string {
        $date = $_GET['date'] ?? '';
        $repository = NrvRepository::getInstance();
        $dateFormatted = date('d/m/Y', strtotime($date));

        if (empty($date)) {
            return "<p>Veuillez spécifier une date.</p>";
        }

        $spectacles = $repository->findSpectaclesByDate($date);
        $filtersRenderer = new SpectacleFiltersListRenderer();

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
            <h2 class="p-2">Spectacles pour la date : $dateFormatted</h2>
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
                $html .= $spectacleRenderer->renderAsCompact($date, $image[0]['chemin_fichier']); // On prend la première image
            } else {
                $html .= $spectacleRenderer->renderAsCompact($date, "pas d'image");
            }
        }
        $html .= "</ul>";

        return $html;
    }

    public function executePost(): string {
        return $this->executeGet();
    }
}