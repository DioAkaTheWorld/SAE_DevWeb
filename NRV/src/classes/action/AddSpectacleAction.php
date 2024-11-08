<?php

namespace nrv\action;

use nrv\action\Action;
use nrv\exception\InvalidPropertyValueException;
use nrv\festivale\Spectacle;
use nrv\repository\NrvRepository;

class AddSpectacleAction extends Action {

    public function executeGet(): string {
        return <<<FIN
        <h2 class="p-2">Ajouter un spectacle</h2>
        <hr>
        <form action="?action=add-spectacle" method="post">
            <div>
                <label for="titre" class="form-label">Titre*</label>
                <input type="text" class="form-control" id="titre" name="titre">
            </div>
            <div>
                <label for="description" class="form-label">Description*</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            <div>
                <label for="horaire" class="form-label">Horaire*</label>
                <input type="time" class="form-control" id="horaire" name="horaire">
            </div>
            <div>
                <label for="style" class="form-label">Style*</label>
                <input type="text" class="form-control" id="style" name="style">
            </div>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        FIN;

    }

    /**
     * @throws InvalidPropertyValueException
     */
    public function executePost(): string {
        var_dump($_POST['horaire']);
        // Récupérer les données du formulaire et les valider
        $titre = filter_var($_POST['titre'], FILTER_SANITIZE_STRING);
        $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
        $horaire = filter_var($_POST['horaire'], FILTER_SANITIZE_STRING);
        $style = filter_var($_POST['style'], FILTER_SANITIZE_STRING);
        try {
            if (empty($titre)) {
                throw new InvalidPropertyValueException("Titre manquant.");
            }
            if (strlen($description) > 200) {
                throw new InvalidPropertyValueException("Description trop longue.");
            }
            if (!preg_match("/^(?:[01]\d|2[0-3]):[0-5]\d$/", $horaire))  {
                throw new InvalidPropertyValueException("Horaire non valide. Format attendu: HH:MM");
            }
            if (empty($style)) {
                throw new InvalidPropertyValueException("Style manquant.");
            }
        } catch (InvalidPropertyValueException $e) {
            return $this->executeGet() . $e->getMessage();
        }


        // Gestion des erreurs et validation des données
        $spectacle = new Spectacle($titre, $description, "aaa", $horaire, $style);
        $repo = NrvRepository::getInstance();
        $repo->ajouterSpectacle($spectacle);
        return <<<FIN
        <div>
            Spectacle ajouté avec succès.
        </div>
        FIN;

    }
}