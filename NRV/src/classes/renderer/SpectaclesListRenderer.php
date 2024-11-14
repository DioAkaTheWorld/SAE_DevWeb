<?php

namespace nrv\renderer;

use nrv\exception\InvalidPropertyNameException;
use nrv\festival\Spectacle;
use nrv\repository\NrvRepository;

/**
 * SpectaclesListRenderer class
 *
 * Class to render the list of all spectacles
 */
class SpectaclesListRenderer {

    /**
     * Render the list of all spectacles
     * @return string The HTML code of the list
     * @throws InvalidPropertyNameException
     */
    public function renderAllSpectacleList() : string {
        $repo = NrvRepository::getInstance();
        $spectacles = $repo->findAllSpectacles();
        return $this->createList($spectacles);
    }

    /**
     * Render the list of all spectacles of a given array
     * @param array $spectacles The list of spectacles to render
     * @return string The HTML code of the list
     * @throws InvalidPropertyNameException
     */
    public function renderSpectacleList(array $spectacles) : string {
        return $this->createList($spectacles);
    }

    /**
     * Create the list of spectacles
     * @param array $spectacles The list of spectacles to render
     * @return string The HTML code of the list
     * @throws InvalidPropertyNameException
     */
    private function createList(array $spectacles) : string {
        $repo = NrvRepository::getInstance();
        $res = "<div class='row row-cols-sm-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 g-4'>";

        // For each spectacle, we render it
        foreach ($spectacles as $spectacle) {
            $spectacleObjet = new Spectacle($spectacle['titre'], $spectacle['description'], $spectacle['horaire'], $spectacle['duree'],$spectacle['style'], $spectacle['chemin_video']);
            $spectacleObjet->setId($spectacle['id']);
            $spectacleRenderer = new SpectacleRenderer($spectacleObjet);
            $date = $repo->getDateSpectacle($spectacle['id']);
            $image = $repo->getImagesSpectacle($spectacle['id']);
            if(!empty($image)) {
                $res .= $spectacleRenderer->renderAsCompact($date, $image[0]['chemin_fichier']); // We only take the first image
            } else {
                $res .= $spectacleRenderer->renderAsCompact($date, "pas d'image");
            }
        }

        $res .= "</div>";
        return $res;
    }
}