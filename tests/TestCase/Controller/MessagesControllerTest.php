<?php
namespace Qobo\MessagingCenter\Test\TestCase\Controller;

use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use Qobo\MessagingCenter\Controller\MessagesController;
use Qobo\MessagingCenter\Model\Entity\Message;
use Qobo\MessagingCenter\Model\Table\MessagesTable;

/**
 * \Qobo\MessagingCenter\Controller\MessagesController Test Case
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
        'plugin.qobo/messaging_center.messages'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->MessagesTable = TableRegistry::get('Qobo/MessagingCenter.Messages');

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

    public function testFolder()
    {
        $this->get('/messaging-center/messages/folder');

        $this->assertResponseOk();

        $this->assertEquals('inbox', $this->viewVariable('folder'));
        $this->assertEquals(1, $this->viewVariable('messages')->count());
    }

    public function testFolderInbox()
    {
        $this->get('/messaging-center/messages/folder/inbox');

        $this->assertResponseOk();

        $this->assertEquals('inbox', $this->viewVariable('folder'));
        $this->assertEquals(1, $this->viewVariable('messages')->count());
    }

    public function testFolderArchived()
    {
        $this->get('/messaging-center/messages/folder/archived');

        $this->assertResponseOk();

        $this->assertEquals('archived', $this->viewVariable('folder'));
        $this->assertEquals(1, $this->viewVariable('messages')->count());
    }

    public function testFolderSent()
    {
        $this->get('/messaging-center/messages/folder/sent');

        $this->assertResponseOk();

        $this->assertEquals('sent', $this->viewVariable('folder'));
        $this->assertTrue($this->viewVariable('messages')->isEmpty());
    }

    public function testFolderTrash()
    {
        $this->get('/messaging-center/messages/folder/trash');

        $this->assertResponseOk();

        $this->assertEquals('trash', $this->viewVariable('folder'));
        $this->assertEquals(1, $this->viewVariable('messages')->count());
    }

    public function testViewInboxMessage()
    {
        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000001');

        $this->assertResponseOk();
        $this->assertEquals('inbox', $this->viewVariable('folder'));
        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
    }

    public function testViewSentMessage()
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000001']);

        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000001');

        $this->assertResponseOk();
        $this->assertEquals('sent', $this->viewVariable('folder'));
        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
    }

    public function testViewDeletedMessage()
    {
        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000002');

        $this->assertResponseOk();
        $this->assertEquals('trash', $this->viewVariable('folder'));
        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
    }

    public function testViewArchivedMessage()
    {
        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000003');

        $this->assertResponseOk();
        $this->assertEquals('archived', $this->viewVariable('folder'));
        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
    }

    public function testViewForbidden()
    {
        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000004');

        $this->assertResponseCode(403);
    }

    public function testCompose()
    {
        $this->get('/messaging-center/messages/compose');

        $this->assertResponseOk();

        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
        $this->assertTrue($this->viewVariable('message')->isNew());
    }

    public function testComposePost()
    {
        $expected = 1 + $this->MessagesTable->find('all')->count();

        $data = [
            'to_user' => '00000000-0000-0000-0000-000000000001',
            'subject' => 'testComposePost message',
            'content' => 'Bla bla bla'
        ];
        $this->post('/messaging-center/messages/compose', $data);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'folder'
        ];
        $this->assertRedirect($url);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->limit(1)->where(['subject' => $data['subject']]);
        $entity = $query->first();
        $this->assertEquals($data['to_user'], $entity->to_user);
        $this->assertEquals($data['content'], $entity->content);
        $this->assertEquals($this->_requestSession->read('Auth.User.id'), $entity->from_user);
        $this->assertEquals('new', $entity->status);
        $time = new Time();
        $this->assertEquals($time->i18nFormat(), $entity->date_sent->i18nFormat());
    }

    public function testComposePostNoData()
    {
        $expected = $this->MessagesTable->find('all')->count();
        $this->post('/messaging-center/messages/compose');

        $this->assertResponseOk();
        $this->assertSession('The message could not be sent. Please, try again.', 'Flash.flash.0.message');
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());
    }

    public function testComposePostEnforceData()
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
        $this->post('/messaging-center/messages/compose', $data);

        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->limit(1)->where(['subject' => $data['subject']]);
        $entity = $query->first();
        $this->assertNotEquals($data['from_user'], $entity->from_user);
        $this->assertNotEquals($data['status'], $entity->status);
        $this->assertNotEquals($data['date_sent'], $entity->date_sent);
    }

    public function testReply()
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $this->get('/messaging-center/messages/reply/' . $id);

        $this->assertResponseOk();

        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
        $this->assertFalse($this->viewVariable('message')->isNew());
    }

    public function testReplyPut()
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $entity = $this->MessagesTable->get($id);

        $expected = 1 + $this->MessagesTable->find('all')->count();

        $data = [
            'subject' => 'testReplyPut message',
            'content' => 'Bla bla bla'
        ];
        $this->put('/messaging-center/messages/reply/' . $id, $data);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'folder'
        ];
        $this->assertRedirect($url);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->limit(1)->where(['subject' => $data['subject']]);
        $newEntity = $query->first();
        $this->assertEquals($entity->get('from_user'), $newEntity->to_user);
        $this->assertEquals($data['content'], $newEntity->content);
        $this->assertEquals($this->_requestSession->read('Auth.User.id'), $newEntity->from_user);
        $this->assertEquals('new', $newEntity->status);
        $time = new Time();
        $this->assertEquals($time->i18nFormat(), $newEntity->date_sent->i18nFormat());
        // verify existing message was not affected.
        $this->assertEquals($entity->toArray(), $this->MessagesTable->get($id)->toArray());
    }

    public function testReplyPutSameUser()
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000001']);

        $id = '00000000-0000-0000-0000-000000000001';

        $expected = $this->MessagesTable->find('all')->count();

        $data = [
            'subject' => 'testReplyPut message',
            'content' => 'Bla bla bla'
        ];
        $this->put('/messaging-center/messages/reply/' . $id, $data);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'view',
            $id
        ];
        $this->assertRedirect($url);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());
    }

    public function testReplyPutNoData()
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $expected = $this->MessagesTable->find('all')->count();
        $this->put('/messaging-center/messages/reply/' . $id);

        $this->assertResponseOk();
        $this->assertSession('The message could not be sent. Please, try again.', 'Flash.flash.0.message');
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());
    }

    public function testReplyPutEnforceData()
    {
        $id = '00000000-0000-0000-0000-000000000001';
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
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'folder'
        ];
        $this->assertRedirect($url);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->limit(1)->where(['subject' => $data['subject']]);
        $entity = $query->first();
        $this->assertNotEquals($data['to_user'], $entity->to_user);
        $this->assertNotEquals($data['from_user'], $entity->from_user);
        $this->assertNotEquals($data['status'], $entity->status);
        $this->assertNotEquals($data['date_sent'], $entity->date_sent);
    }

    public function testDelete()
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $this->delete('/messaging-center/messages/delete/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'folder'
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('deleted', $entity->get('status'));
    }

    public function testDeleteAlreadyDeleted()
    {
        $id = '00000000-0000-0000-0000-000000000002';

        $this->delete('/messaging-center/messages/delete/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'view',
            $id
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('deleted', $entity->get('status'));
    }

    public function testDeleteUserSent()
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000001']);

        $id = '00000000-0000-0000-0000-000000000001';

        $this->delete('/messaging-center/messages/delete/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'view',
            $id
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('new', $entity->get('status'));
    }

    public function testArchive()
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $this->post('/messaging-center/messages/archive/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'folder'
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('archived', $entity->get('status'));
    }

    public function testArchiveAlreadyArchived()
    {
        $id = '00000000-0000-0000-0000-000000000003';

        $this->post('/messaging-center/messages/archive/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'view',
            $id
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('archived', $entity->get('status'));
    }

    public function testArchiveUserSent()
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000001']);

        $id = '00000000-0000-0000-0000-000000000001';

        $this->post('/messaging-center/messages/archive/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'view',
            $id
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('new', $entity->get('status'));
    }

    public function testRestoreDeleted()
    {
        $id = '00000000-0000-0000-0000-000000000002';

        $this->post('/messaging-center/messages/restore/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'folder'
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertNotEquals('archived', $entity->get('status'));
    }

    public function testRestoreArchived()
    {
        $id = '00000000-0000-0000-0000-000000000003';

        $this->post('/messaging-center/messages/restore/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'folder'
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertNotEquals('archived', $entity->get('status'));
    }

    public function testRestoreNotArchived()
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $this->post('/messaging-center/messages/restore/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'view',
            $id
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals('new', $entity->get('status'));
    }

    public function testRestoreUserSent()
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000001']);

        $id = '00000000-0000-0000-0000-000000000001';

        $this->post('/messaging-center/messages/restore/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'Qobo/MessagingCenter',
            'controller' => 'Messages',
            'action' => 'view',
            $id
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals($this->_requestSession->read('Auth.User.id'), $entity->get('from_user'));
        $this->assertEquals('new', $entity->get('status'));
    }
}
