<?php
declare(strict_types=1);

namespace nrv\repository;

use Exception;
use nrv\auth\User;
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
        $stmt = $this->pdo->prepare("SELECT I.url
                                            FROM image I
                                            JOIN spectacle2image S2I ON I.id = S2I.id_image
                                            WHERE S2I.id_spectacle = ?");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    /** fonction permettant d'ajouter un spectacle à la base de données */
    function ajouterSpectacle($pdo, $titre, $description, $url, $horaire, $style)
    {
        $sql = "INSERT INTO spectacle (titre, description, url, horaire, style) VALUES (titre, description, url, horaire, style)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'titre' => $titre,
            'description' => $description,
            'url' => $url,
            'horaire' => $horaire,
            'style' => $style
        ]);
    }


    /** fonction permettant de créer un spectacle de saisir les données et les valider */
    function creerSpectacle()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire et les valider
            $titre = trim($_POST['titre']);
            $description = trim($_POST['description']);
            $url = trim($_POST['url']);
            $horaire = trim($_POST['horaire']);
            $style = trim($_POST['style']);


            // Gestion des erreurs et validation des données
            $errors = [];

            if (empty($titre)) {
                $errors[] = "le titre est requis.";

            }

            if (strlen($description) > 200) {
                $errors[] = " la description ne peut pas dépasser 200 caractères.";
            }

            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                $errors[] = "l'url n'est pas valide";
            }

            if (empty($horaire) || !preg_match('/^(2[0-3]|[01]?[0-9]):[0-5][0-9]$/', $horaire)) {
                $errors[] = "L'horaire n'est pas au bon format veuillez la mettre au format HH:MM.";
            }
            if (empty($style)) {
                $errors[] = "Le style de musique est requis veuillez le mentionner.";
            }
            if (empty($errors)) {
                if (!empty($pdo)) {
                    if (ajouterSpectacle($pdo, $titre, $description, $url, $style, $horaire)) {
                        echo "<p>Le spectacle a été ajouté avec succès !</p>";

                    } else {
                        echo "<p> Une erreur est survenue lors de l'ajout du spectacle</p>";
                    }
                }


            }
            /** fonction permettant d'ajouter une soirée à la base de données */
            function ajouterSoiree($pdo, $nom, $thematique, $date, $horaire_debut, $horaire_fin, $id_lieu)
            {
                $sql = "INSERT INTO soiree (nom, thematique, date, horaire_debut, horaire_fin, id_lieu) 
            VALUES (nom, thematique, date, horaire_debut, horaire_fin, id_lieu)";
                $stmt = $pdo->prepare($sql);
                return $stmt->execute([
                    'nom' => $nom,
                    'thematique' => $thematique,
                    'date' => $date,
                    'horaire_debut' => $horaire_debut,
                    'horaire_fin' => $horaire_fin,
                    'id_lieu' => $id_lieu
                ]);
            }

            /** Fonctionnalité permettant de créer une soirée de saisir les données et les valider
             * (Même raisonnement que pour la fonctionnalité précédente mais avec des soirées)
             */
            function creerSoiree()
            {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Récupérer les données du formulaire et les valider
                    $nom = trim($_POST['nom']);
                    $thematique = trim($_POST['thematique']);
                    $date = trim($_POST['date']);
                    $horaire_debut = trim($_POST['horaire_debut']);
                    $horaire_fin = trim($_POST['horaire_fin']);
                    $id_lieu = trim($_POST['id_lieu']);

                    $errors = [];

                    // Validation des données
                    if (empty($nom)) {
                        $errors[] = "Le nom de la soirée est requis.";
                    }

                    if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                        $errors[] = "La date est requise et doit être au format AAAA-MM-JJ.";
                    }

                    if (empty($horaire_debut) || !preg_match('/^(2[0-3]|[01]?[0-9]):[0-5][0-9]$/', $horaire_debut)) {
                        $errors[] = "L'horaire de début est requis et doit être au format HH:MM.";
                    }

                    if (empty($horaire_fin) || !preg_match('/^(2[0-3]|[01]?[0-9]):[0-5][0-9]$/', $horaire_fin)) {
                        $errors[] = "L'horaire de fin est requis et doit être au format HH:MM.";
                    }

                    if (empty($id_lieu) || !is_numeric($id_lieu)) {
                        $errors[] = "Le lieu est requis et doit être un identifiant valide.";
                    }

                    if (empty($errors)) {
                        if (!empty($pdo)) {
                            if (ajouterSoiree($pdo, $nom, $thematique, $date, $horaire_debut, $horaire_fin, $id_lieu)) {
                                echo "<p>La soirée a été ajoutée avec succès !</p>";
                            } else {
                                echo "<p>Une erreur est survenue lors de l'ajout de la soirée.</p>";
                            }
                        }

                    }
                }
            }


        }
    }

    /**
     * Récupérer les détails d'un spectacle par son ID
     * @param int $spectacleId ID du spectacle
     * @return array informations du spectacle
     * @throws Exception
     */
    public function getSpectacleDetails(int $spectacleId): array {
        $stmt = $this->pdo->prepare("SELECT titre, description, style, horaire, url FROM Spectacle WHERE id = ?");
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
     * @return array liste des URLs d'images
     */
    public function getSpectacleImages(int $spectacleId): array {
        $stmt = $this->pdo->prepare("SELECT i.url FROM Image i INNER JOIN spectacle2image si ON i.id = si.id_image WHERE si.id_image = ?");
        $stmt->execute([$spectacleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}