<?php
declare(strict_types=1);

namespace nrv\dispatch;

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
use nrv\auth\AuthnProvider;
use nrv\auth\User;

/**
 * Dispatcher class
 *
 * Dispatches the action requested by the user and displays the result.
 * Each action is represented by an object.
 * This class is the entry point of the application.
 */
class Dispatcher {

    /** @var string action to do */
    private string $action;

    /**
     * Constructor
     */
    public function __construct() {
        if (isset($_GET['action'])) {
            $this->action = $_GET['action'];
        } else {
            $this->action = 'default';
        }
    }

    /**
     * Dispatches the action requested by the user and displays the result.
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
     * Displays the page with the content of the action requested by the user.
     * @param string $html HTML content of the page
     */
    private function renderPage(string $html): void {
        $connected = AuthnProvider::isSignedIn();

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
                    <a class="navbar-brand" href="index.php">
                        <img src="/SAE_DevWeb/medias/images/rock.webp" alt="Logo" width="60" height="48" class="d-inline-block align-text-center">
                        <span class="mx-2">NRV Festival</span>
                    </a>
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
     * Renders the items of the navigation bar according to the user's role.
     * @param bool $connected True if the user is connected, false otherwise
     * @return string HTML content of the navigation bar items
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
     * Renders the navigation bar items for a standard user.
     * @return string HTML content of the navigation bar items
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
     * Renders the navigation bar items for a staff user.
     * @return string HTML content of the navigation bar items
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
     * Renders the navigation bar items for an admin user.
     * @return string HTML content of the navigation bar items
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
     * Renders the navigation bar items for a user that is not connected.
     * @return string HTML content of the navigation bar items
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