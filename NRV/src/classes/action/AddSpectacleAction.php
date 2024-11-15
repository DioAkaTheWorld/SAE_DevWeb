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
use nrv\festival\Spectacle;
use nrv\renderer\ArtistsFormRenderer;
use nrv\repository\NrvRepository;

/**
 * Class to add a spectacle
 */
class AddSpectacleAction extends Action {

    /**
     * Displays the form to add a spectacle
     * @return string The HTML code of the form
     * @throws InvalidPropertyNameException
     */
    public function executeGet(): string {
        $check = $this->checkUser(User::STAFF);
        if ($check !== "") {
            return $check;
        }

        // Test if the user is connected and has the right role
        try {
            $authz = new Authz(AuthnProvider::getSignedInUser());
            $authz->checkRole(User::STAFF);
        } catch (AuthzException|AuthnException $e) {
            return "<div class='container'>{$e->getMessage()}</div>";
        }

        $artistesRenderer = new ArtistsFormRenderer();

        return <<<FIN
        <h2 class="p-2">Ajouter un spectacle</h2>
        <hr>
        <form action="?action=add-spectacle" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="titre" class="form-label">Titre<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="titre" name="titre" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description<span class="text-danger">*</span></label>
                <textarea class="form-control" id="description" name="description" required></textarea>
            </div>
            <div class="mb-3">
                <label for="horaire" class="form-label">Horaire<span class="text-danger">*</span></label>
                <input type="time" class="form-control" id="horaire" name="horaire" required>
            </div>
            <div class="mb-3">
                <label for="duree" class ="form-label">Durée<span class="text-danger">*</span></label>
                <input type="time" class="form-control" id="duree" name="duree" required>
            </div>
            <div class="mb-3">
                <label for="style" class="form-label">Style<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="style" name="style" required>
            </div>
            <div class="mb-3">
                <label for="artiste" class="form-label">Artiste(s)<span class="text-danger">*</span></label><br>
                {$artistesRenderer->render(NrvRepository::getInstance()->getAllArtists())}
            </div>
            <div class="mb-3">
                <label for="video" class="form-label">Ajouter une vidéo:<span class="text-danger">*</span></label>
                <input class="form-control" type="file" name="video" id="video">
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Ajouter une image<span class="text-danger">*</span></label>
                <input class="form-control" type="file" name="image" id="image">
            </div>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        FIN;

    }

    /**
     * Processes the form submission to add a spectacle
     * @return string The HTML code of the result
     * @throws InvalidPropertyNameException
     */
    public function executePost(): string {
        $check = $this->checkUser(User::STAFF);
        if ($check !== "") {
            return $check;
        }

        // Get the data from the form and sanitize it
        $titre = filter_var($_POST['titre'], FILTER_SANITIZE_STRING);
        $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
        $horaire = filter_var($_POST['horaire'], FILTER_SANITIZE_STRING);
        $style = filter_var($_POST['style'], FILTER_SANITIZE_STRING);
        $style = trim($style);
        $style = strtolower($style); // Avoid double entries in the database when fetching by style
        $duree = filter_var($_POST['duree'],FILTER_SANITIZE_STRING);
        $artistes = array();
        $repo = NrvRepository::getInstance();
        $nbArtistes = $repo->getNbArtistes();
        for ($i = 1; $i <= $nbArtistes; $i++) {
            if (isset($_POST["artiste$i"])) {
                $artistes[$i] = filter_var($_POST["artiste$i"], FILTER_SANITIZE_STRING);
            }
        }

        // Check the data
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
            if (empty($artistes)) {
                throw new InvalidPropertyValueException("Il faut au moins 1 artiste.");
            }
        } catch (InvalidPropertyValueException $e) {
            return $this->executeGet() . $e->getMessage();
        }

        $spectacle = new Spectacle($titre, $description, $horaire, $duree, $style, "aucune image");
        $spectacle = $repo->ajouterSpectacle($spectacle);
        $spectacleId = $spectacle->__get('id');

        // Add the artists to the spectacle
        foreach($artistes as $id => $artiste) {
            $repo->addArtisteToSpectacle($id, $spectacleId);
        }

        // Upload the video and the image
        try {
            // Upload the video
            if ($_FILES["video"]["error"] === UPLOAD_ERR_OK) {
                $nomFichier = UploadFile::uploadVideo();
                $repo->updateVideoPathForSpectacle($nomFichier, $spectacle->__get('id'));
                $spectacle->setCheminVideo($nomFichier);
            }

            // Upload the image
            if ($_FILES["image"]["error"] === UPLOAD_ERR_OK) {
                $extension =strrchr($_FILES["image"]["type"], "/");
                $nomFichier = UploadFile::uploadImage(substr($extension, 1));
                $idImage = $repo->addImage($nomFichier);
                $repo->addImageToSpectacle($idImage, $spectacle->__get('id'));
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