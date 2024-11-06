<?php
declare(strict_types=1);

namespace iutnc\deefy\repository;

use Exception;
use iutnc\deefy\auth\User;
use PDO;

/**
 * Classe permettant de gérer les accès à la base de données
 */
class NrvRepository {

    /** @var PDO connexion avec la BD */
    private PDO $pdo;
    /** @var DeefyRepository|null instance unique de la classe */
    private static ?DeefyRepository $instance = null;
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
    public static function getInstance(): ?DeefyRepository {
        if (is_null(self::$instance)) {
            self::$instance = new DeefyRepository(self::$config);
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
        $stmt = $this->pdo->prepare("INSERT INTO user (email, passwd, role) VALUES (?, ?, ?)");
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
        $stmt = $this->pdo->prepare("SELECT passwd FROM user WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch()[0];
    }

}