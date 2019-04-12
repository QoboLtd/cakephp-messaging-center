<?php
namespace MessagingCenter\Test\TestCase\Controller;

use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use MessagingCenter\Controller\MessagesController;
use MessagingCenter\Model\Entity\Message;
use MessagingCenter\Model\Table\MessagesTable;

/**
 * MessagingCenter\Controller\MessagesController Test Case
 */
class MessagesControllerTest extends IntegrationTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.users',
        'plugin.messaging_center.messages',
        'plugin.messaging_center.folders',
        'plugin.messaging_center.mailboxes',
    ];

    /**
     * @var \MessagingCenter\Model\Table\MessagesTable $MessagesTable
     */
    protected $MessagesTable;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        /**
         * @var \MessagingCenter\Model\Table\MessagesTable $table
         */
        $table = TableRegistry::get('MessagingCenter.Messages');
        $this->MessagesTable = $table;

        $this->enableRetainFlashMessages();
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->MessagesTable);

        parent::tearDown();
    }

    public function testViewInboxMessage(): void
    {
        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000001');

        $this->assertResponseOk();
        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
    }

    public function testViewSentMessage(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000002');

        $this->assertResponseOk();
        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
    }

    public function testViewDeletedMessage(): void
    {
        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000002');

        $this->assertResponseOk();
        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
    }

    public function testViewArchivedMessage(): void
    {
        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000003');

        $this->assertResponseOk();
        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
    }

    public function testViewForbidden(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000001']);
        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000004');

        $this->assertResponseCode(403);
    }

    public function testCompose(): void
    {
        $this->get('/messaging-center/messages/compose/00000000-0000-0000-0000-000000000002');

        $this->assertResponseOk();

        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
        $this->assertTrue($this->viewVariable('message')->isNew());
    }

    public function testComposePost(): void
    {
        $mailboxId = '00000000-0000-0000-0000-000000000002';
        $expected = 1 + $this->MessagesTable->find('all')->count();

        $data = [
            'to_user' => '00000000-0000-0000-0000-000000000001',
            'subject' => 'testComposePost message',
            'content' => 'Bla bla bla'
        ];
        $this->post('/messaging-center/messages/compose/' . $mailboxId, $data);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            $mailboxId
        ];
        $this->assertRedirect($url);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->limit(1)->where(['subject' => $data['subject']]);
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $query->first();
        $this->assertEquals($data['to_user'], $entity->to_user);
        $this->assertEquals($data['content'], $entity->content);
        /**
         * @var \Cake\Http\Session $session
         */
        $session = $this->_requestSession;
        $this->assertEquals($session->read('Auth.User.id'), $entity->from_user);
        $this->assertEquals('new', $entity->status);
        $time = new Time();
        $this->assertEquals($time->i18nFormat(), $entity->date_sent->i18nFormat());
    }

    public function testComposePostNoData(): void
    {
        $expected = $this->MessagesTable->find('all')->count();
        $this->post('/messaging-center/messages/compose/00000000-0000-0000-0000-000000000002');

        $this->assertResponseOk();
        $this->assertSession('The message could not be sent. Please, try again.', 'Flash.flash.0.message');
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());
    }

    public function testComposePostEnforceData(): void
    {
        $expected = 1 + $this->MessagesTable->find('all')->count();

        $data = [
            'to_user' => '00000000-0000-0000-0000-000000000001',
            'subject' => 'testComposePost message',
            'content' => 'Bla bla bla',
            // try to enforce protected data
            'from_user' => 'Enforce other user id',
            'status' => 'Enforce custom message status',
            'date_sent' => 'Enforce custom date sent',
        ];
        $this->post('/messaging-center/messages/compose/00000000-0000-0000-0000-000000000002', $data);

        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->limit(1)->where(['subject' => $data['subject']]);
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $query->first();
        $this->assertNotEquals($data['from_user'], $entity->from_user);
        $this->assertNotEquals($data['status'], $entity->status);
        $this->assertNotEquals($data['date_sent'], $entity->date_sent);
    }

    public function testReply(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $this->get('/messaging-center/messages/reply/' . $id);

        $this->assertResponseOk();

        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
        $this->assertFalse($this->viewVariable('message')->isNew());
    }

    public function testReplyPut(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $mailboxId = '00000000-0000-0000-0000-000000000001';
        $entity = $this->MessagesTable->get($id);

        $expected = 1 + $this->MessagesTable->find('all')->count();

        $data = [
            'subject' => 'testReplyPut message',
            'content' => 'Bla bla bla'
        ];
        $this->put('/messaging-center/messages/reply/' . $id, $data);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            $mailboxId
        ];
        $this->assertRedirect($url);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->limit(1)->where(['subject' => $data['subject']]);
        /**
         * @var \MessagingCenter\Model\Entity\Message $newEntity
         */
        $newEntity = $query->first();
        $this->assertEquals($entity->get('from_user'), $newEntity->to_user);
        $this->assertEquals($data['content'], $newEntity->content);
        /**
         * @var \Cake\Http\Session $session
         */
        $session = $this->_requestSession;
        $this->assertEquals($session->read('Auth.User.id'), $newEntity->from_user);
        $this->assertEquals('new', $newEntity->status);
        $time = new Time();
        $this->assertEquals($time->i18nFormat(), $newEntity->date_sent->i18nFormat());
        // verify existing message was not affected.
        $this->assertEquals($entity->toArray(), $this->MessagesTable->get($id)->toArray());
    }

    public function testReplyPutSameUser(): void
    {
        $this->markTestSkipped('Skip this test till refactoring will be completed');

        $id = '00000000-0000-0000-0000-000000000001';
        $mailboxId = '00000000-0000-0000-0000-000000000001';

        $expected = $this->MessagesTable->find('all')->count();

        $data = [
            'subject' => 'testReplyPut message',
            'content' => 'Bla bla bla'
        ];
        $this->put('/messaging-center/messages/reply/' . $id, $data);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            $mailboxId
        ];
        $this->assertRedirect($url);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());
    }

    public function testReplyPutNoData(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $expected = $this->MessagesTable->find('all')->count();
        $this->put('/messaging-center/messages/reply/' . $id);

        $this->assertResponseOk();
        $this->assertSession('The message could not be sent. Please, try again.', 'Flash.flash.0.message');
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());
    }

    public function testReplyPutEnforceData(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $mailboxId = '00000000-0000-0000-0000-000000000001';
        $expected = 1 + $this->MessagesTable->find('all')->count();

        $data = [
            'subject' => 'testComposePost message',
            'content' => 'Bla bla bla',
            // try to enforce protected data
            'to_user' => 'Enforce other user id',
            'from_user' => 'Enforce other user id',
            'status' => 'Enforce custom message status',
            'date_sent' => 'Enforce custom date sent',
        ];
        $this->put('/messaging-center/messages/reply/' . $id, $data);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            $mailboxId
        ];
        $this->assertRedirect($url);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->limit(1)->where(['subject' => $data['subject']]);
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $query->first();
        $this->assertNotEquals($data['to_user'], $entity->to_user);
        $this->assertNotEquals($data['from_user'], $entity->from_user);
        $this->assertNotEquals($data['status'], $entity->status);
        $this->assertNotEquals($data['date_sent'], $entity->date_sent);
    }

    public function testDelete(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $this->delete('/messaging-center/messages/delete/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Messages',
            'action' => 'folder'
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('deleted', $entity->get('status'));
    }

    public function testDeleteAlreadyDeleted(): void
    {
        $id = '00000000-0000-0000-0000-000000000002';

        $this->delete('/messaging-center/messages/delete/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Messages',
            'action' => 'view',
            $id
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('deleted', $entity->get('status'));
    }

    public function testDeleteUserSent(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000001']);

        $id = '00000000-0000-0000-0000-000000000001';

        $this->delete('/messaging-center/messages/delete/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Messages',
            'action' => 'view',
            $id
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('new', $entity->get('status'));
    }

    public function testArchive(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $this->post('/messaging-center/messages/archive/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Messages',
            'action' => 'folder'
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('archived', $entity->get('status'));
    }

    public function testArchiveAlreadyArchived(): void
    {
        $id = '00000000-0000-0000-0000-000000000003';

        $this->post('/messaging-center/messages/archive/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Messages',
            'action' => 'view',
            $id
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('archived', $entity->get('status'));
    }

    public function testArchiveUserSent(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000001']);

        $id = '00000000-0000-0000-0000-000000000001';

        $this->post('/messaging-center/messages/archive/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Messages',
            'action' => 'view',
            $id
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('new', $entity->get('status'));
    }

    public function testRestoreDeleted(): void
    {
        $id = '00000000-0000-0000-0000-000000000002';

        $this->post('/messaging-center/messages/restore/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Messages',
            'action' => 'folder'
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertNotEquals('archived', $entity->get('status'));
    }

    public function testRestoreArchived(): void
    {
        $id = '00000000-0000-0000-0000-000000000003';

        $this->post('/messaging-center/messages/restore/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Messages',
            'action' => 'folder'
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertNotEquals('archived', $entity->get('status'));
    }

    public function testRestoreNotArchived(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $this->post('/messaging-center/messages/restore/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Messages',
            'action' => 'view',
            $id
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('new', $entity->get('status'));
    }

    public function testRestoreUserSent(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000001']);

        $id = '00000000-0000-0000-0000-000000000001';

        $this->post('/messaging-center/messages/restore/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Messages',
            'action' => 'view',
            $id
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        /**
         * @var \Cake\Http\Session $session
         */
        $session = $this->_requestSession;
        $this->assertEquals($session->read('Auth.User.id'), $entity->get('from_user'));
        $this->assertEquals('new', $entity->get('status'));
    }
}
