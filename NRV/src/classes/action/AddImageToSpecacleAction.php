<?php

namespace nrv\action;

use Exception;
use nrv\auth\User;
use nrv\exception\InvalidPropertyNameException;
use nrv\renderer\SpectaclesListRenderer;
use nrv\repository\NrvRepository;

class AddImageToSpecacleAction extends Action {

    /**
     * @throws InvalidPropertyNameException
     */
    public function executeGet(): string {
        $check = $this->checkUser(User::STAFF);
        if ($check !== "") {
            return $check;
        }

        $list = new SpectaclesListRenderer();
        return <<<FIN
        <h1>Ajouter une image à un spectacle</h1>
        <hr>
        <form action="?action=add-image-to-spectacle" method="post" enctype="multipart/form-data">
            <div>
                <label for="id_spectacle">Spectacle*</label>
                {$list->renderAsSelectForHtml()}
            </div>
            <div>
                <label for="fichier">Ajouter une image: </label>
                <input type="file" name="fichier" id="fichier">
            </div>
            <input type="submit" value="Ajouter">
        </form>
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

        // Validation des données
        $id_spectacle = filter_var($_POST['id_spectacle'], FILTER_SANITIZE_NUMBER_INT);

        if (empty($id_spectacle)) {
            return "<p>Le spectacle est manquant.</p>";
        }

        // Gestion du fichier
        $repo = NrvRepository::getInstance();
        var_dump($_FILES);
        try {
            if ($_FILES["fichier"]["error"] === UPLOAD_ERR_OK) {
                $extension =strrchr($_FILES["fichier"]["type"], "/");
                $nomFichier = UploadFile::uploadFile("image", substr($extension, 1));
                $idImage = $repo->addImage($nomFichier);
                $repo->addImageToSpectacle($idImage, $id_spectacle);
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
                <h2>Image ajoutée.</h2>
                <a href="?action=display-spectacle&id=$id_spectacle">Retour au spectacle</a>
                <a href="?action=add-image-to-spectacle">Ajouter une autre image</a>
            </div>
        FIN;
    }
}