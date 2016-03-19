<?php
namespace MessagingCenter\Event\Model;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;

class TranslationsAfterSaveEventsListener implements EventListenerInterface
{
    const FIELD_TRANSLATED_BY = 'translated_by';

    /**
     * messages array
     * @var array
     */
    protected $_messages = [];

    /**
     * html markup
     */
    const HTML_LINK = '<a href="/translations/view/{{id}}">{{id}}</a>';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_messages['subject'] = __('Translation record');
        $this->_messages['content'] = __('Translation record %s has been assigned to you.');
    }

    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Model.Translations.Field.' . static::FIELD_TRANSLATED_BY . '.afterSave' => 'notifyTranslatedByUser',
        ];
    }

    /**
     * Notify user when translation document is assigned to him
     * @param  Cake\Event\Event $event Event object
     * @param  Cake\Datasource\EntityInterface $entity Translation entity
     * @param  ArrayObject $options entity options
     * @return void
     */
    public function notifyTranslatedByUser(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        $messages = TableRegistry::get('MessagingCenter.Messages');
        $data['to_user'] = $entity->{static::FIELD_TRANSLATED_BY};

        if (!is_null($data['to_user'])) {
            $users = TableRegistry::get('Users');
            $query = $users->find('all', [
                'conditions' => ['username' => 'SYSTEM'],
                'limit' => 1
            ]);
            $data['from_user'] = $query->first()->id;
            $data['status'] = $messages->getNewStatus();
            $data['date_sent'] = $messages->getDateSent();
            $data['subject'] = $this->_messages['subject'];
            $data['content'] = sprintf($this->_messages['content'], str_replace('{{id}}', $entity->id, static::HTML_LINK));

            $message = $messages->newEntity();
            $message = $messages->patchEntity($message, $data);

            $messages->save($message);
        }
    }
}
