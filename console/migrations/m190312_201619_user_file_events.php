<?php

use yii\db\Migration;

/**
 * Class m190312_201619_user_file_events
 */
class m190312_201619_user_file_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            CREATE INDEX user_file_events_event_id_user_id
            ON {{%user_file_events}}
            USING btree
            (event_id, user_id);
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190312_201619_user_file_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190312_201619_user_file_events cannot be reverted.\n";

        return false;
    }
    */
}
