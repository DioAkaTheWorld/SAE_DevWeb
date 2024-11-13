<?php

namespace nrv\renderer;

use nrv\festivale\Spectacle;
use nrv\repository\NrvRepository;

class SpectaclesListRenderer {

    public function render() : string {
        $repo = NrvRepository::getInstance();
        $spectacles = $repo->findAllSpectacles();
        $res = "<ol class='list-inline'>";

        // Liste des spectacles
        foreach ($spectacles as $spectacle) {
            $spectacleObjet = new Spectacle($spectacle['titre'], $spectacle['description'], $spectacle['horaire'], $spectacle['duree'],$spectacle['style'], $spectacle['chemin_video']);
            $spectacleObjet->setId($spectacle['id']);
            $spectacleRenderer = new SpectacleRenderer($spectacleObjet);
            $date = $repo->getDateSpectacle($spectacle['id']);
            $image = $repo->getImagesSpectacle($spectacle['id']);
            if(!empty($image)) {
                $res .= $spectacleRenderer->renderAsCompact($date, $image[0]['chemin_fichier']); // On prend la première image
            } else {
                $res .= $spectacleRenderer->renderAsCompact($date, "pas d'image");
            }
        }

        $res .= "</ol>";
        return $res;
    }
}