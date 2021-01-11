<?php    

require __DIR__ . '/../vendor/autoload.php';  
require __DIR__ . '/../app/config/settings.php';

$app = new \Slim\App(["settings" => $config]);

require __DIR__ . '/../app/config/dependencies.php';
require __DIR__ . '/../app/config/routes.php';

$app->run();