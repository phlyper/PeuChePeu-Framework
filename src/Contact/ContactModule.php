<?php

namespace App\Contact;

use Framework\App;
use Framework\Module;
use Framework\View\ViewInterface;

class ContactModule extends Module
{
    public function __construct(App $app, ViewInterface $view)
    {
        $view->addPath(__DIR__ . '/views', 'contact');

        // Routes
        $app
            ->map(['GET', 'POST'], '/contact', [Controller\ContactController::class, 'contact'])
            ->setName('contact');
    }
}
