<?php
namespace MessagingCenter\Test\TestCase\Model\Behavior;

use Cake\Core\Configure;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use MessagingCenter\Event\Model\MailboxListener;

class NotifyBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.MessagingCenter.Articles',
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * @var \MessagingCenter\Test\App\Model\Table\ArticlesTable
     */
    protected $Articles;

    /**
     * @var \MessagingCenter\Model\Behavior\NotifyBehavior $Behavior
     */
    protected $Behavior;

    public function setUp()
    {
        parent::setUp();

        /**
         * @var \MessagingCenter\Test\App\Model\Table\ArticlesTable $table
         */
        $table = TableRegistry::get('Articles', ['table' => 'articles']);
        $this->Articles = $table;
        $this->Articles->setDisplayField('title');
        $this->Articles->belongsTo('Users', [
            'foreignKey' => 'author',
            'className' => 'Users',
        ]);
        $this->Articles->addBehavior('MessagingCenter.Notify', [
            'ignoredFields' => ['id'],
        ]);

        /**
         * @var \MessagingCenter\Model\Behavior\NotifyBehavior $behavior
         */
        $behavior = $this->Articles->behaviors()->get('Notify');
        $this->Behavior = $behavior;

        EventManager::instance()->setEventList(new EventList());
        EventManager::instance()->on(new MailboxListener());
    }

    public function tearDown()
    {
        unset($this->Articles);
        unset($this->Behavior);

        parent::tearDown();
    }

    public function testInitialize(): void
    {
        $result = $this->Behavior->getConfig('ignoredFields');
        $expected = ['id'];
        $this->assertEquals($expected, $result);
    }

    public function testImplementedEvents(): void
    {
        $expected = [
            'Model.afterSave' => 'afterSave',
        ];

        $this->assertEquals($expected, $this->Behavior->implementedEvents());
    }

    public function testAfterSave(): void
    {
        $data = [
            'author' => '00000000-0000-0000-0000-000000000001',
            'title' => 'New Article',
            'body' => 'New Article Body',
        ];

        // triggers behavior
        /**
         * @var \MessagingCenter\Test\App\Model\Entity\Article $result
         */
        $result = $this->Articles->save($this->Articles->newEntity($data));

        $expected = [
            'subject' => 'Article: New Article',
            'content' => 'Article record <a href="/articles/view/' . $result->id . '">New Article</a> has been assinged to you via \'Author\' field.' . "\n",
            'from_user' => Configure::readOrFail('MessagingCenter.systemUser.id'),
            'sender' => Configure::readOrFail('MessagingCenter.systemUser.name'),
        ];

        $table = TableRegistry::get('MessagingCenter.Messages');
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $table->find()->limit(1)->where(['subject LIKE' => '%' . $data['title'] . '%'])->first();

        $this->assertEquals($expected['subject'], $entity->get('subject'));
        $this->assertEquals($expected['content'], $entity->get('content'));
        $this->assertEquals($expected['from_user'], $entity->get('from_user'));
        $this->assertEquals($expected['sender'], $entity->get('sender'));
    }

    public function testNotificationEvent(): void
    {
        $data = [
            'author' => '00000000-0000-0000-0000-000000000001',
            'title' => 'New Article',
            'body' => 'New Article Body',
        ];

        /**
         * @var \MessagingCenter\Test\App\Model\Entity\Article $result
         */
        $result = $this->Articles->save($this->Articles->newEntity($data));

        $this->assertEventFired('MessagingCenter.Notify.notificationReceived');
    }

    public function testAfterSaveModified(): void
    {
        $data = [
            'title' => 'Modified Article',
            'body' => 'Modified Article Body',
        ];

        $entity = $this->Articles->get('00000000-0000-0000-0000-000000000001');
        $entity = $this->Articles->patchEntity($entity, $data);

        // triggers behavior
        /**
         * @var \MessagingCenter\Test\App\Model\Entity\Article
         */
        $result = $this->Articles->save($entity);

        $expected = [
            'subject' => 'Article: Modified Article',
            'content' => 'Article <a href="/articles/view/' . $result->id . '">Modified Article</a> has been modified.' . "\n\n" . '* <strong>Title</strong>: changed from \'First Article\' to \'Modified Article\'.' . "\n" . '* <strong>Body</strong>: changed from \'First Article Body\' to \'Modified Article Body\'.' . "\n",
        ];

        $table = TableRegistry::get('MessagingCenter.Messages');
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $table->find()->limit(1)->where(['subject LIKE' => '%' . $data['title'] . '%'])->first();
        $this->assertFalse(empty($entity), "Failed to fetch first message");

        $this->assertEquals($expected['subject'], $entity->get('subject'));
        $this->assertEquals($expected['content'], $entity->get('content'));
    }
}
