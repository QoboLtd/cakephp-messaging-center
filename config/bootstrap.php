<?php
use Cake\Core\Configure;

// get app level config
$config = Configure::read('MessagingCenter');

// load default plugin config
Configure::load('MessagingCenter.messaging_center');

// overwrite default plugin config by app level config
Configure::write('MessagingCenter', array_replace_recursive(
    Configure::read('MessagingCenter'),
    $config
));
