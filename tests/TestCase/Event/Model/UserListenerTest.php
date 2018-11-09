<?php
namespace MessagingCenter\Test\TestCase\Event\Model;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use MessagingCenter\Event\Model\UserListener;

class UserListenerTest extends TestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.users',
        'plugin.MessagingCenter.messages',
    ];

    /**
     * @var \CakeDC\Users\Model\Table\UsersTable $Users
     */
    protected $Users;

    public function setUp()
    {
        parent::setUp();

        Configure::write('Users.table', 'CakeDC/Users.Users');

        /**
         * @var \CakeDC\Users\Model\Table\UsersTable $table
         */
        $table = TableRegistry::get('CakeDC/Users.Users');
        $this->Users = $table;

        // enable event tracking
        $this->Users->getEventManager()->setEventList(new EventList());

        EventManager::instance()->on(new UserListener());
    }

    public function tearDown()
    {
        unset($this->Users);

        parent::tearDown();
    }

    public function testAfterSave(): void
    {
        $table = TableRegistry::get('MessagingCenter.Messages');

        $expected = 1 + $table->find()->count();

        $data = [
            'username' => 'foobar',
            'password' => 'foobar',
        ];

        $entity = $this->Users->newEntity();
        $entity = $this->Users->patchEntity($entity, $data);

        // trigger event
        $result = $this->Users->save($entity);

        $this->assertEventFired('Model.afterSave', $this->Users->getEventManager());

        $subject = 'Welcome to Project Name';
        $content = "\nDear foobar<br>\n<br>\n$subject\nBest regards,\nSYSTEM";

        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $table->find()->limit(1)->where(['subject' => $subject])->first();
        $this->assertEquals($content, $entity->get('content'));

        $this->assertEquals($expected, $table->find()->count());
    }

    public function testAfterSaveNoWelcomeMessage(): void
    {
        Configure::write('MessagingCenter.welcomeMessage.enabled', false);

        $table = TableRegistry::get('MessagingCenter.Messages');

        $expected = $table->find()->count();

        $data = [
            'username' => 'foobar',
            'password' => 'foobar',
        ];

        $entity = $this->Users->newEntity();
        $entity = $this->Users->patchEntity($entity, $data);

        // trigger event
        $result = $this->Users->save($entity);

        $this->assertEquals($expected, $table->find()->count());
    }
}
