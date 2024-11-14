<?php

namespace nrv\action;

use Exception;
use nrv\auth\User;
use nrv\exception\InvalidPropertyNameException;
use nrv\exception\InvalidPropertyValueException;
use nrv\festival\Spectacle;
use nrv\renderer\ArtistsFormRenderer;
use nrv\renderer\ImageFormRenderer;
use nrv\repository\NrvRepository;

/**
 * Action to modify a spectacle
 */
class ModifySpectacleAction extends Action {

    /**
     * Displays the form to modify a spectacle
     * @return string The HTML code of the form
     * @throws InvalidPropertyNameException
     */
    public function executeGet(): string {
        // Check if the spectacle ID is specified
        if (!isset($_GET['id'])) {
            http_response_code(400);
            return <<<FIN
            <div class="container d-flex flex-column justify-content-center align-items-center h3">
                        <h2 class="h1">Erreur 400</h2>
                        ID du spectacle manquant
                    </div>
            FIN;
        }

        $spectacleId = (int)$_GET['id'];

        // Check if the user has the right role to modify a spectacle
        $check = $this->checkUser(User::STAFF);
        if ($check !== "") {
            return $check;
        }

        // Get the details of the spectacle
        try {
            $repository = NrvRepository::getInstance();
            $spectacleDetails = $repository->getSpectacleDetails($spectacleId);
            $artistes = $repository->getArtistsFromSpectacle($spectacleId);
            $images = $repository->getSpectacleImages($spectacleId);
            $artistesRenderer = new ArtistsFormRenderer();
            $imagesRenderer = new ImageFormRenderer();

            // Avoid displaying seconds in the time fields (only HH:MM)
            $horaire = substr($spectacleDetails['horaire'], 0, 5);
            $duree = substr($spectacleDetails['duree'], 0, 5);
        } catch (Exception $e) {
            return "<p>Erreur lors de la récupération des informations du spectacle : {$e->getMessage()}</p>";
        }

        return <<<FIN
        <h2 class="p-2">Modifier un spectacle</h2>
        <hr>
        <form action="?action=modify-spectacle&id=$spectacleId" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="titre" class="form-label">Titre<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="titre" name="titre" value="{$spectacleDetails['titre']}" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description<span class="text-danger">*</span></label>
                <input class="form-control" id="description" name="description" value="{$spectacleDetails['description']}" required>
            </div>
            <div class="mb-3">
                <label for="horaire" class="form-label">Horaire<span class="text-danger">*</span></label>
                <input type="time" class="form-control" id="horaire" name="horaire" value="$horaire" required>
            </div>
            <div class="mb-3">
                <label for="duree" class ="form-label">Durée<span class="text-danger">*</span></label>
                <input type="time" class="form-control" id="duree" name="duree" value="$duree" required>
            </div>
            <div class="mb-3">
                <label for="style" class="form-label">Style<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="style" name="style" value="{$spectacleDetails['style']}" required>
            </div>
            <div class="mb-3">
                <label for="video" class="form-label">Modifier la vidéo</label>
                <input type="file" class="form-control" name="video" id="video">
            </div>
            <div class="mb-3">
                <label for="artiste" class="form-label">Artiste(s) (cocher pour supprimer)</label><br>
                {$artistesRenderer->render($artistes)}
            </div>
            <div class="mb-3" >
                <label for="deleteImage" class="form-label">Image(s) (cocher pour supprimer)</label><br>
                <div class="d-flex justify-content-start flex-row">
                    {$imagesRenderer->render($images)}
                </div>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Ajouter une image</label>
                <input type="file" class="form-control" name="image" id="image">
            </div>
            <button type="submit" class="btn btn-primary">Modifier</button>
        FIN;
    }

    /**
     * Modifies a spectacle
     * @return string The HTML code of the result
     * @throws InvalidPropertyNameException
     */
    public function executePost(): string {
        // Check if the user has the right role to modify a spectacle
        $check = $this->checkUser(User::STAFF);
        if ($check !== "") {
            return $check;
        }

        // Get and validate the data from the form
        $titre = filter_var($_POST['titre'], FILTER_SANITIZE_STRING);
        $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
        $horaire = filter_var($_POST['horaire'], FILTER_SANITIZE_STRING);
        $duree = filter_var($_POST['duree'], FILTER_SANITIZE_STRING);
        $style = filter_var($_POST['style'], FILTER_SANITIZE_STRING);
        $artistes = [];
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'artiste')) {
                $artisteId = (int)substr($key, 7); // The artist ID is after 'artiste' in the key name, e.g. 'artiste1'
                $artistes[] = $artisteId;
            }
        }
        $images = [];
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'image')) {
                $imageId = (int)substr($key, 5); // The image ID is after 'image' in the key name, e.g. 'image1'
                $images[] = $imageId;
            }
        }

        // Validate the data
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

        // Modify the spectacle
        $spectacle = new Spectacle($titre, $description, $horaire, $duree, $style, "aucune image");
        $spectacle->setId((int)$_GET['id']);
        $repo = NrvRepository::getInstance();
        try {
            $repo->modifierSpectacle($spectacle);
        } catch (Exception $e) {
            return "<p>Erreur lors de la modification du spectacle : {$e->getMessage()}</p>";
        }

        // Delete the artists
        foreach($artistes as $id) {
            $repo->deleteArtistFromSpectacle($id, $_GET['id']);
        }

        // Delete the images
        foreach($images as $id) {
            $path = $repo->getImagePath($id);
            if($repo->deleteImagesFromSpectacle($id, $_GET['id'])) {
                unlink(__DIR__ . "/../../../../medias/images/" . $path);
            }

        }

        // Upload the video and the image
        try {
            // Upload the video
            if ($_FILES["video"]["error"] === UPLOAD_ERR_OK) {
                $nomFichier = UploadFile::uploadVideo();
                $previousVideoPath = $repo->getVideoPathFromSpectacle($spectacle->__get('id'));
                $repo->updateVideoPathForSpectacle($nomFichier, $spectacle->__get('id'));
                $spectacle->setCheminVideo($nomFichier);
                if (!empty($previousVideoPath) && $previousVideoPath !== "aucune video") {
                    unlink(__DIR__ . "/../../../../medias/videos/" . $previousVideoPath);
                }
            }

            // Upload the image
            if ($_FILES["image"]["error"] === UPLOAD_ERR_OK) {
                $extension = strrchr($_FILES["image"]["type"], "/");
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
                <h2>Spectacle modifié</h2>
                <a href="?action=display-spectacle&id={$spectacle->__get('id')}">Voir le spectacle</a>
                <a href="?action=add-image-to-spectacle">Ajouter une autre image</a>
            </div>
        FIN;

    }
}