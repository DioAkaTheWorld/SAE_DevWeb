<?php
declare(strict_types=1);

namespace nrv\repository;

use Exception;
use nrv\auth\User;
use nrv\festivale\Soiree;
use nrv\festivale\Spectacle;
use PDO;

/**
 * Classe permettant de gérer les accès à la base de données
 */
class NrvRepository
{

    /** @var PDO connexion avec la BD */
    private PDO $pdo;
    /** @var NrvRepository|null instance unique de la classe */
    private static ?NrvRepository $instance = null;
    /** @var array configuration de la connexion à la BD */
    private static array $config = [];

    /**
     * Constructeur privé
     * @param array $conf configuration de la connexion à la BD
     */
    private function __construct(array $conf) {
        $this->pdo = new PDO($conf['dsn'], $conf['user'], $conf['pass'], [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false]);
        $this->pdo->prepare("SET NAMES 'utf8'")->execute();
    }

    /**
     * Méthode permettant de récupérer l'instance unique de la classe
     * @return NrvRepository instance unique de la classe
     */
    public static function getInstance(): ?NrvRepository {
        if (is_null(self::$instance)) {
            self::$instance = new NrvRepository(self::$config);
        }
        return self::$instance;
    }

    /**
     * Méthode permettant de définir la configuration de la connexion à la BD
     * @param string $file fichier de configuration
     * @return void
     * @throws Exception
     */
    public static function setConfig(string $file): void {
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
     * Fonction permettant de récupérer un utilisateur à partir de son email
     * @param string $email email de l'utilisateur
     * @return User utilisateur
     */
    public function getUser(string $email) : User {
        $stmt = $this->pdo->prepare("SELECT id, role FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $res = $stmt->fetchAll()[0];
        $id = $res['id'];
        $role = $res['role'];
        $hash = $this->getHash($email);
        return new User($id, $email, $hash, $role);
    }

    /**
     * Fonction permettant d'ajouter un utilisateur
     * @param string $email email de l'utilisateur
     * @param string $hash hash du mot de passe
     * @return void
     */
    public function addUser(string $email, string $hash) : void {
        $stmt = $this->pdo->prepare("INSERT INTO user (email, hash, role) VALUES (?, ?, ?)");
        $stmt->execute([$email, $hash, 1]);
    }

    /**
     * Fonction permettant de vérifier si un utilisateur existe à partir de son email
     * @param string $email email de l'utilisateur
     * @return bool true si l'utilisateur existe, false sinon
     */
    public function userExistsEmail(string $email) : bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch()[0] > 0;
    }

    /**
     * Fonction permettant de vérifier si un utilisateur existe à partir de son id
     * @param int $id id de l'utilisateur
     * @return bool true si l'utilisateur existe, false sinon
     */
    public function userExistsId(int $id) : bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM user WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch()[0] > 0;
    }

    /**
     * Fonction permettant de récupérer le hash du mot de passe d'un utilisateur
     * @param string $email email de l'utilisateur
     * @return string hash du mot de passe
     */
    public function getHash(string $email) : string {
        $stmt = $this->pdo->prepare("SELECT hash FROM user WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch()[0];
    }

    /**
     * Fonction permettant de récupérer tous les spectacles
     * @return array spectacles
     */
    public function findAllSpectacles() : array {
        $stmt = $this->pdo->prepare("SELECT * FROM spectacle");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fonction permettant de récupérer une date de spectacle à partir de son id
     * @param int $id id du spectacle
     * @return string date du spectacle
     */
    public function getDateSpectacle(int $id) : string {
        $stmt = $this->pdo->prepare("SELECT S.date 
                                            FROM soiree S
                                            JOIN soiree2spectacle S2P ON S.id = S2P.id_soiree
                                            JOIN spectacle SP ON S2P.id_spectacle = SP.id
                                            WHERE SP.id = ?");
        $stmt->execute([$id]);
        $date = $stmt->fetch();
        if (empty($date)) {
            return "Pas de date";
        }
        return $date['date'];
    }

    /**
     * Fonction permettant de récupérer les images d'un spectacle à partir de son id
     * @param int $id id du spectacle
     * @return array images du spectacle
     */
    public function getImagesSpectacle(int $id) : array {
        $stmt = $this->pdo->prepare("SELECT I.chemin_fichier
                                            FROM image I
                                            JOIN spectacle2image S2I ON I.id = S2I.id_image
                                            WHERE S2I.id_spectacle = ?");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    /**
     * Fonction permettant d'ajouter un spectacle
     * @param Spectacle $s spectacle à ajouter
     * @return Spectacle spectacle ajouté
     */
    function ajouterSpectacle(Spectacle $s) : Spectacle {
        $sql = "INSERT INTO spectacle (titre, description, chemin_video, horaire, duree, style) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$s->__get('titre'), $s->__get('description'), $s->__get('chemin_fichier'), $s->__get('horaire'), $s->__get('duree'), $s->__get('style')]);
        $s->setId((int)$this->pdo->lastInsertId());
        return $s;
    }

    /**
     * Ajoute une soirée
     * @param Soiree $s soirée à ajouter
     * @return Soiree soirée ajoutée
     */
    function ajouterSoiree(Soiree $s) : Soiree {
        $sql = "INSERT INTO soiree (nom, thematique, date, horaire_debut, horaire_fin, id_lieu) 
            VALUES (:nom, :thematique, :date, :horaire_debut, :horaire_fin, :id_lieu)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nom' => $s->__get('nom'),
            'thematique' => $s->__get('thematique'),
            'date' => $s->__get('date'),
            'horaire_debut' => $s->__get('horaire_debut'),
            'horaire_fin' => $s->__get('horaire_fin'),
            'id_lieu' => $s->__get('id_lieu')
        ]);
        $s->setId((int)$this->pdo->lastInsertId());
        return $s;
    }

    /**
     * Récupérer les détails d'un spectacle par son ID
     * @param int $spectacleId ID du spectacle
     * @return array informations du spectacle
     * @throws Exception
     */
    public function getSpectacleDetails(int $spectacleId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Spectacle WHERE id = ?");
        $stmt->execute([$spectacleId]);
        $spectacle = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$spectacle) {
            throw new Exception("Spectacle non trouvé");
        }
        return $spectacle;
    }

    /**
     * Récupérer les artistes d'un spectacle
     * @param int $spectacleId ID du spectacle
     * @return array liste des artistes
     */
    public function getSpectacleArtists(int $spectacleId): array {
        $stmt = $this->pdo->prepare("SELECT DISTINCT a.nom FROM artiste a INNER JOIN spectacle2artiste sa ON a.id = sa.id_spectacle WHERE sa.id_spectacle = ?");
        $stmt->execute([$spectacleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les images d'un spectacle
     * @param int $spectacleId ID du spectacle
     * @return array liste des chemin_fichiers d'images
     */
    public function getSpectacleImages(int $spectacleId): array {
        $stmt = $this->pdo->prepare("SELECT i.chemin_fichier FROM Image i INNER JOIN spectacle2image si ON i.id = si.id_image WHERE si.id_image = ?");
        $stmt->execute([$spectacleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Méthode permettant d'ajouter une image
     * @param string $chemin chemin de l'image
     * @return int id de l'image ajoutée
     */
    public function addImage(string $chemin) : int {
        $stmt = $this->pdo->prepare("INSERT INTO image (chemin_fichier) VALUES (?)");
        $stmt->execute([$chemin]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Méthode permettant d'ajouter une image à un spectacle
     * @param int $idImage id de l'image
     * @param int $idSpectacle id du spectacle
     * @return void
     */
    public function addImageToSpectacle(int $idImage, int $idSpectacle) : void {
        $stmt = $this->pdo->prepare("INSERT INTO spectacle2image (id_spectacle, id_image) VALUES (?, ?)");
        $stmt->execute([$idSpectacle, $idImage]);
    }

    /**
     * Méthode permettant de mettre à jour le chemin d'une image pour un spectacle
     * @param string $chemin chemin de la vidéo
     * @param int $idSpectacle id du spectacle
     * @return void
     */
    public function updateVideoPathForSpectacle(string $chemin, int $idSpectacle) : void {
        $stmt = $this->pdo->prepare("UPDATE spectacle SET chemin_video = ? WHERE id = ?");
        $stmt->execute([$chemin, $idSpectacle]);
    }

    /**
     * Fonction permettant de récupérer les spectacles par style
     * @param string $style style de musique
     * @return array spectacles filtrés par style
     */
    public function findSpectaclesByStyle(string $style) : array {
        $stmt = $this->pdo->prepare("SELECT * FROM spectacle WHERE style = ?");
        $stmt->execute([$style]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getListeStyleSpectacle() : array {
        $stmt = $this->pdo->prepare("SELECT DISTINCT style FROM spectacle");
        $stmt->execute();
        return $stmt->fetchAll();
    }

}