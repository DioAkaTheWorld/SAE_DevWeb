<?php

namespace nrv\action;

use Exception;
use nrv\auth\User;
use nrv\exception\InvalidPropertyNameException;
use nrv\exception\InvalidPropertyValueException;
use nrv\festivale\Spectacle;
use nrv\renderer\ArtistsListRenderer;
use nrv\renderer\ImageListRenderer;
use nrv\repository\NrvRepository;

class ModifySpectacleAction extends Action {

    /**
     * @throws InvalidPropertyNameException
     */
    public function executeGet(): string {
        // Vérifier si l'ID du spectacle est spécifié
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

        $check = $this->checkUser(User::STAFF);
        if ($check !== "") {
            return $check;
        }

        // Obtenir les détails du spectacle depuis le dépôt
        try {
            $repository = NrvRepository::getInstance();
            $spectacleDetails = $repository->getSpectacleDetails($spectacleId);
            $artistes = $repository->getArtistsFromSpectacle($spectacleId);
            $images = $repository->getSpectacleImages($spectacleId);
            $artistesRenderer = new ArtistsListRenderer();
            $imagesRenderer = new ImageListRenderer();

            // Evite de récupérer les secondes causant une erreur lors de la modification
            $horaire = substr($spectacleDetails['horaire'], 0, 5);
            $duree = substr($spectacleDetails['duree'], 0, 5);

            // Récupérer l'ID de la soirée associée à ce spectacle
            $soireeId = $repository->getSoireeIdBySpectacleId($spectacleId);
        } catch (Exception $e) {
            return "<p>Erreur lors de la récupération des informations du spectacle : {$e->getMessage()}</p>";
        }

        return <<<FIN
        <h2 class="p-2">Modifier un spectacle</h2>
        <hr>
        <form action="?action=modify-spectacle&id=$spectacleId" method="post" enctype="multipart/form-data">
            <div>
                <label for="titre" class="form-label">Titre*</label>
                <input type="text" class="form-control" id="titre" name="titre" value="{$spectacleDetails['titre']}" required>
            </div>
            <div>
                <label for="description" class="form-label">Description*</label>
                <input class="form-control" id="description" name="description" value="{$spectacleDetails['description']}" required></input>
            </div>
            <div>
                <label for="horaire" class="form-label">Horaire*</label>
                <input type="time" class="form-control" id="horaire" name="horaire" value="$horaire" required>
            </div>
            <div>
                <label for="duree" class ="form-label">Durée*</label>
                <input type="time" class="form-control" id="duree" name="duree" value="$duree" required>
            </div>
            <div>
                <label for="style" class="form-label">Style*</label>
                <input type="text" class="form-control" id="style" name="style" value="{$spectacleDetails['style']}" required>
            </div>
            <div>
                <label for="video">Modifier la vidéo: </label>
                <input type="file" name="video" id="video">
            </div>
            <div>
                <label for="artiste" class="form-label">Artiste(s) (cocher pour supprimer)</label><br>
                {$artistesRenderer->render($artistes)}
            </div>
            <div>
                <label for="image" class="form-label">Image(s) (cocher pour supprimer)</label><br>
                {$imagesRenderer->render($images)}
            </div>
            <div>
                <label for="uploadImage" class="form-label">Ajouter une image</label>
                <input type="file" name="uploadImage" id="uploadImage">
            </div>
            <button type="submit" class="btn btn-primary">Modifier</button>
        FIN;
    }

    /**
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
        $duree = filter_var($_POST['duree'], FILTER_SANITIZE_STRING);
        $style = filter_var($_POST['style'], FILTER_SANITIZE_STRING);
        $artistes = [];
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'artiste')) {
                $artisteId = (int)substr($key, 7); // l'id de l'artiste est après 'artiste' dans le nom de la clé
                $artistes[] = $artisteId;
            }
        }
        $images = [];
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'image')) {
                $imageId = (int)substr($key, 5); // l'id de l'image est après 'image' dans le nom de la clé
                $images[] = $imageId;
            }
        }

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

        $spectacle = new Spectacle($titre, $description, $horaire, $duree, $style, "aucune image");
        $spectacle->setId((int)$_GET['id']);
        $repo = NrvRepository::getInstance();
        try {
            $repo->modifierSpectacle($spectacle);
        } catch (Exception $e) {
            return "<p>Erreur lors de la modification du spectacle : {$e->getMessage()}</p>";
        }

        // Suppression des artistes
        foreach($artistes as $id) {
            $repo->deleteArtistFromSpectacle($id, $_GET['id']);
        }

        // Suppression des images
        foreach($images as $id) {
            $repo->deleteImagesFromSpectacle($id, $_GET['id']);
        }

        // Gestion des fichier
        try {
            // Gestion de la vidéo
            if ($_FILES["video"]["error"] === UPLOAD_ERR_OK) {
                $nomFichier = UploadFile::uploadFile("video", "mp4", "video");
                $previousVideoPath = $repo->getVideoPathFromSpectacle($spectacle->__get('id'));
                $repo->updateVideoPathForSpectacle($nomFichier, $spectacle->__get('id'));
                $spectacle->setCheminVideo($nomFichier);
                if ($previousVideoPath !== "aucune image") {
                    unlink(__DIR__ . "/../../../../medias/videos/" . $previousVideoPath);
                }
            }

            // Gestion de l'image
            if ($_FILES["uploadImage"]["error"] === UPLOAD_ERR_OK) {
                $extension =strrchr($_FILES["uploadImage"]["type"], "/");
                $nomFichier = UploadFile::uploadFile("image", substr($extension, 1), "uploadImage");
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