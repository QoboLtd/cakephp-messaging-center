<?php
use Migrations\AbstractMigration;

class CreateMessages extends AbstractMigration
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
        $table = $this->table('messages');
        $table->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('from', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('to', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('subject', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('content', 'text', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('date_sent', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('status', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('related_model', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('related_id', 'uuid', [
            'default' => null,
            'null' => true,
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
