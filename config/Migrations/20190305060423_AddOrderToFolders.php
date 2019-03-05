<?php
use Migrations\AbstractMigration;

class AddOrderToFolders extends AbstractMigration
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
        $table = $this->table('qobo_folders');
        $table->addColumn('order_no', 'integer', [
            'default' => 0,
            'null' => false
        ]);
        $table->update();
    }
}
