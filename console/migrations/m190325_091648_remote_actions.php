<?php

use yii\db\Migration;

/**
 * Class m190325_091648_remote_actions
 */
class m190325_091648_remote_actions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%remote_actions}} ALTER COLUMN user_id DROP DEFAULT;");


        $this->execute("ALTER TABLE {{%remote_actions}} ALTER COLUMN target_node_id DROP DEFAULT;");
        $this->execute("
            ALTER TABLE {{%remote_actions}}
            ADD CONSTRAINT fk_remote_actions_target_node_id FOREIGN KEY (target_node_id)
            REFERENCES {{%user_node}} (node_id) MATCH SIMPLE
            ON UPDATE CASCADE
            ON DELETE CASCADE;
        ");

        $this->execute("ALTER TABLE {{%remote_actions}} ALTER COLUMN source_node_id DROP DEFAULT;");
        $this->execute("
            ALTER TABLE {{%remote_actions}}
            ADD CONSTRAINT fk_remote_actions_source_node_id FOREIGN KEY (source_node_id)
            REFERENCES {{%user_node}} (node_id) MATCH SIMPLE
            ON UPDATE CASCADE
            ON DELETE CASCADE;
        ");

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190325_091648_remote_actions cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190325_091648_remote_actions cannot be reverted.\n";

        return false;
    }
    */
}
