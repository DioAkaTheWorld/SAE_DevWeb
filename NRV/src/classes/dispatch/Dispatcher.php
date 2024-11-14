<?php
declare(strict_types=1);

namespace nrv\dispatch;

use nrv\action\AddImageToSpecacleAction;
use nrv\action\AddSoireeAction;
use nrv\action\AddSpectacleAction;
use nrv\action\AddSpectacleToSoireeAction;
use nrv\action\RegisterAction;
use nrv\action\DefaultAction;
use nrv\action\DisplayAllSpectaclesAction;
use nrv\action\DisplaySoireeAction;
use nrv\action\DisplaySpectacleAction;
use nrv\action\DisplaySpectacleByStyleAction;
use nrv\action\DisplaySpectaclesByDatesAction;
use nrv\action\DisplaySpectaclesByLocation;
use nrv\action\ModifySpectacleAction;
use nrv\action\LogInAction;
use nrv\action\LogOutAction;
use nrv\auth\User;

/**
 * Classe Dispatcher
 *
 * Cette classe est le point d'entrée de l'application. Elle est responsable de
 * déterminer quelle action doit être exécutée en fonction de la requête de
 * l'utilisateur, puis d'exécuter cette action et de renvoyer le résultat.
 */
class Dispatcher {

    /** @var string action à réaliser */
    private string $action;

    /**
     * Constructeur
     *
     * Initialise l'action à réaliser en fonction de la requête de l'utilisateur.
     */
    public function __construct() {
        if (isset($_GET['action'])) {
            $this->action = $_GET['action'];
        } else {
            $this->action = 'default';
        }
    }

    /**
     * Exécute l'action demandée par l'utilisateur et affiche le résultat.
     * @return void
     */
    public function run(): void {
        $actionObjet = match ($this->action) {
            'display-all-spectacles' => new DisplayAllSpectaclesAction(),
            'add-spectacle' => new AddSpectacleAction(),
            'modify-spectacle' => new ModifySpectacleAction(),
            'add-soiree' => new AddSoireeAction(),
            'display-spectacle' => new DisplaySpectacleAction(),
            'display-soiree' => new DisplaySoireeAction(),
            'register' => new RegisterAction(),
            'log-in' => new LogInAction(),
            'log-out' => new LogOutAction(),
            'display-spectacles-by-style' => new DisplaySpectacleByStyleAction(),
            'display-spectacles-by-date' => new DisplaySpectaclesByDatesAction(),
            'display-spectacles-by-lieu' => new DisplaySpectaclesByLocation(),
            'add-spectacle-to-soiree' => new AddSpectacleToSoireeAction(),
            default => new DefaultAction(),
        };
        $this->renderPage($actionObjet());
    }

    /**
     * Affiche la page HTML en fonction de l'état de connexion de l'utilisateur.
     *
     * @param string $html contenu de la page à afficher selon l'action demandée
     */
    private function renderPage(string $html): void {
        $connected = isset($_SESSION['user']);

        echo <<<FIN
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='utf-8'>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>NRV</title>
            <link rel="stylesheet" href="../style.css">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        </head>
        <body data-bs-theme="dark">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark p-3">
                <div class="container-fluid">
                    <a class="navbar-brand" href="index.php">NRV Festival</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse flex-row-reverse" id="navbarNavDropdown">
                        <ul class="navbar-nav d-flex flex-sm-row flex-column align-items-center text-center my-2">
                            {$this->renderNavBarItems($connected)}
                        </ul>
                    </div>
                </div>
            </nav>
            <div class="container my-4 p-4 page-content">
                $html
            </div>
            <footer class="position-relative bottom-0 start-0">
                &copy; 2024 NRV Festival. Tous droits réservés.
            </footer>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        </body>
        </html>
        FIN;
    }

    /**
     * Renvoie le contenu HTML des éléments de la barre de navigation en fonction
     * de l'état de connexion de l'utilisateur et de son rôle.
     *
     * @param bool $connected état de connexion de l'utilisateur
     * @return string contenu HTML des éléments de la barre de navigation
     */
    private function renderNavBarItems(bool $connected): string {
        if ($connected) {
            $user = $_SESSION['user'];
            $role = $user->__get('role');
            return match ($role) {
                User::STANDARD_USER => $this->renderNavBarItemsStandardUser(),
                User::STAFF => $this->renderNavBarItemsStaffUser(),
                User::ADMIN => $this->renderNavBarItemsAdminUser(),
                default => $this->renderNavBarItemsNotConnected(),
            };
        }

        return $this->renderNavBarItemsNotConnected();
    }

    /**
     * Fais le rendu de la barre de navigation pour un utilisateur standard.
     * @return string contenu HTML des éléments de la barre de navigation
     */
    private function renderNavBarItemsStandardUser(): string {
        return <<<FIN
        <li class="nav-item p-1">
                            <a class="nav-link" href="?action=display-all-spectacles">Les spectacles</a>
                        </li>
                        <li class="nav-item p-1 d-flex align-items-center">
                            <a class="btn btn-danger text-dark my-0 p-2" href="?action=log-out"><strong>Se déconnecter</strong></a>
                        </li>
        FIN;
    }

    /**
     * Fais le rendu de la barre de navigation pour un utilisateur staff.
     * @return string contenu HTML des éléments de la barre de navigation
     */
    private function renderNavBarItemsStaffUser() : string {
        return <<<FIN
            <li class="nav-item p-1">
                                <a class="nav-link" href="?action=display-all-spectacles">Les spectacles</a>
                            </li>
                            <li class="nav-item p-1">
                                <a class="nav-link" href="?action=add-spectacle">Ajouter un spectacle</a>
                            </li>
                            <li class="nav-item p-1">
                                <a class="nav-link" href="?action=add-soiree">Ajouter une soirée</a>
                            </li>
                            <li class="nav-item p-1">
                                <a class="nav-link" href="?action=add-spectacle-to-soiree">Ajouter un spectacle à une soirée</a>
                            </li>
                            <li class="nav-item p-1 d-flex align-items-center">
                                <a class="btn btn-danger text-dark my-0 p-2" href="?action=log-out"><strong>Se déconnecter</strong></a>
                            </li>
            FIN;
    }

    /**
     * Fais le rendu de la barre de navigation pour un utilisateur admin.
     * @return string contenu HTML des éléments de la barre de navigation
     */
    private function renderNavBarItemsAdminUser() : string {
        return <<<FIN
            <li class="nav-item p-1">
                                <a class="nav-link" href="?action=display-all-spectacles">Les spectacles</a>
                            </li>
                            <li class="nav-item p-1">
                                <a class="nav-link" href="?action=add-spectacle">Ajouter un spectacle</a>
                            </li>
                            <li class="nav-item p-1">
                                <a class="nav-link" href="?action=add-soiree">Ajouter une soirée</a>
                            </li>
                            <li class="nav-item p-1">
                                <a class="nav-link" href="?action=add-spectacle-to-soiree">Ajouter un spectacle à une soirée</a>
                            </li>
                            <!--    Nom implémenté     -->
                            <li class="nav-item p-1">
                                <a class="nav-link" href="?action=register">Créer un compte staff</a>
                            </li>
                            <!--    Nom implémenté     -->
                            <li class="nav-item p-1 d-flex align-items-center">
                                <a class="btn btn-danger text-dark my-0 p-2" href="?action=log-out"><strong>Se déconnecter</strong></a>
                            </li>
            FIN;
    }

    /**
     * Fais le rendu de la barre de navigation pour un utilisateur non connecté.
     * @return string contenu HTML des éléments de la barre de navigation
     */
    private function renderNavBarItemsNotConnected() : string {
        return <<<FIN
            <li class="nav-item p-1">
                                <a class="nav-link" href="?action=register">S'inscrire</a>
                            </li>
                            <li class="nav-item p-1 pe-5">
                                <a class="nav-link" href="?action=log-in">Se connecter</a>
                            </li>
            FIN;
    }

}