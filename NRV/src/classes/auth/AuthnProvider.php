<?php

namespace nrv\auth;

use nrv\exception\AuthnException;
use nrv\repository\NrvRepository;

/**
 * Classe abstraite pour la gestion de l'authentification
 */
abstract class AuthnProvider {

    /**
     * Fonction de connexion
     * @param string $email email de l'utilisateur
     * @param string $passwd2check mot de passe en clair à vérifier
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
     * Fonction qui retourne l'utilisateur connecté
     * @return User l'utilisateur connecté
     * @throws AuthnException
     */
    public static function getSignedInUser() : User {
        if (!isset($_SESSION['user'])) {
            throw new AuthnException("Non connecté");
        }
        return $_SESSION['user'];
    }

    public static function isSignedIn() : bool {
        return isset($_SESSION['user']);
    }

    /**
     * Fonction d'incription
     * @param string $email email de l'utilisateur
     * @param string $pass mot de passe en clair
     * @return void
     * @throws AuthnException
     */
    public static function register(string $email, string $pass): void {
        $repo = NrvRepository::getInstance();
        if ($repo->userExistsEmail($email)) {
            throw new AuthnException("Adresse email déjà utilisée");
        }

        if (self::checkPasswordStrength($pass) === false) {
            throw new AuthnException("Mot de passe invalide");
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT, ['cost' => 12]);
        $repo->addUser($email, $hash);
    }

    /**
     * Vérifie la force du mot de passe
     * @param string $pass mot de passe à vérifier
     * @return bool true si le mot de passe est fort, false sinon
     */
    public static function checkPasswordStrength(string $pass): bool {
        $length = (strlen($pass) >= 10); // longueur minimale
        $digit = preg_match("#[\d]#", $pass); // au moins un digit
        $special = preg_match("#[\W]#", $pass); // au moins un car. spécial
        $lower = preg_match("#[a-z]#", $pass); // au moins une minuscule
        $upper = preg_match("#[A-Z]#", $pass); // au moins une majuscule
        if (!$length || !$digit || !$special || !$lower || !$upper) return false;
        return true;
    }

}