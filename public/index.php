<?php

require_once "./../bootstrap.php";

$router = new App\Services\RouterService($entityManager);
$router->dispatch();