<?php

use Schnittstabil\Psr7\Csrf\MiddlewareBuilder as CsrfMiddlewareBuilder;

return [
    // Env
    'dev'                                        => true,

    // Chemins
    'basepath'                                   => dirname(__DIR__),
    'settings.addContentLengthHeader'            => false,
    'settings.displayErrorDetails'               => true,
    'settings.routerCacheFile'                   => function (\Psr\Container\ContainerInterface $c) {
        if ($c->get('dev')) {
            return false;
        }

        return $c->get('basepath') . '/tmp/routes';
    },
    'settings.determineRouteBeforeAppMiddleware' => true,
    'settings'                                   => \DI\add(['debug' => \DI\get('dev')]),
    'errorHandler'                               => \DI\object(\Framework\Handler::class),
    'upload_path'                                => \DI\string('{basepath}/public/uploads'),

    // Misc
    \Slim\Interfaces\RouterInterface::class      => \DI\object(\Slim\Router::class),

    // Vue
    'view.cache'                                 => \DI\string('{basepath}/tmp/views'),
    'view'                                       => function (\Psr\Container\ContainerInterface $c) {
        return new \Framework\View\TwigView([
            new \Framework\Twig\ModuleExtension($c->get('app')->getModules()),
            new \Framework\Twig\RouterExtension($c->get('router'), $c->get('request')->getUri()),
            new \Framework\Twig\PagerfantaExtension($c->get('router')),
            new \Framework\Twig\TimeExtension(),
            new \Framework\Twig\CsrfExtension($c->get('csrf.name'), $c->get('csrf')),
            new \Knlv\Slim\Views\TwigMessages($c->get(\Slim\Flash\Messages::class)),
            new \Framework\Twig\TextExtension()
        ], $c->get('dev') ? false : $c->get('view.cache'));
    },
    \Framework\View\ViewInterface::class         => \DI\get('view'),

    // Session
    'session'                                    => \DI\object(\Framework\Session\Session::class),
    'session.flash'                              => \DI\object(\Slim\Flash\Messages::class)
        ->constructor(\DI\get('session')),
    \Framework\Session\SessionInterface::class   => \DI\get('session'),
    \Slim\Flash\Messages::class                  => \DI\get('session.flash'),

    // CSRF
    'csrf.name'                                  => 'X-XSRF-TOKEN',
    'csrf.key'                                   => \DI\env('csrf_key', 'fake key'),
    'csrf'                                       => function ($c) {
        return CsrfMiddlewareBuilder::create($c->get('csrf.key'))
            ->buildSynchronizerTokenPatternMiddleware($c->get('csrf.name'));
    },
    \Framework\Twig\CsrfExtension::class         => \DI\object()
        ->constructor(
            \DI\get('csrf.name'),
            \DI\get('csrf')
        ),

    // Database
    'db_name'                                    => \DI\env('db_name'),
    'db_username'                                => \DI\env('db_username', 'root'),
    'db_password'                                => \DI\env('db_password', 'root'),
    'db_host'                                    => \DI\env('db_host', '127.0.0.1'),
    \Framework\Database\Database::class          => \DI\object()->constructor(
        \DI\get('db_name'),
        \DI\get('db_username'),
        \DI\get('db_password'),
        \DI\get('db_host')
    ),
    'db'                                         => \DI\get(\Framework\Database\Database::class),

    // Mailer
    'mailer.webmaster'                           => 'demo@local.dev',
    'mailer.transport'                           => \DI\object(Swift_SmtpTransport::class)
        ->constructor('localhost', 1025),
    'mailer'                                     => \DI\object(Swift_Mailer::class)
        ->constructor(\DI\get('mailer.transport')),
    Swift_Mailer::class                          => \DI\get('mailer'),
    \Framework\Mail::class                       => \DI\factory(function (\Psr\Container\ContainerInterface $c) {
        return new \Framework\Mail(
            $c->get('mailer.webmaster'),
            $c->get('mailer'),
            $c->get('view')
        );
    })
];
