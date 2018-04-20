<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once __DIR__ . '/../vendor/autoload.php';

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(
    [__DIR__ . "/../App"],
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
    'dbname'   => 'unittest_blog',
];

// obtaining the entity manager
$entityManager = EntityManager::create($conn, $config);

if (!$entityManager instanceof EntityManager) {
    throw new \Exception('Unable to create EntityManager.');
}
