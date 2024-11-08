<?php

namespace nrv\action;

use Exception;
use nrv\exception\InvalidPropertyValueException;
use nrv\festivale\Spectacle;
use nrv\repository\NrvRepository;

class AddSpectacleAction extends Action {

    public function executeGet(): string {
        return <<<FIN
        <h2 class="p-2">Ajouter un spectacle</h2>
        <hr>
        <form action="?action=add-spectacle" method="post" enctype="multipart/form-data">
            <div>
                <label for="titre" class="form-label">Titre*</label>
                <input type="text" class="form-control" id="titre" name="titre" required>
            </div>
            <div>
                <label for="description" class="form-label">Description*</label>
                <textarea class="form-control" id="description" name="description" required></textarea>
            </div>
            <div>
                <label for="horaire" class="form-label">Horaire*</label>
                <input type="time" class="form-control" id="horaire" name="horaire" required>
            </div>
            <div>
            <label for="duree" class ="form-label">Durée*</label>
            <input type="time" class="form-control" id="duree" name="duree" required>
            </div>

            <div>
                <label for="style" class="form-label">Style*</label>
                <input type="text" class="form-control" id="style" name="style" required>
            </div>
            <div class="col-md-6">
                    <label class="form-label" for="fichier">Ajouter une image: </label>
                    <input class="form-control" type="file" name="fichier" id="fichier">
                </div>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        FIN;

    }

    /**
     * @throws InvalidPropertyValueException
     */
    public function executePost(): string {
        // Récupérer les données du formulaire et les valider
        $titre = filter_var($_POST['titre'], FILTER_SANITIZE_STRING);
        $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
        $horaire = filter_var($_POST['horaire'], FILTER_SANITIZE_STRING);
        $style = filter_var($_POST['style'], FILTER_SANITIZE_STRING);
        $duree = filter_var($_POST['duree']=FILTER_SANITIZE_STRING);
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
            if (empty($duree)){
                throw new InvalidPropertyValueException("Durée non valide");
            }
        } catch (InvalidPropertyValueException $e) {
            return $this->executeGet() . $e->getMessage();
        }

        $spectacle = new Spectacle($titre, $description, $horaire, $style);
        $repo = NrvRepository::getInstance();
        $repo->ajouterSpectacle($spectacle);
        $nomFichier = "Pas d'image";
        // Gestion du fichier
        try {
            if ($_FILES["fichier"]["error"] === UPLOAD_ERR_OK) {
                $nomFichier = $this->uploadFile();
                $idImage = $repo->addImage($nomFichier);
                $repo->addImageToSpectacle($idImage, $spectacle->__get('id'));
            }
        } catch (Exception $e) {
            return $this->executeGet() . <<<FIN
            <div class="alert alert-warning my-5" role="alert">
                {$e->getMessage()}
            FIN;

        }

        // Gestion des erreurs et validation des données
        return <<<FIN
        <div>
            Spectacle ajouté avec succès.
        </div>
        FIN;

    }

    /**
     * Upload le fichier audio
     * @return string Le nom du fichier
     * @throws Exception
     */
    private function uploadFile(): string {
        $upload_dir = __DIR__ . "/../../../../images/";
        $file_name = uniqid() . ".jpg";
        $tmp = $_FILES["fichier"]["tmp_name"];
        $dest = $upload_dir . $file_name;
        if ($_FILES["fichier"]["type"] === "image/jpeg" && move_uploaded_file($tmp, $dest)) {
            return $file_name;
        }
        throw new Exception("Échec de l'upload ou format audio incorrect");
    }
}