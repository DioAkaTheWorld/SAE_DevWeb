<?php
declare(strict_types=1);

namespace nrv\repository;

use Exception;
use nrv\auth\User;
use PDO;

/**
 * Classe permettant de gérer les accès à la base de données
 */
class NrvRepository {

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
     * @return DeefyRepository|null instance unique de la classe
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
        $stmt = $this->pdo->prepare("SELECT I.url
                                            FROM image I
                                            JOIN spectacle2image S2I ON I.id = S2I.id_image
                                            WHERE S2I.id_spectacle = ?");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }


    /** fonction permettant de filtrer pour un spectacle sa date  */
    function filtreSpecDate(){
        $query = "SELECT date,titre,horaire,image FROM spectacles where date = ?";
        return executeQuerywithparam($query,[$date]);

    }

    /** fonction permettant de filtrer par style de spectacle  */
    function filtreSpecStyle(){
        $query ="SELECT date,titre,horaire,image FROM spectacles where style = ?";
            return executeQuerywithparam($query,[$style]);
    }

    /** fonction permettant de filtrer par lieu de spectacle */
    function filtreSpecLieu($lieu){
        $query ="SELECT date,titre,horaire,image FROM spectacles where lieu = ? ";
        return executeQuerywithparam($query,[$lieu]);

    }





}