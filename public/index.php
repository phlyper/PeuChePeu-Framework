<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// COnstantes
define('WWW_ROOT', __DIR__);
define('UPLOAD_PATH', __DIR__ . '/uploads');

// On démarre slim
$app = new \Framework\App(
    dirname(__DIR__) . '/config.php',
    [
        \App\Base\BaseModule::class,
        \App\Admin\AdminModule::class,
        \App\Auth\AuthModule::class,
        \App\Blog\BlogModule::class,
        \App\Contact\ContactModule::class,
        \App\Error\ErrorModule::class,
        \App\Registration\RegistrationModule::class
    ]
);

// On lance l'application
if (php_sapi_name() !== "cli") {
    $content = $app->run(true);
    $app->respond($content);
}
