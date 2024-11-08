<?php
declare(strict_types=1);

require_once './vendor/autoload.php';

use \nrv\repository\NrvRepository;
use nrv\dispatch\Dispatcher;

session_start();

try {
    NrvRepository::setConfig('../config/db.config.ini');
} catch (Exception $e) {
    print("Erreur de configuration : " . $e->getMessage());
}

$d = new Dispatcher();
$d->run();