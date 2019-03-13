<?php
namespace MessagingCenter\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MailboxesFixture
 *
 */
class MailboxesFixture extends TestFixture
{
    public $table = 'qobo_mailboxes';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'user_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'type' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'incoming_transport' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'incoming_settings' => ['type' => 'text', 'length' => 4294967295, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'outgoing_transport' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'outgoing_settings' => ['type' => 'text', 'length' => 4294967295, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
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
                'id' => '00000000-0000-0000-0000-000000000001',
                'user_id' => '3eb99f53-16c7-4046-a1bd-8d526bdc24aa',
                'name' => 'test@system',
                'type' => 'system',
                'incoming_transport' => 'internal',
                'incoming_settings' => 'default',
                'outgoing_transport' => 'internal',
                'outgoing_settings' => 'default',
                'active' => 1,
                'created' => '2019-01-07 20:34:29',
                'modified' => '2019-01-07 20:34:29'
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000002',
                'user_id' => '3eb99f53-16c7-4046-a1bd-8d526bdc24aa',
                'name' => 'My Test Email',
                'type' => 'email',
                'incoming_transport' => 'imap4',
                'incoming_settings' => '{"host":"imap.yandex.ru","port":"933","use_ssl":"1","no_validate_ssl_cert":"1","username":"test2019me@ya.ru","password":"XXXX"}',
                'outgoing_transport' => 'smtp',
                'outgoing_settings' => '{"host":"smtp.yandex.ru","port":"465","username":"test2019me@ya.ru","password":"XXXX"}',
                'active' => 1,
                'created' => '2019-01-07 20:34:29',
                'modified' => '2019-01-07 20:34:29'
            ],
        ];
        parent::init();
    }
}
