<?php
use Cake\Event\EventManager;
use MessagingCenter\Event\Model\TranslationsAfterSaveEventsListener;

EventManager::instance()->on(new TranslationsAfterSaveEventsListener());
