<?php

use yii\db\Migration;

/**
 * Class m200303_120024_user_file_events
 */
class m200303_120024_user_file_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_file_events}}
                        DROP CONSTRAINT fk_user_file_events_event_creator_node_id;");
        $this->execute("ALTER TABLE {{%user_file_events}}
                        DROP CONSTRAINT fk_user_file_events_event_creator_user_id;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200303_120024_user_file_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200303_120024_user_file_events cannot be reverted.\n";

        return false;
    }
    */
}
