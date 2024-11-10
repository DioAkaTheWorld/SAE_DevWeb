<?php

namespace nrv\action;

use nrv\festivale\Spectacle;
use nrv\renderer\SpectacleFiltersListRenderer;
use nrv\renderer\SpectacleRenderer;
use nrv\repository\NrvRepository;

class DisplayAllSpectaclesAction extends Action {

    public function executeGet(): string {
        $repo = NrvRepository::getInstance();
        $spectacles = $repo->findAllSpectacles();
        $stylesRenderer = new SpectacleFiltersListRenderer();

        // Liste des filtres
        $res = <<<FIN
        <h2 class="p-2">Liste des spectacles</h2>
        <ul>
            <li><span>Filtrer</span>
                <ul>
                    <li>Date</li>
                    <li>{$stylesRenderer->render()}</li>
                    <li>Lieu</li>
                </ul>
            </li>
        </ul>
        <hr>
        <ol class='list-group list-group-numbered'>
        FIN;

        // Liste des spectacles
        foreach ($spectacles as $spectacle) {
            $spectacleObjet = new Spectacle($spectacle['titre'], $spectacle['description'], $spectacle['horaire'], $spectacle['duree'],$spectacle['style'], $spectacle['chemin_fichier']);
            $spectacleObjet->setId($spectacle['id']);
            $spectacleRenderer = new SpectacleRenderer($spectacleObjet);
            $date = $repo->getDateSpectacle($spectacle['id']);
            $image = $repo->getImagesSpectacle($spectacle['id']); // On prend la première image
            if($image){
                $res .= $spectacleRenderer->renderAsCompact($date, $image[0]['chemin_fichier']);
            } else {
                $res .= $spectacleRenderer->renderAsCompact($date, "pas d'image");
            }
        }

        $res .= "</ol>";
        return $res;

    }

    public function executePost(): string {
        return $this->executeGet();
    }
}