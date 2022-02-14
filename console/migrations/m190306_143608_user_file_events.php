<?php

use yii\db\Migration;

/**
 * Class m190306_143608_user_file_events
 */
class m190306_143608_user_file_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_file_events}}
                        ADD CONSTRAINT fk_user_file_events_user_id FOREIGN KEY (user_id)
                        REFERENCES {{%users}} (user_id) MATCH SIMPLE
                        ON UPDATE CASCADE
                        ON DELETE CASCADE;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE {{%user_file_events}}
                        DROP CONSTRAINT fk_user_file_events_user_id;");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190306_143608_user_file_events cannot be reverted.\n";

        return false;
    }
    */
}
