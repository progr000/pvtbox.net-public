<?php

use yii\db\Migration;

/**
 * Class m190312_112923_user_file_events
 */
class m190312_112923_user_file_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_file_events}} ALTER COLUMN event_creator_user_id SET DEFAULT NULL;");
        $this->execute("ALTER TABLE {{%user_file_events}} ALTER COLUMN event_creator_node_id SET DEFAULT NULL;");
        $this->execute("ALTER TABLE {{%user_file_events}} ALTER COLUMN event_creator_user_id DROP NOT NULL;");
        $this->execute("ALTER TABLE {{%user_file_events}} ALTER COLUMN event_creator_node_id DROP NOT NULL;");

        $this->execute("
            ALTER TABLE {{%user_file_events}}
            ADD CONSTRAINT fk_user_file_events_event_creator_node_id FOREIGN KEY (node_id)
            REFERENCES {{%user_node}} (node_id) MATCH SIMPLE
            ON UPDATE CASCADE
            ON DELETE SET NULL;
        ");
        $this->execute("
            ALTER TABLE {{%user_file_events}}
            ADD CONSTRAINT fk_user_file_events_event_creator_user_id FOREIGN KEY (user_id)
            REFERENCES {{%users}} (user_id) MATCH SIMPLE
            ON UPDATE CASCADE
            ON DELETE CASCADE;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190312_112923_user_file_events cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190312_112923_user_file_events cannot be reverted.\n";

        return false;
    }
    */
}
