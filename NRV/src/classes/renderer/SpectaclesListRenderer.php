<?php

namespace nrv\renderer;

use nrv\festivale\Spectacle;
use nrv\repository\NrvRepository;

class SpectaclesListRenderer {

    public function render() : string {
        $repo = NrvRepository::getInstance();
        $spectacles = $repo->findAllSpectacles();
        $res = "<ol class='list-group list-group-numbered'>";

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

    public function renderAsSelectForHtml() : string {
        $repo = NrvRepository::getInstance();
        $spectacles = $repo->findAllSpectacles();

        // Liste des spectacles
        $res = "<select name='id_spectacle' id='id_spectacle' required>";
        foreach ($spectacles as $spectacle) {
            $res .= <<<FIN
                <option value='{$spectacle['id']}'>{$spectacle['titre']}</option>
                
            FIN;
        }
        $res .= "</select>";
        
        return $res;
    }
}