<?php

namespace nrv\action;

use Exception;

abstract class UploadFile {

    /**
     * Upload une image
     * @param string $format Le format de l'image (sans le point)
     * @return string Le nom du fichier
     * @throws Exception
     */
    public static function uploadImage(string $format): string {
        return self::uploadFile("image", $format);
    }

    /**
     * Upload une vidéo
     * @return string Le nom du fichier
     * @throws Exception
     */
    public static function uploadVideo(): string {
        return self::uploadFile("video", "mp4");
    }

    /**
     * Upload un fichier audio ou vidéo
     * @param string $type Le type de fichier (image ou video)
     * @param string $format Le format du fichier (sans le point)
     * @return string Le nom du fichier
     * @throws Exception
     */
    private static function uploadFile(string $type, string $format): string {
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
        $tmp = $_FILES[$type]["tmp_name"];
        $dest = $upload_dir . $file_name;
        // Vérifie si le fichier est dans le bon format et si sa taille est inférieure à 15 Mo
        if ($_FILES[$type]["type"] === $fichierType && $_FILES[$type]["size"] < 15728640 && move_uploaded_file($tmp, $dest)) {
            return $file_name;
        }
        throw new Exception("Échec de l'upload ou format audio incorrect");
    }
}