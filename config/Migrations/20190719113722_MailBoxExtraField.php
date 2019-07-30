<?php
use Migrations\AbstractMigration;

class MailBoxExtraField extends AbstractMigration
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
        $table = $this->table('qobo_mailboxes');
        $table->addColumn('default_folder', 'string', [
            'default' => '',
            'null' => true,
            'limit' => 255
        ]);

        $table->update();
    }
}
