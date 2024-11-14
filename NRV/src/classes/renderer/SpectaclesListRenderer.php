<?php

namespace nrv\renderer;

use nrv\festival\Spectacle;
use nrv\repository\NrvRepository;

class SpectaclesListRenderer {

    /**
     * Rendre la liste de tous les spectacles
     * @return string Le HTML généré pour la liste de tous les spectacles
     */
    public function renderAllSpectacleList() : string {
        $repo = NrvRepository::getInstance();
        $spectacles = $repo->findAllSpectacles();
        return $this->createList($spectacles);
    }

    /**
     * Rendre la liste des spectacles passés
     * @param array $spectacles Tableau d'objets de type spectacle
     * @return string Le HTML généré pour la liste des spectacles passés
     */
    public function renderSpectacleList(array $spectacles) : string {
        return $this->createList($spectacles);
    }

    /**
     * Créer une liste de spectacles
     * @param array $spectacles Tableau d'objets de type spectacle
     * @return string Le HTML généré pour la liste des spectacles
     */
    private function createList(array $spectacles) : string {
        $repo = NrvRepository::getInstance();
        $res = "<div class='row row-cols-sm-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 g-4'>";

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

        $res .= "</div>";
        return $res;
    }
}