<?php
use Cake\Core\Configure;
use Cake\Event\EventManager;
use MessagingCenter\Event\Model\UserListener;

// get app level config
$config = Configure::read('MessagingCenter');
$config = $config ? $config : [];

// load default plugin config
Configure::load('MessagingCenter.messaging_center');

// overwrite default plugin config by app level config
Configure::write('MessagingCenter', array_replace_recursive(
    Configure::read('MessagingCenter'),
    $config
));

EventManager::instance()->on(new UserListener());
