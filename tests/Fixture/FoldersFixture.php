<?php
namespace MessagingCenter\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FoldersFixture
 *
 */
class FoldersFixture extends TestFixture
{
    public $table = 'qobo_folders';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'mailbox_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'parent_id' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'type' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'order_no' => ['type' => 'integer', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'latin1_swedish_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Init method
     *
     * @return void
     */
    public function init()
    {
        $this->records = [
            [
                'id' => '8f49d83e-3690-496a-8448-9e9b08c0ef92',
                'mailbox_id' => '00000000-0000-0000-0000-000000000002',
                'parent_id' => '5d00d5d5-5873-44eb-85d6-f4736d2419dc',
                'name' => 'Sent',
                'type' => 'Lorem ipsum dolor sit amet',
                'created' => '2019-01-07 20:47:29',
                'modified' => '2019-01-07 20:47:29'
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000001',
                'mailbox_id' => '00000000-0000-0000-0000-000000000001',
                'parent_id' => '',
                'name' => 'Sent',
                'type' => 'default',
                'created' => '2019-01-07 20:47:29',
                'modified' => '2019-01-07 20:47:29',
                'order_no' => 10,
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000002',
                'mailbox_id' => '00000000-0000-0000-0000-000000000001',
                'parent_id' => '',
                'name' => 'Inbox',
                'type' => 'default',
                'created' => '2019-01-07 20:47:29',
                'modified' => '2019-01-07 20:47:29',
                'order_no' => 1,
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000003',
                'mailbox_id' => '00000000-0000-0000-0000-000000000002',
                'parent_id' => '',
                'name' => 'Inbox',
                'type' => 'default',
                'created' => '2019-01-07 20:47:29',
                'modified' => '2019-01-07 20:47:29'
            ],
        ];
        parent::init();
    }
}
