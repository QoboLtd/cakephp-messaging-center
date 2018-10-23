<?php
use Cake\Routing\Router;

Router::plugin(
    'Qobo/MessagingCenter',
    ['path' => '/messaging-center'],
    function ($routes) {
        $routes->connect('/messages/', ['controller' => 'Messages', 'action' => 'folder']);
        $routes->fallbacks('DashedRoute');
    }
);
