<?php
declare(strict_types=1);

namespace nrv\auth;

use nrv\exception\InvalidPropertyNameException;

/**
 * Classe représentant un utilisateur
 */
class User {

    /** @var int identifiant unique */
    private int $id;
    /** @var string email */
    private string $email;
    /** @var string hash du mot de passe */
    private string $hash;
    /** @var int rôle */
    private int $role;

    /** @var int constante définissant le role standard */
    const STANDARD_USER = 1;
    /** @var int constante définissant le role staff */
    const STAFF = 50;
    /** @var int constante définissant le role administrateur */
    const ADMIN = 100;

    /**
     * Constructeur
     *
     * @param int $id identifiant unique
     * @param string $email email
     * @param string $hash hash du mot de passe
     * @param int $role rôle
     */
    public function __construct(int $id, string $email, string $hash, int $role) {
        $this->id = $id;
        $this->email = $email;
        $this->hash = $hash;
        $this->role = $role;
    }

    /**
     * Getter magique
     * @param string $attrName nom de l'attribut
     * @return mixed valeur de l'attribut
     * @throws InvalidPropertyNameException
     */
    public function __get(string $attrName) : mixed {
        if (property_exists($this, $attrName)) {
            return $this->$attrName;
        }
        throw new InvalidPropertyNameException("Propriété inexistante : $attrName");
    }
}
