<?php
use Migrations\AbstractMigration;

class AdjustMessagesTable extends AbstractMigration
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

        $table->addColumn('message_id', 'string', [
            'default' => null,
            'null' => true,
            'limit' => 255
        ]);

        $table->addColumn('from_name', 'string', [
            'default' => null,
            'null' => true,
            'limit' => 100
        ]);

        $table->changeColumn('from_user', 'string', [
            'default' => null,
            'null' => true,
            'limit' => 100
        ]);

        $table->changeColumn('to_user', 'string', [
            'default' => null,
            'null' => true,
            'limit' => 100
        ]);

        $table->update();
    }
}
