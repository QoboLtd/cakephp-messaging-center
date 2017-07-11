<?php
namespace MessagingCenter\Events;

use App\Model\Table\UsersTable;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;
use MessagingCenter\Notifier\MessageNotifier;

class UserListener implements EventListenerInterface
{
    use EventDispatcherTrait;

    /**
     * Notifier instance.
     *
     * @var \MessagingCenter\Notifier\Notifier
     */
    protected $Notifier = null;

    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            'Model.afterSave' => 'afterSave'
        ];
    }

    /**
     * After save event.
     *
     * @param Event $event event.
     * @param EntityInterface $entity entity.
     * @param \ArrayObject $options options.
     */
    public function afterSave(Event $event, EntityInterface $entity, \ArrayObject $options)
    {
        if (!$event->subject() instanceof UsersTable) {
            return;
        }

        if (! Configure::readOrFail('MessagingCenter.welcomeMessage.enabled')) {
            return;
        }

        try {
            $this->Notifier = new MessageNotifier();
            $this->Notifier->from(Configure::readOrFail('MessagingCenter.systemUser.id'));
            $this->Notifier->to($entity->id);

            $data = [
                'username' => $entity->username,
                'projectName' => Configure::readOrFail('MessagingCenter.welcomeMessage.projectName'),
                'adminName' => Configure::readOrFail('MessagingCenter.systemUser.name'),
            ];
            $this->Notifier->template('MessagingCenter.welcome');

            // broadcast event for modifying message data before passing them to the Notifier
            $event = new Event('MessagingCenter.Notify.beforeRender', $this, [
                'table' => TableRegistry::get('Messages'),
                'entity' => $entity,
                'data' => $data
            ]);
            $this->eventManager()->dispatch($event);
            $data = !empty($event->result) ? $event->result : $data;

            $subject = Configure::readOrFail('MessagingCenter.welcomeMessage.subject');

            if (empty($subject)) {
                $subject = 'Welcome to' . Configure::readOrFail('MessagingCenter.welcomeMessage.projectName');
            }

            $this->Notifier->subject($subject);
            $this->Notifier->message($data);

            $this->Notifier->send();
        } catch (\Exception $ex) {
            print_r($ex);
        }
    }
}
