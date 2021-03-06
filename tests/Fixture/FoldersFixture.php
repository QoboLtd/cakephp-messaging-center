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
        'name' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'type' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'icon' => ['type' => 'string', 'length' => 50, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'order_no' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci'
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
                'id' => '00000000-0000-0000-0000-000000000001',
                'mailbox_id' => '00000000-0000-0000-0000-000000000001',
                'parent_id' => '',
                'name' => 'Sent',
                'type' => 'default',
                'order_no' => 10,
                'created' => '2019-01-07 20:47:29',
                'modified' => '2019-01-07 20:47:29',
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000002',
                'mailbox_id' => '00000000-0000-0000-0000-000000000001',
                'parent_id' => '',
                'name' => 'Inbox',
                'type' => 'default',
                'order_no' => 1,
                'created' => '2019-01-07 20:47:29',
                'modified' => '2019-01-07 20:47:29',
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000003',
                'mailbox_id' => '00000000-0000-0000-0000-000000000002',
                'parent_id' => '',
                'name' => 'Inbox',
                'type' => 'default',
                'order_no' => 2,
                'created' => '2019-01-07 20:47:29',
                'modified' => '2019-01-07 20:47:29',
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000004',
                'mailbox_id' => '00000000-0000-0000-0000-000000000003',
                'parent_id' => '',
                'name' => 'Inbox',
                'type' => 'default',
                'order_no' => 3,
                'created' => '2019-01-07 20:47:29',
                'modified' => '2019-01-07 20:47:29',
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000005',
                'mailbox_id' => '00000000-0000-0000-0000-000000000002',
                'parent_id' => '',
                'name' => 'Sent',
                'type' => 'default',
                'order_no' => 11,
                'created' => '2019-01-07 20:47:29',
                'modified' => '2019-01-07 20:47:29',
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000006',
                'mailbox_id' => '00000000-0000-0000-0000-000000000002',
                'parent_id' => '',
                'name' => 'Trash',
                'type' => 'default',
                'order_no' => 13,
                'created' => '2019-01-07 20:47:29',
                'modified' => '2019-01-07 20:47:29',
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000007',
                'mailbox_id' => '00000000-0000-0000-0000-000000000002',
                'parent_id' => '',
                'name' => 'Archive',
                'type' => 'default',
                'order_no' => 12,
                'created' => '2019-01-07 20:47:29',
                'modified' => '2019-01-07 20:47:29',
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000008',
                'mailbox_id' => '00000000-0000-0000-0000-000000000001',
                'parent_id' => '',
                'name' => 'Trash',
                'type' => 'default',
                'order_no' => 23,
                'created' => '2019-10-16 16:14:29',
                'modified' => '2019-10-16 16:14:29',
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000009',
                'mailbox_id' => '00000000-0000-0000-0000-000000000001',
                'parent_id' => '',
                'name' => 'Archive',
                'type' => 'default',
                'order_no' => 17,
                'created' => '2019-10-16 16:28:29',
                'modified' => '2019-10-16 16:28:29',
            ],
        ];
        parent::init();
    }
}
