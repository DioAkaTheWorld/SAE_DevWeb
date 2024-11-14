<?php

namespace nrv\auth;

use nrv\exception\AuthnException;
use nrv\repository\NrvRepository;

/**
 * Abstract class to manage the authentication
 */
abstract class AuthnProvider {

    /**
     * Function to connect an user
     * @param string $email email of the user
     * @param string $passwd2check password to check
     * @return void
     * @throws AuthnException
     */
    public static function signin(string $email, string $passwd2check): void {
        $r = NrvRepository::getInstance();
        $hash = $r->getHash($email);

        if (password_verify($passwd2check, $hash)) {
            $_SESSION['user'] = $r->getUser($email);
        } else {
            throw new AuthnException("Email ou mot de passe incorrect");
        }
    }

    /**
     * Function to get the connected user
     * @return User the connected user
     * @throws AuthnException
     */
    public static function getSignedInUser() : User {
        if (!isset($_SESSION['user'])) {
            throw new AuthnException("Non connecté");
        }
        return $_SESSION['user'];
    }

    /**
     * Function to test if the user is connected
     * @return bool true if the user is connected, false otherwise
     */
    public static function isSignedIn() : bool {
        return isset($_SESSION['user']);
    }

    /**
     * Function to register a new user
     * @param string $email email of the new user
     * @param string $pass password of the new user
     * @param int $role role of the new user
     * @return void
     * @throws AuthnException
     */
    public static function register(string $email, string $pass, int $role): void {
        $repo = NrvRepository::getInstance();
        if ($repo->userExistsEmail($email)) {
            throw new AuthnException("Adresse email déjà utilisée");
        }

        if (self::checkPasswordStrength($pass) === false) {
            throw new AuthnException("Mot de passe invalide");
        }

        if ($role != User::STANDARD_USER && $role != User::STAFF) {
            throw new AuthnException("Rôle invalide");
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT, ['cost' => 12]);
        $repo->addUser($email, $hash, $role);
    }

    /**
     * Function to check the strength of a password
     * @param string $pass password to check
     * @return bool true if the password is strong enough, false otherwise
     */
    public static function checkPasswordStrength(string $pass): bool {
        $length = (strlen($pass) >= 10); // At least 10 characters
        $digit = preg_match("#[\d]#", $pass); // At least one digit
        $special = preg_match("#[\W]#", $pass); // At least one special character
        $lower = preg_match("#[a-z]#", $pass); // At least one lowercase
        $upper = preg_match("#[A-Z]#", $pass); // At least one uppercase
        if (!$length || !$digit || !$special || !$lower || !$upper) return false;
        return true;
    }

}