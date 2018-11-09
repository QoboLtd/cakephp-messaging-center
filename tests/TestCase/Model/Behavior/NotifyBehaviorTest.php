<?php
namespace MessagingCenter\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use MessagingCenter\Model\Behavior\NotifyBehavior;

class NotifyBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.MessagingCenter.articles',
        'plugin.CakeDC/Users.users',
    ];

    public function setUp()
    {
        parent::setUp();

        $this->Articles = TableRegistry::get('Articles', ['table' => 'articles']);
        $this->Articles->setDisplayField('title');
        $this->Articles->belongsTo('Users', [
            'foreignKey' => 'author',
            'className' => 'Users'
        ]);
        $this->Articles->addBehavior('MessagingCenter.Notify', [
            'ignoredFields' => ['id']
        ]);

        $this->Behavior = $this->Articles->behaviors()->Notify;
    }

    public function tearDown()
    {
        unset($this->Articles);
        unset($this->Behavior);

        parent::tearDown();
    }

    public function testInitialize()
    {
        $result = $this->Behavior->getConfig('ignoredFields');
        $expected = ['id'];
        $this->assertEquals($expected, $result);
    }

    public function testImplementedEvents()
    {
        $expected = [
            'Model.afterSave' => 'afterSave',
        ];

        $this->assertEquals($expected, $this->Behavior->implementedEvents());
    }

    public function testAfterSave()
    {
        $data = [
            'author' => '00000000-0000-0000-0000-000000000001',
            'title' => 'New Article',
            'body' => 'New Article Body'
        ];

        // triggers behavior
        $result = $this->Articles->save($this->Articles->newEntity($data));

        $expected = [
            'subject' => 'Article: New Article',
            'content' => 'Article record <a href="/articles/view/' . $result->id . '">New Article</a> has been assinged to you via \'Author\' field.' . "\n"
        ];

        $table = TableRegistry::get('MessagingCenter.Messages');
        $entity = $table->find()->limit(1)->where(['subject LIKE' => '%' . $data['title'] . '%'])->first();

        $this->assertEquals($expected['subject'], $entity->get('subject'));
        $this->assertEquals($expected['content'], $entity->get('content'));
    }

    public function testAfterSaveModified()
    {
        $data = [
            'title' => 'Modified Article',
            'body' => 'Modified Article Body'
        ];

        $entity = $this->Articles->get('00000000-0000-0000-0000-000000000001');
        $entity = $this->Articles->patchEntity($entity, $data);

        // triggers behavior
        $result = $this->Articles->save($entity);

        $expected = [
            'subject' => 'Article: Modified Article',
            'content' => 'Article <a href="/articles/view/' . $result->id . '">Modified Article</a> has been modified.' . "\n\n" . '* <strong>Title</strong>: changed from \'First Article\' to \'Modified Article\'.' . "\n" . '* <strong>Body</strong>: changed from \'First Article Body\' to \'Modified Article Body\'.' . "\n"
        ];

        $table = TableRegistry::get('MessagingCenter.Messages');
        $entity = $table->find()->limit(1)->where(['subject LIKE' => '%' . $data['title'] . '%'])->first();

        $this->assertEquals($expected['subject'], $entity->get('subject'));
        $this->assertEquals($expected['content'], $entity->get('content'));
    }
}
