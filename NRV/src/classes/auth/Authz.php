<?php
declare(strict_types=1);

namespace nrv\auth;

use nrv\exception\AuthzException;
use nrv\exception\InvalidPropertyNameException;
use nrv\repository\NrvRepository;

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
     * @throws AuthzException
     * @throws InvalidPropertyNameException
     */
    public function checkRole(int $required) : void {
        if ($this->authenticated_user->__get('role') < $required) throw new AuthzException("<div><h2>Erreur 403</h2>Droits insuffisants</div>");
    }

}