<?php
namespace nrv\action;

use nrv\repository\NrvRepository;

class DisplaySpectacleStyleAction extends Action{

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

        $html = "<h2>Spectacles pour le style : $style</h2><ul>";
        foreach ($spectacles as $spectacle) {
            $html .= "<li>{$spectacle['titre']} - {$spectacle['description']}</li>";
        }
        $html .= "</ul>";

        return $html;
    }

    public function executePost(): string{
        return $this->executeGet();
    }
}
