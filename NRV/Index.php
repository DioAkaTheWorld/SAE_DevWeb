<?php
declare(strict_types=1);

require_once './vendor/autoload.php';

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\dispatch\Dispatcher;

session_start();

try {
    DeefyRepository::setConfig('config/db.config.ini');
} catch (Exception $e) {
    print("Erreur de configuration : " . $e->getMessage());
}

$d = new Dispatcher();
$d->run();