<?php

namespace nrv\action;

use Exception;
use nrv\auth\AuthnProvider;
use nrv\auth\Authz;
use nrv\auth\User;
use nrv\exception\AuthnException;
use nrv\exception\AuthzException;
use nrv\exception\InvalidPropertyNameException;
use nrv\exception\InvalidPropertyValueException;
use nrv\festivale\Spectacle;
use nrv\repository\NrvRepository;

class AddSpectacleAction extends Action {

    /**
     * @throws InvalidPropertyNameException
     */
    public function executeGet(): string {
        $check = $this->checkUser(User::STAFF);
        if ($check !== "") {
            return $check;
        }

        // Test des droits
        try {
            $authz = new Authz(AuthnProvider::getSignedInUser());
            $authz->checkRole(User::STAFF);
        } catch (AuthzException|AuthnException $e) {
            return "<div class='container'>{$e->getMessage()}</div>";
        }

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
            <div>
                <label for="fichier">Ajouter une vidéo: </label>
                <input type="file" name="fichier" id="fichier">
            </div>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        FIN;

    }

    /**
     * @return string
     * @throws InvalidPropertyNameException
     */
    public function executePost(): string {
        $check = $this->checkUser(User::STAFF);
        if ($check !== "") {
            return $check;
        }

        // Récupérer les données du formulaire et les valider
        $titre = filter_var($_POST['titre'], FILTER_SANITIZE_STRING);
        $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
        $horaire = filter_var($_POST['horaire'], FILTER_SANITIZE_STRING);
        $style = filter_var($_POST['style'], FILTER_SANITIZE_STRING);
        $duree = filter_var($_POST['duree'],FILTER_SANITIZE_STRING);
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

        $spectacle = new Spectacle($titre, $description, $horaire, $duree, $style, "Pas d'image");
        $repo = NrvRepository::getInstance();
        $spectacle = $repo->ajouterSpectacle($spectacle);

        // Gestion du fichier
        try {
            if ($_FILES["fichier"]["error"] === UPLOAD_ERR_OK) {
                $nomFichier = UploadFile::uploadFile("video", "mp4");
                $repo->updateVideoPathForSpectacle($nomFichier, $spectacle->__get('id'));
                $spectacle->setCheminVideo($nomFichier);
            }
        } catch (Exception $e) {
            return $this->executeGet() . <<<FIN
            <div class="alert alert-warning my-5" role="alert">
                {$e->getMessage()}
            </div>
            FIN;
        }

        return <<<FIN
            <div>
                <h2>Spectacle ajouté</h2>
                <a href="?action=display-spectacle&id={$spectacle->__get('id')}">Voir le spectacle</a>
                <a href="?action=add-image-to-spectacle">Ajouter une image</a>
            </div>
        FIN;

    }
}