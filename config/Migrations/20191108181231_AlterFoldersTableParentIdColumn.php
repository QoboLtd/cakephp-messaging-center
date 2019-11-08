<?php
use Migrations\AbstractMigration;

class AlterFoldersTableParentIdColumn extends AbstractMigration
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

        $table->changeColumn('parent_id', 'uuid', [
            'default' => null,
            'null' => true,
        ]);

        $table->update();
    }
}
