<?php
namespace Qobo\MessagingCenter\Test\TestCase\Event\Model;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\MessagingCenter\Event\Model\UserListener;

class UserListenerTest extends TestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.users',
        'plugin.qobo/messaging_center.messages',
    ];

    public function setUp()
    {
        parent::setUp();

        Configure::write('Users.table', 'CakeDC/Users.Users');

        $this->Users = TableRegistry::get('CakeDC/Users.Users');

        // enable event tracking
        $this->Users->eventManager()->setEventList(new EventList());

        EventManager::instance()->on(new UserListener());
    }

    public function tearDown()
    {
        unset($this->Users);

        parent::tearDown();
    }

    public function testAfterSave()
    {
        $table = TableRegistry::get('Qobo/MessagingCenter.Messages');

        $expected = 1 + $table->find()->count();

        $data = [
            'username' => 'foobar',
            'password' => 'foobar',
        ];

        $entity = $this->Users->newEntity();
        $entity = $this->Users->patchEntity($entity, $data);

        // trigger event
        $result = $this->Users->save($entity);

        $this->assertEventFired('Model.afterSave', $this->Users->eventManager());

        $subject = 'Welcome to Project Name';
        $content = "\nDear foobar<br>\n<br>\n$subject\nBest regards,\nSYSTEM";

        $entity = $table->find()->limit(1)->where(['subject' => $subject])->first();
        $this->assertEquals($content, $entity->get('content'));

        $this->assertEquals($expected, $table->find()->count());
    }

    public function testAfterSaveNoWelcomeMessage()
    {
        Configure::write('MessagingCenter.welcomeMessage.enabled', false);

        $table = TableRegistry::get('Qobo/MessagingCenter.Messages');

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
