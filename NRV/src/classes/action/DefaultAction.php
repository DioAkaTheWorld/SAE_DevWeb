<?php
declare(strict_types=1);

namespace nrv\action;

/**
 * Action par défaut
 */
class DefaultAction extends Action {

    /**
     * Méthode exécutée en cas de requête GET
     * @return string HTML de la page d'accueil
     */
    public function executeGet(): string {
        // Message de bienvenue
        return <<<FIN
        <h2 class="h3 text-center" >Bienvenue sur Deefy !</h2>
                <div class="text-center my-4">
                    <p>Créez et personnalisez vos playlists en un clin d'œil ! Notre site vous permet de :</p>
                    <ul class="list-inline">
                        <li>Ajouter des playlists pour organiser vos morceaux selon vos styles et ambiances préférés.</li>
                        <li>Ajouter des pistes pour enrichir vos listes et explorer de nouveaux sons.</li>
                        <li>Afficher toutes vos playlists et profiter d’une vue d'ensemble claire et facile d'accès.</li>
                    </ul>
                </div>
        FIN;

    }

    /**
     * Méthode exécutée en cas de requête POST
     * @return string HTML de la page d'accueil
     */
    public function executePost(): string{
        return $this->executeGet();
    }
}