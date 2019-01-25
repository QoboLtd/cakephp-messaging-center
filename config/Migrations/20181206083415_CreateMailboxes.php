<?php
use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateMailboxes extends AbstractMigration
{
    public $autoId = false;

    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('qobo_mailboxes');
        $table->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('user_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('name', 'string', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('type', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('incoming_transport', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('incoming_settings', 'text', [
            'default' => null,
            'limit' => MysqlAdapter::TEXT_LONG,
            'null' => false,
        ]);
        $table->addColumn('outgoing_transport', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('outgoing_settings', 'text', [
            'default' => null,
            'limit' => MysqlAdapter::TEXT_LONG,
            'null' => false,
        ]);
        $table->addColumn('active', 'boolean', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addPrimaryKey([
            'id',
        ]);
        $table->create();
    }
}
