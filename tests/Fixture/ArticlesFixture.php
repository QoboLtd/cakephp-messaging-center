<?php
namespace MessagingCenter\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ArticlesFixture
 *
 */
class ArticlesFixture extends TestFixture
{

    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'uuid'],
        'author' => ['type' => 'uuid', 'null' => false],
        'title' => ['type' => 'string', 'null' => false],
        'body' => 'text',
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'author' => '00000000-0000-0000-0000-000000000001',
            'title' => 'First Article',
            'body' => 'First Article Body'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'author' => '00000000-0000-0000-0000-000000000002',
            'title' => 'Second Article',
            'body' => 'Second Article Body'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000003',
            'author' => '00000000-0000-0000-0000-000000000001',
            'title' => 'Third Article',
            'body' => 'Third Article Body'
        ]
    ];
}
