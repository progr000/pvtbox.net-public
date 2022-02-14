<?php

use yii\db\Migration;

/**
 * Class m180219_150250_user_file_events
 */
class m180219_150250_user_file_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_file_events}} DROP CONSTRAINT fk_user_file_events_node_id2;");

        $this->execute("ALTER TABLE {{%user_file_events}}
                        ADD CONSTRAINT fk_user_file_events_node_id2 FOREIGN KEY (node_id)
                        REFERENCES {{%user_node}} (node_id) MATCH SIMPLE
                        ON UPDATE CASCADE
                        ON DELETE SET NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180219_150250_user_file_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180219_150250_user_file_events cannot be reverted.\n";

        return false;
    }
    */
}
