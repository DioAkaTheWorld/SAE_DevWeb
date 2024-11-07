<?php

namespace nrv\action;

use nrv\repository\NrvRepository;

class DisplayAllSpectaclesAction extends Action {

    public function executeGet(): string {
        $repo = NrvRepository::getInstance();
        $spectacles = $repo->findAllSpectacles();

        $res = <<<FIN
        <h2 class="p-2">Liste des spectacles</h2>
        <hr>
        <ol class='list-group list-group-numbered'>
        FIN;

        foreach ($spectacles as $spectacle) {
            $date = $repo->getDateSpectacle($spectacle['id']);
            $image = $repo->getImagesSpectacle($spectacle['id'])[0]['url'];
            $res .= <<<FIN
                        <li>
                            <div>
                                <a href='/NRV/index.php?action=display-spectacle&id={$spectacle['id']}'>{$spectacle['titre']}</a>
                            </div>
                            <span>Date: $date, </span>
                            <span>Horaire: {$spectacle['horaire']}</span>
                            <img src="$image" alt="image spectacle">
                        </li>

            FIN;
        }

        $res .= "</ol>";
        return $res;

    }

    public function executePost(): string {
        $this->executeGet();
    }
}