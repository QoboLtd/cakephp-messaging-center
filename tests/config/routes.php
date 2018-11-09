<?php
namespace MessagingCenter\Test\App\Config;

use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::defaultRouteClass(DashedRoute::class);
Router::connect('/:controller/:action/*');
