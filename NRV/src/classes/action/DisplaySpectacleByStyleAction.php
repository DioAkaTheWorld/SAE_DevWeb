<?php
namespace nrv\action;

use nrv\festivale\Spectacle;
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

        $html = <<<FIN
            <h2>Spectacles pour le style : $style</h2>
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
                $html .= $spectacleRenderer->renderAsCompact($date, "pas d'image");
            }
        }
        $html .= "</ul>";

        return $html;
    }

    public function executePost(): string{
        return $this->executeGet();
    }
}
