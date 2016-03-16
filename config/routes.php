<?php
use Cake\Routing\Router;

Router::plugin(
    'MessagingCenter',
    ['path' => '/messaging-center'],
    function ($routes) {
        $routes->fallbacks('DashedRoute');
    }
);
