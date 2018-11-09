<?php
namespace MessagingCenter\Test\App\Config;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Routing\Router;

Router::connect('/users/login', ['controller' => 'Users', 'action' => 'login']);
