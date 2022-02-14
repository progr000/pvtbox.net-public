<?php

use yii\db\Migration;

/**
 * Class m190309_012556_user_files
 */
class m190309_012556_user_files extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TABLE {{%user_files}}
            ADD CONSTRAINT fk_user_files_node_id FOREIGN KEY (node_id)
            REFERENCES {{%user_node}} (node_id) MATCH SIMPLE
            ON UPDATE CASCADE
            ON DELETE SET NULL;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190309_012556_user_files cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190309_012556_user_files cannot be reverted.\n";

        return false;
    }
    */
}
