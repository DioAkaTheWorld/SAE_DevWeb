<?php
declare(strict_types=1);

namespace nrv\auth;

use nrv\exception\InvalidPropertyNameException;

/**
 * Class to manage the user
 */
class User {

    /** @var int unique identifier */
    private int $id;
    /** @var string email */
    private string $email;
    /** @var string password hash */
    private string $hash;
    /** @var int role */
    private int $role;

    /** @var int constant defining the standard role */
    const STANDARD_USER = 1;
    /** @var int constant defining the staff role */
    const STAFF = 50;
    /** @var int constant defining the admin role */
    const ADMIN = 100;

    /**
     * Constructor
     * @param int $id unique identifier
     * @param string $email email
     * @param string $hash password hash
     * @param int $role role
     */
    public function __construct(int $id, string $email, string $hash, int $role) {
        $this->id = $id;
        $this->email = $email;
        $this->hash = $hash;
        $this->role = $role;
    }

    /**
     * Magic method to get the value of an attribute
     * @param string $attrName name of the attribute
     * @return mixed value of the attribute
     * @throws InvalidPropertyNameException
     */
    public function __get(string $attrName) : mixed {
        if (property_exists($this, $attrName)) {
            return $this->$attrName;
        }
        throw new InvalidPropertyNameException("Propriété inexistante : $attrName");
    }
}
