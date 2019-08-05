<?php
namespace MessagingCenter\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MessagesFixture
 *
 */
class MessagesFixture extends TestFixture
{
    public $table = 'qobo_messages';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'from_user' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'to_user' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'subject' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'content' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'date_sent' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'status' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'related_model' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'related_id' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'folder_id' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'headers' => ['type' => 'json', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'from_user' => '00000000-0000-0000-0000-000000000001',
            'to_user' => '00000000-0000-0000-0000-000000000002',
            'subject' => 'Lorem ipsum dolor sit amet',
            'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'date_sent' => '2016-03-16 10:46:23',
            'status' => 'new',
            'related_model' => 'Lorem ipsum dolor sit amet',
            'related_id' => 'df3011fc-a45d-4081-9ffe-25aeaaf73789',
            'created' => '2016-03-16 10:46:23',
            'modified' => '2016-03-16 10:46:23',
            'folder_id' => '00000000-0000-0000-0000-000000000002',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'from_user' => '00000000-0000-0000-0000-000000000001',
            'to_user' => '00000000-0000-0000-0000-000000000002',
            'subject' => 'Lorem ipsum dolor sit amet',
            'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'date_sent' => '2016-03-16 10:46:23',
            'status' => 'deleted',
            'related_model' => 'Lorem ipsum dolor sit amet',
            'related_id' => 'df3011fc-a45d-4081-9ffe-25aeaaf73789',
            'created' => '2016-03-16 10:46:23',
            'modified' => '2016-03-16 10:46:23',
            'folder_id' => '00000000-0000-0000-0000-000000000002',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000003',
            'from_user' => '00000000-0000-0000-0000-000000000001',
            'to_user' => '00000000-0000-0000-0000-000000000002',
            'subject' => 'Lorem ipsum dolor sit amet',
            'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'date_sent' => '2016-03-16 10:46:23',
            'status' => 'archived',
            'related_model' => 'Lorem ipsum dolor sit amet',
            'related_id' => 'df3011fc-a45d-4081-9ffe-25aeaaf73789',
            'created' => '2016-03-16 10:46:23',
            'modified' => '2016-03-16 10:46:23',
            'folder_id' => '00000000-0000-0000-0000-000000000002',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000004',
            'from_user' => '00000000-0000-0000-0000-000000000001',
            'to_user' => '00000000-0000-0000-0000-000000000003',
            'subject' => 'Lorem ipsum dolor sit amet',
            'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'date_sent' => '2016-03-16 10:46:23',
            'status' => 'new',
            'related_model' => 'Lorem ipsum dolor sit amet',
            'related_id' => 'df3011fc-a45d-4081-9ffe-25aeaaf73789',
            'created' => '2016-03-16 10:46:23',
            'modified' => '2016-03-16 10:46:23',
            'folder_id' => '00000000-0000-0000-0000-000000000002',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000005',
            'from_user' => '00000000-0000-0000-0000-000000000000',
            'to_user' => '00000000-0000-0000-0000-000000000003',
            'subject' => 'Lorem ipsum dolor sit amet',
            'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'date_sent' => '2016-03-16 10:46:23',
            'status' => 'new',
            'related_model' => 'Lorem ipsum dolor sit amet',
            'related_id' => 'df3011fc-a45d-4081-9ffe-25aeaaf73789',
            'created' => '2016-03-16 10:46:23',
            'modified' => '2016-03-16 10:46:23',
            'folder_id' => '00000000-0000-0000-0000-000000000002',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000006',
            'from_user' => '00000000-0000-0000-0000-000000000000',
            'to_user' => '00000000-0000-0000-0000-000000000003',
            'subject' => 'Lorem ipsum dolor sit amet',
            'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'date_sent' => '2016-03-16 10:46:23',
            'status' => 'new',
            'related_model' => 'Lorem ipsum dolor sit amet',
            'related_id' => 'df3011fc-a45d-4081-9ffe-25aeaaf73789',
            'created' => '2016-03-16 10:46:23',
            'modified' => '2016-03-16 10:46:23',
            'folder_id' => '00000000-0000-0000-0000-000000000001',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000007',
            'from_user' => '',
            'to_user' => '',
            'subject' => 'Lorem ipsum dolor sit amet',
            'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'date_sent' => '2016-03-16 10:46:23',
            'status' => 'new',
            'related_model' => '',
            'related_id' => '',
            'created' => '2016-03-16 10:46:23',
            'modified' => '2016-03-16 10:46:23',
            'folder_id' => '00000000-0000-0000-0000-000000000003',
            'headers1' => '{"toaddress":"foo@bar.com","to":[{"mailbox":"to","host":"bar.com"}],"fromaddress":"Test2019","from":[{"personal":"Test2019","mailbox":"test2019me","host":"ya.ru"}]}',
            'headers' => [
                'to' => [
                    0 => [
                        'host' => 'bar1.com',
                        'mailbox' => 'to',
                    ],
                ],
                'Date' => 'Thu, 1 Aug 2019 16:08:44 +0300',
                'date' => 'Thu, 1 Aug 2019 16:08:44 +0300',
                'from' => [
                    0 => [
                        'host' => 'ya.ru',
                        'mailbox' => 'test2019me',
                        'personal' => 'Test2019',
                    ],
                ],
                'toaddress' => 'foo@bar.com',
                'fromaddress' => 'Test2019'
            ]
        ],
    ];
}
