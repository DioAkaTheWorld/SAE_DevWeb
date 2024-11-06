<?php
declare(strict_types=1);

namespace iutnc\deefy\auth;

use iutnc\deefy\exception\AuthzException;
use iutnc\deefy\exception\InvalidPropertyNameException;
use iutnc\deefy\repository\DeefyRepository;

/**
 * Classe pour la gestion des autorisations
 */
class Authz {

    /** @var User Utilisateur connecté */
    private User $authenticated_user;

    /**
     * Constructeur
     *
     * @param User $user Utilisateur connecté
     */
    public function __construct(User $user) {
        $this->authenticated_user = $user;
    }


    /**
     * Vérifie si l'utilisateur connecté a le rôle nécessaire
     * @param int $required rôle nécessaire
     * @return void
     * @throws InvalidPropertyNameException
     * @throws AuthzException
     */
    public function checkRole(int $required) : void {
        if ($this->authenticated_user->__get('role') < $required) throw new AuthzException("<div><h2>Erreur 403</h2>Droits insuffisants</div>");
    }

    /**
     * Vérifie si l'utilisateur connecté est propriétaire de la playlist
     * @param int $playlistID ID de la playlist
     * @return void
     * @throws AuthzException
     * @throws InvalidPropertyNameException
     */
    public function checkPlaylistOwner(int $playlistID) : void {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            throw new AuthzException("<div><h2>Erreur 401</h2>Vous n'êtes pas connecté</div>");
        }
        $repo = DeefyRepository::getInstance();
        $userID = $_SESSION['user']->__get('id');
        if (!$repo->userExistsId($userID)) {
            http_response_code(404);
            throw new AuthzException("<div><h2>Erreur 404</h2>Utilisateur inexistant</div>");
        }

        if((!$repo->isPlaylistFromUser($userID, $playlistID)) && ($this->authenticated_user->__get('role') < (User::ADMIN))) {
            throw new AuthzException("Non propriétaire de la playlist");
        }
    }
}