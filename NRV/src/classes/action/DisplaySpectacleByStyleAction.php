<?php
namespace nrv\action;

use nrv\festivale\Spectacle;
use nrv\renderer\SpectacleFiltersListRenderer;
use nrv\renderer\SpectacleRenderer;
use nrv\repository\NrvRepository;

class DisplaySpectacleByStyleAction extends Action{

    public function executeGet(): string{
        $style = $_GET['style'] ?? '';
        $repository = NrvRepository::getInstance();


        if (empty($style)) {
            return "<p>Veuillez spécifier un style de musique.</p>";
        }

        $spectacles = $repository->findSpectaclesByStyle($style);

        if (empty($spectacles)) {
            return "<p>Aucun spectacle trouvé pour le style '$style'.</p>";
        }

        $filtersRenderer = new SpectacleFiltersListRenderer();
        $html = <<<FIN
            <h2>Spectacles pour le style : $style</h2>
            <div>
                {$filtersRenderer->render()}
            </div>
            <hr>
            <ul>
        FIN;

        foreach ($spectacles as $spectacle) {
            $s = new Spectacle($spectacle['titre'], $spectacle['description'], $spectacle['horaire'], $spectacle['duree'], $spectacle['style'], $spectacle['chemin_video']);
            $s->setId($spectacle['id']);
            $date = $repository->getDateSpectacle($spectacle['id']);
            $image = $repository->getImagesSpectacle($spectacle['id']);
            $spectacleRenderer = new SpectacleRenderer($s);
            if($image){
                $html .= $spectacleRenderer->renderAsCompact($date, $image[0]['chemin_fichier']); // On prend la première image
            } else {
                $html .= $spectacleRenderer->renderAsCompact($date, "aucune image");
            }
        }
        $html .= "</ul>";

        return $html;
    }

    public function executePost(): string{
        return $this->executeGet();
    }
}
