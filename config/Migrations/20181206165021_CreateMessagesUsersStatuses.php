<?php
use Migrations\AbstractMigration;

class CreateMessagesUsersStatuses extends AbstractMigration
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
        $table = $this->table('qobo_messages_statuses');
        $table->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('message_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('user_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $tabe->addColumn('status', 'string', [
            'default' => null,
            'null' => false,
            'limit' => 255,
        ]);
        $table->addPrimaryKey([
            'id',
        ]);
        $table->create();
    }
}
