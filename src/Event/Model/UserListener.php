<?php
namespace MessagingCenter\Event\Model;

use App\Model\Table\UsersTable;
use ArrayObject;
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
     * @param \Cake\Event\Event $event event.
     * @param \Cake\Datasource\EntityInterface $entity entity.
     * @param \ArrayObject $options options.
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        if (!$event->subject() instanceof UsersTable) {
            return;
        }

        if (!Configure::read('MessagingCenter.welcomeMessage.enabled')) {
            return;
        }

        $this->Notifier = new MessageNotifier();
        $this->Notifier->from(Configure::readOrFail('MessagingCenter.systemUser.id'));
        $this->Notifier->to($entity->id);

        $projectName = Configure::read('MessagingCenter.welcomeMessage.projectName');

        $subject = 'Welcome';
        if (!empty($projectName)) {
            $subject .= ' to ' . $projectName;
        }

        $data = [
            'username' => $entity->username,
            'projectName' => $projectName,
            'subject' => $subject,
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

        $this->Notifier->subject($subject);
        $this->Notifier->message($data);

        $this->Notifier->send();
    }
}
