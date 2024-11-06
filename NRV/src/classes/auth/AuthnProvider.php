<?php

namespace iutnc\deefy\auth;

use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\repository\DeefyRepository;

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
        $r = DeefyRepository::getInstance();
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
        $repo = DeefyRepository::getInstance();
        if ($repo->userExistsEmail($email)) {
            throw new AuthnException("Adresse email déjà utilisée");
        }

        if (strlen($pass) < 10) {
            throw new AuthnException("Mot de passe trop court: 10 caractères minimum");
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT, ['cost' => 12]);
        $repo->addUser($email, $hash);
    }

}