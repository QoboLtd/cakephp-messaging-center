<?php
use Cake\Core\Configure;

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

\Cake\Event\EventManager::instance()->on(new \MessagingCenter\Event\UserListener());
