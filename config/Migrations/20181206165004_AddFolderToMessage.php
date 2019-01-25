<?php
use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddFolderToMessage extends AbstractMigration
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
        $table = $this->table('qobo_messages');
        $table->addColumn('folder_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('mime_type', 'string', [
            'default' => null,
            'null' => false,
            'limit' => 255,
        ]);
        $table->addColumn('raw_content', 'text', [
            'default' => null,
            'null' => false,
            'limit' => MysqlAdapter::TEXT_LONG,
        ]);
        $table->addColumn('preview_content', 'text', [
            'default' => null,
            'null' => false,
            'limit' => MysqlAdapter::TEXT_LONG,
        ]);
        $table->update();
    }
}
