<?php

namespace nrv\action;

use nrv\repository\NrvRepository;

class DisplayAllSpectaclesAction extends Action {

    public function executeGet(): string {
        $repo = NrvRepository::getInstance();
        $spectacles = $repo->findAllSpectacles();

        $res = <<<FIN
        <h2 class="p-2">Liste des spectacles</h2>
         <li class="deroulant"><a href="#">Filtrer &ensp;</a>
            <ul class="sous">
            <li><a href="#">Date</a></li>
            <li><a>Style</a></li>
            <ul><li><a href="?action=display-spectacles-by-style&style=jazz">Jazz</a></li>
                <li><a href="?action=display-spectacles-by-style&style=rock">Rock</a></li>
                <li><a href="?action=display-spectacles-by-style&style=blues">Blues</a></li>
                <li><a href="?action=display-spectacles-by-style&style=classical">Classique</a></li>
                <li><a href="?action=display-spectacles-by-style&style=electronic">Electro</a></li></ul>
            <li><a href="#">Lieu</a></li>
          </ul>
        </li>
        <hr>
        <ol class='list-group list-group-numbered'>
        FIN;
        foreach ($spectacles as $spectacle) {
            $date = $repo->getDateSpectacle($spectacle['id']);
            $inter = $repo->getImagesSpectacle($spectacle['id']);
            if($inter ){
                $image = $inter[0]['chemin_fichier'];
                $res .= <<<FIN
                        <li>
                            <div>
                                <a href='?action=display-spectacle&id={$spectacle['id']}'>{$spectacle['titre']}</a>
                            </div>
                            <span>Date: $date, </span>
                            <span>Horaire: {$spectacle['horaire']}</span>
                            <span>Durée :{$spectacle['duree']}</span>
                            <img src="/SAE_DevWeb/images/$image" alt="image spectacle">
                        </li>

                       FIN;
            }


        }

        $res .= "</ol>";
        return $res;

    }

    public function executePost(): string {
        return $this->executeGet();
    }
}