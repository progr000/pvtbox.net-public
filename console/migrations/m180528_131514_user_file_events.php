<?php

use yii\db\Migration;

/**
 * Class m180528_131514_user_file_events
 */
class m180528_131514_user_file_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            CREATE SEQUENCE {{%user_file_events_group_id_seq}}
            INCREMENT 1
            START 1
            MINVALUE 1
            MAXVALUE 9223372036854775807
            CACHE 1;
        ");
        $this->execute("ALTER TABLE {{%user_file_events}} ADD COLUMN event_group_id bigint DEFAULT NULL;");
        $this->execute("CREATE INDEX idx_event_group_id
                        ON {{%user_file_events}} USING btree
                        (event_group_id)
                        TABLESPACE pg_default;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180528_131514_user_file_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180528_131514_user_file_events cannot be reverted.\n";

        return false;
    }
    */
}
