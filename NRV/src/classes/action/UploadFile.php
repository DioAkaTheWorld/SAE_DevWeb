<?php

namespace nrv\action;

use Exception;

abstract class UploadFile {

    /**
     * Upload le fichier audio
     * @param string $type Le type de fichier (image ou video)
     * @param string $format Le format du fichier (sans le point)
     * @return string Le nom du fichier
     * @throws Exception
     */
    public static function uploadFile(string $type, string $format): string {
        // Vérification des paramètres
        if ($type !== "image" && $type !== "video") {
            throw new Exception("Type de fichier incorrect");
        }
        if ($format !== "jpg" && $format !== "jpeg" && $format !== "mp4") {
            throw new Exception("Format de fichier incorrect");
        }
        if ($type === "image") {
            if ($format !== "jpg" && $format !== "jpeg") {
                throw new Exception("Format d'image incorrect");
            }
        }
        if ($type === "video") {
            if ($format !== "mp4") {
                throw new Exception("Format de vidéo incorrect");
            }
        }

        // variables intermédiaires pour la lisibilité
        $dir = __DIR__ . "/../../../../medias/" . $type . "s" . "/"; // NRV/medias/images/ ou NRV/medias/videos/
        $fichierType = $type . "/" . $format; // image/jpg, image/jpeg, image/png, video/mp4

        $upload_dir = $dir;
        $file_name = uniqid() . "." . $format;
        $tmp = $_FILES["fichier"]["tmp_name"];
        $dest = $upload_dir . $file_name;
        // Vérifie si le fichier est dans le bon format et si sa taille est inférieure à 10 Mo
        if ($_FILES["fichier"]["type"] === $fichierType && $_FILES["fichier"]["size"] < 10485760 && move_uploaded_file($tmp, $dest)) {
            return $file_name;
        }
        throw new Exception("Échec de l'upload ou format audio incorrect");
    }
}