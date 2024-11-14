<?php
declare(strict_types=1);

namespace nrv\auth;

use nrv\exception\AuthzException;
use nrv\exception\InvalidPropertyNameException;
use nrv\repository\NrvRepository;

/**
 * Class to manage the authorization
 */
class Authz {

    /** @var User Connected user */
    private User $authenticated_user;

    /**
     * Constructor
     * @param User $user Connected user
     */
    public function __construct(User $user) {
        $this->authenticated_user = $user;
    }


    /**
     * Check if the user has the required role
     * @param int $required Required role
     * @return void
     * @throws AuthzException
     * @throws InvalidPropertyNameException
     */
    public function checkRole(int $required) : void {
        if ($this->authenticated_user->__get('role') < $required) throw new AuthzException("<div><h2>Erreur 403</h2>Droits insuffisants</div>");
    }

}