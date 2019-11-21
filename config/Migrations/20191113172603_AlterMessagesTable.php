<?php
use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AlterMessagesTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $this->table('qobo_messages')
            ->changeColumn('folder_id', 'uuid', ['default' => null, 'null' => true])
            ->changeColumn('mime_type', 'string', ['default' => null, 'null' => true, 'limit' => 255])
            ->changeColumn('preview_content', 'text', ['default' => null, 'null' => true, 'limit' => MysqlAdapter::TEXT_LONG])
            ->changeColumn('raw_content', 'text', ['default' => null, 'null' => true, 'limit' => MysqlAdapter::TEXT_LONG])
            ->update();
    }

}
