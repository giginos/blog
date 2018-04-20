<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;

require_once dirname(__FILE__) . '/vendor/autoload.php';

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(
    [__DIR__ . "/App"],
    $isDevMode,
    null,
    null,
    false
);

// database configuration parameters
$conn = [
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/db.sqlite',
];

$dbParams = [
    'driver'   => 'pdo_mysql',
    'user'     => 'root',
    'password' => 'root',
    'dbname'   => 'blog',
];

// obtaining the entity manager
try {
    $entityManager = EntityManager::create($conn, $config);
} catch (ORMException $ormException) {
    // Abort, if EntityManager can not be initialized
    trigger_error(
        $ormException->getMessage() . PHP_EOL .  $ormException->getTraceAsString(),
        E_USER_ERROR
    );
}
