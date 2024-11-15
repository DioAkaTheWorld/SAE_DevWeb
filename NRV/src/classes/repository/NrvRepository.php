<?php
declare(strict_types=1);

namespace nrv\repository;

use Exception;
use nrv\auth\User;
use nrv\exception\InvalidPropertyNameException;
use nrv\festival\Soiree;
use nrv\festival\Spectacle;
use PDO;

/**
 * NrvRepository class
 * Manage the data access
 */
class NrvRepository
{

    /** @var PDO Connexion with the DB */
    private PDO $pdo;
    /** @var NrvRepository|null unique instance */
    private static ?NrvRepository $instance = null;
    /** @var array configuration of the connexion */
    private static array $config = [];

    /**
     * Private constructor
     * @param array $conf configuration of the connexion
     */
    private function __construct(array $conf)
    {
        $this->pdo = new PDO($conf['dsn'], $conf['user'], $conf['pass'], [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false]);
        $this->pdo->prepare("SET NAMES 'utf8'")->execute();
    }

    /**
     * Get the unique instance of the class
     * @return NrvRepository|null the unique instance
     */
    public static function getInstance(): ?NrvRepository
    {
        if (is_null(self::$instance)) {
            self::$instance = new NrvRepository(self::$config);
        }
        return self::$instance;
    }

    /**
     * Set the configuration of the connexion
     * @param string $file path of the configuration file
     * @return void
     * @throws Exception
     */
    public static function setConfig(string $file): void
    {
        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new Exception("Error reading configuration file");
        }

        $driver = $conf['driver'];
        $host = $conf['host'];
        $database = $conf['database'];
        $dsn = "$driver:host=$host;dbname=$database";

        self::$config = ['dsn' => $dsn, 'user' => $conf['username'], 'pass' => $conf['password']];
    }

    /**
     * Get the user from the database
     * @param string $email email of the user
     * @return User the user
     */
    public function getUser(string $email): User
    {
        $stmt = $this->pdo->prepare("SELECT id, role FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $res = $stmt->fetchAll()[0];
        $id = $res['id'];
        $role = $res['role'];
        $hash = $this->getHash($email);
        return new User($id, $email, $hash, $role);
    }

    /**
     * Add a user to the database
     * @param string $email email of the user
     * @param string $hash hash of the password
     * @param int $role role of the user
     * @return void
     */
    public function addUser(string $email, string $hash, int $role): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO user (email, hash, role) VALUES (?, ?, ?)");
        $stmt->execute([$email, $hash, $role]);
    }

    /**
     * Check if a user exists in the database
     * @param string $email email of the user
     * @return bool true if the user exists, false otherwise
     */
    public function userExistsEmail(string $email): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch()[0] > 0;
    }

    /**
     * Get the hash of the password
     * @param string $email email of the user
     * @return string hash of the password
     */
    public function getHash(string $email): string
    {
        $stmt = $this->pdo->prepare("SELECT hash FROM user WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch()[0] ?? "";
    }

    /**
     * Get all spectacles
     * @return array list of all spectacles
     */
    public function findAllSpectacles(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM spectacle");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get the date of a spectacle
     * @param int $id id of the spectacle
     * @return string|null date of the spectacle
     */
    public function getDateSpectacle(int $id): ?string
    {
        $stmt = $this->pdo->prepare("SELECT S.date 
                                 FROM soiree S
                                 JOIN soiree2spectacle S2P ON S.id = S2P.id_soiree
                                 JOIN spectacle SP ON S2P.id_spectacle = SP.id
                                 WHERE SP.id = ? LIMIT 1");
        $stmt->execute([$id]);
        $date = $stmt->fetch();
        return $date ? $date['date'] : "Date inconnue";
    }


    /**
     * Get images of a spectacle
     * @param int $id id of the spectacle
     * @return array list of images
     */
    public function getImagesSpectacle(int $id): array
    {
        $stmt = $this->pdo->prepare("SELECT I.chemin_fichier
                                            FROM image I
                                            JOIN spectacle2image S2I ON I.id = S2I.id_image
                                            WHERE S2I.id_spectacle = ?");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    /**
     * Get the path of an image
     * @param int $id id of the image
     * @return string path of the image
     */
    public function getImagePath(int $id): string
    {
        $stmt = $this->pdo->prepare("SELECT I.chemin_fichier
                                            FROM image I
                                            WHERE I.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }

    /**
     * Add a spectacle
     * @param Spectacle $s spectacle to add
     * @return Spectacle added spectacle
     * @throws InvalidPropertyNameException
     */
    function ajouterSpectacle(Spectacle $s): Spectacle
    {
        $sql = "INSERT INTO spectacle (titre, description, chemin_video, horaire, duree, style) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$s->__get('titre'), $s->__get('description'), $s->__get('chemin_fichier'), $s->__get('horaire'), $s->__get('duree'), $s->__get('style')]);
        $s->setId((int)$this->pdo->lastInsertId());
        return $s;
    }

    /**
     * Modify a spectacle
     * @param Spectacle $s spectacle to modify
     * @return Spectacle modified spectacle
     * @throws Exception
     */
    function modifierSpectacle(Spectacle $s): Spectacle
    {
        $stmt = $this->pdo->prepare("SELECT * FROM spectacle WHERE id = ?");
        $stmt->execute([$s->__get('id')]);
        $spectacle = $stmt->fetch();
        if (!$spectacle) {
            throw new Exception("Spectacle non trouvé");
        }

        $stmt = $this->pdo->prepare("UPDATE spectacle SET titre = ?, description = ?, horaire = ?, duree = ?, style = ? WHERE id = ?");
        $stmt->execute([$s->__get('titre'), $s->__get('description'), $s->__get('horaire'), $s->__get('duree'), $s->__get('style'), $s->__get('id')]);
        return $s;
    }

    /**
     * Add a party
     * @param Soiree $s party to add
     * @return Soiree added party
     * @throws InvalidPropertyNameException
     */
    function ajouterSoiree(Soiree $s): Soiree
    {
        $sql = "INSERT INTO soiree (nom, thematique, date, horaire_debut, horaire_fin, id_lieu, tarif) 
            VALUES (:nom, :thematique, :date, :horaire_debut, :horaire_fin, :id_lieu, :tarif)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nom' => $s->__get('nom'),
            'thematique' => $s->__get('thematique'),
            'date' => $s->__get('date'),
            'horaire_debut' => $s->__get('horaire_debut'),
            'horaire_fin' => $s->__get('horaire_fin'),
            'id_lieu' => $s->__get('id_lieu'),
            'tarif' => $s->__get('tarif')
        ]);
        $s->setId((int)$this->pdo->lastInsertId());
        return $s;
    }

    /**
     * Get a spectacle details
     * @param int $spectacleId spectacle ID
     * @return array spectacle details
     * @throws Exception
     */
    public function getSpectacleDetails(int $spectacleId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM spectacle WHERE id = ?");
        $stmt->execute([$spectacleId]);
        $spectacle = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$spectacle) {
            throw new Exception("Spectacle non trouvé");
        }
        return $spectacle;
    }

    /**
     * Get the artists of a spectacle
     * @param int $spectacleId spectacle ID
     * @return array list of artists
     */
    public function getArtistsFromSpectacle(int $spectacleId): array
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT a.nom, a.id
                                            FROM artiste a 
                                            JOIN spectacle2artiste sa ON a.id = sa.id_artiste 
                                            WHERE sa.id_spectacle = ?");
        $stmt->execute([$spectacleId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all artists
     * @return array list of all artists
     */
    public function getAllArtists(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM artiste");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get the number of artists
     * @return int number of artists
     */
    public function getNbArtistes(): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM artiste");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Add an artist to a spectacle
     * @param int $idArtiste artist ID
     * @param int $idSpectacle spectacle ID
     * @return void
     */
    public function addArtisteToSpectacle(int $idArtiste, int $idSpectacle): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO spectacle2artiste (id_spectacle, id_artiste) VALUES (?, ?)");
        $stmt->execute([$idSpectacle, $idArtiste]);
    }

    /**
     * Delete an artist from a spectacle
     * @param int $idArtiste artist ID
     * @param int $idSpectacle spectacle ID
     * @return void
     */
    public function deleteArtistFromSpectacle(int $idArtiste, int $idSpectacle): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM spectacle2artiste WHERE id_spectacle = ? AND id_artiste = ?");
        $stmt->execute([$idSpectacle, $idArtiste]);
    }

    /**
     * Delete an image from a spectacle
     * @param int $idImage image ID
     * @param int $idSpectacle spectacle ID
     * @return bool true if the image is deleted, false otherwise
     */
    public function deleteImagesFromSpectacle(int $idImage, int $idSpectacle): bool
    {
        // Delete the link between the spectacle and the image
        $stmt = $this->pdo->prepare("DELETE FROM spectacle2image WHERE id_spectacle = ? AND id_image = ?");
        $stmt->execute([$idSpectacle, $idImage]);

        // Check if the image is the image 1 or 2 (these are permanent images in the DB)
        if ($idImage === 1 || $idImage === 2) {
            return false;
        }

        // Delete the image if it is not linked to any spectacle
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM spectacle2image WHERE id_image = ?");
        $stmt->execute([$idImage]);
        $count = $stmt->fetchColumn();
        if ($count === 0) {
            $stmt = $this->pdo->prepare("DELETE FROM image WHERE id = ?");
            $stmt->execute([$idImage]);
            return true;
        }
        return false;
    }

    /**
     * Get the images of a spectacle
     * @param int $spectacleId spectacle ID
     * @return array list of images
     */
    public function getSpectacleImages(int $spectacleId): array
    {
        $stmt = $this->pdo->prepare("SELECT * 
                                            FROM image i 
                                            JOIN spectacle2image si ON i.id = si.id_image 
                                            WHERE si.id_spectacle = ?");
        $stmt->execute([$spectacleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add an image
     * @param string $chemin path of the image
     * @return int image ID
     */
    public function addImage(string $chemin): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO image (chemin_fichier) VALUES (?)");
        $stmt->execute([$chemin]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Add an image to a spectacle
     * @param int $idImage image ID
     * @param int $idSpectacle spectacle ID
     * @return void
     */
    public function addImageToSpectacle(int $idImage, int $idSpectacle): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO spectacle2image (id_spectacle, id_image) VALUES (?, ?)");
        $stmt->execute([$idSpectacle, $idImage]);
    }

    /**
     * Update the path of the video of a spectacle
     * @param string $chemin path of the video
     * @param int $idSpectacle spectacle ID
     * @return void
     */
    public function updateVideoPathForSpectacle(string $chemin, int $idSpectacle): void
    {
        $stmt = $this->pdo->prepare("UPDATE spectacle SET chemin_video = ? WHERE id = ?");
        $stmt->execute([$chemin, $idSpectacle]);
    }

    /**
     * Get the path of the video of a spectacle
     * @param int $idSpectacle spectacle ID
     * @return string path of the video
     */
    public function getVideoPathFromSpectacle(int $idSpectacle): string
    {
        $stmt = $this->pdo->prepare("SELECT chemin_video FROM spectacle WHERE id = ?");
        $stmt->execute([$idSpectacle]);
        return $stmt->fetchColumn();
    }

    /**
     * Get all the spectacle from a given style
     * @param string $style style of the spectacle
     * @return array list of spectacles
     */
    public function findSpectaclesByStyle(string $style): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM spectacle WHERE style = ?");
        $stmt->execute([$style]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the list of styles
     * @return array list of styles
     */
    public function getListeStyleSpectacle(): array
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT style FROM spectacle");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get the spectacle from a given date
     * @param string $date date of the spectacle
     * @return array list of spectacles
     */
    public function findSpectaclesByDate(string $date): array
    {
        $stmt = $this->pdo->prepare("SELECT s.* 
                                            FROM spectacle S
                                            JOIN soiree2spectacle S2P ON S.id = S2P.id_spectacle
                                            JOIN soiree SO ON S2P.id_soiree = SO.id
                                            WHERE SO.date = ?");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }

    /**
     * Get the list of dates
     * @return array list of dates
     */
    public function getListeDateSpectacle(): array
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT date FROM soiree");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get the spectacle from a given location
     * @param string $lieu location of the spectacle
     * @return array list of spectacles
     */
    public function findSpectaclesByLieu(string $lieu): array
    {
        $stmt = $this->pdo->prepare("SELECT S.*
                                            FROM spectacle S
                                            JOIN soiree2spectacle ON S.id = soiree2spectacle.id_spectacle
                                            JOIN soiree ON soiree.id = soiree2spectacle.id_soiree
                                            JOIN lieu ON lieu.id = soiree.id_lieu
                                            WHERE lieu.nom = ?");
        $stmt->execute([$lieu]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the list of locations
     * @return array list of locations
     */
    public function getListeLieuSpectacle(): array
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT nom FROM lieu");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get the list of all the parties
     * @return array list of all the parties
     */
    public function findAllSoirees(): array
    {
        $sql = "SELECT id, nom, date FROM soiree ORDER BY date";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Add a spectacle to a party
     * @param int $id_soiree party ID
     * @param int $id_spectacle spectacle ID
     * @return bool true if the spectacle is added, false otherwise
     */
    public function addSpectacleToSoiree(int $id_soiree, int $id_spectacle): bool
    {
        // Check if the association already exists
        $checkSql = "SELECT COUNT(*) FROM soiree2spectacle WHERE id_soiree = :id_soiree AND id_spectacle = :id_spectacle";
        $stmt = $this->pdo->prepare($checkSql);
        $stmt->execute(['id_soiree' => $id_soiree, 'id_spectacle' => $id_spectacle]);
        $exists = $stmt->fetchColumn() > 0;

        if ($exists) {
            return false;
        }

        // Add the association if it does not exist
        $sql = "INSERT INTO soiree2spectacle (id_soiree, id_spectacle) VALUES (:id_soiree, :id_spectacle)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id_soiree' => $id_soiree, 'id_spectacle' => $id_spectacle]);
    }

    /**
     * Get the details of a party
     * @param int $soireeId party ID
     * @return array party details
     * @throws Exception
     */
    public function getSoireeDetails(int $soireeId): array
    {
        $query = "SELECT nom, thematique, date, horaire_debut, horaire_fin, id_lieu, tarif FROM soiree WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $soireeId, PDO::PARAM_INT);
        $stmt->execute();
        $soireeDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$soireeDetails) {
            throw new Exception("Aucune soirée trouvée avec l'ID spécifié.");
        }
        return $soireeDetails;
    }

    /**
     * Get the details of a location
     * @param int $lieuId location ID
     * @return array location details
     * @throws Exception
     */
    public function getLieuDetails(int $lieuId): array
    {
        $query = "SELECT nom, adresse FROM lieu WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $lieuId, PDO::PARAM_INT);
        $stmt->execute();
        $lieuDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$lieuDetails) {
            throw new Exception("Aucun lieu trouvé avec l'ID spécifié.");
        }
        return $lieuDetails;
    }

    /**
     * Get the list of spectacle for a party
     * @param int $soireeId
     * @return array list of spectacles
     * @throws Exception
     */
    public function getSpectaclesFromSoiree(int $soireeId): array
    {
        $query = "
        SELECT *
        FROM spectacle s 
        JOIN soiree2spectacle ON soiree2spectacle.id_spectacle = s.id 
        WHERE soiree2spectacle.id_soiree = :id_soiree";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id_soiree', $soireeId, PDO::PARAM_INT);
        $stmt->execute();
        $spectacles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$spectacles) {
            throw new Exception("Aucun spectacle trouvé pour la soirée spécifiée.");
        }
        return $spectacles;
    }

    /**
     * Get the party ID from a spectacle ID
     * @param int $spectacleId spectacle ID
     * @return int|null party ID
     */
    public function getSoireeIdBySpectacleId(int $spectacleId): ?int
    {
        $sql = 'SELECT id_lieu FROM soiree 
            JOIN soiree2spectacle ON soiree.id = soiree2spectacle.id_soiree 
            WHERE soiree2spectacle.id_spectacle = ? LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$spectacleId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ? (int)$result['id_lieu'] : null;
    }

    /**
     * Gety the style of a spectacle
     * @param int $spectacleId spectacle ID
     * @return string|null style of the spectacle
     */
    public function getStyleFromSpectacleId(int $spectacleId): ?string
    {
        $sql = "SELECT style 
                FROM soiree s 
                JOIN soiree2spectacle s2s on s.id = s2s.id_soiree 
                JOIN spectacle Sp on Sp.id = s2s.id_spectacle 
                WHERE s.id = :id ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$spectacleId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? (string)$result['style'] : null;
    }

    /**
     * Get the location of a spectacle
     * @param int $spectacleId spectacle ID
     * @return string|null location of the spectacle
     */
    public function getLieuFromSpectacleId(int $spectacleId): ?string
    {
        $sql = "SELECT l.nom, l.adresse 
                FROM lieu l 
                JOIN soiree s on s.id_lieu= l.id 
                JOIN soiree2spectacle s2s on s2s.id_soiree = s.id 
                JOIN spectacle sp on sp.id = s2s.id_spectacle 
                WHERE sp.id = :id ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$spectacleId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? (string)$result['nom'] : null;
    }
}