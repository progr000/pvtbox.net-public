<?php

use yii\db\Migration;

/**
 * Class m180423_133951_user_events
 */
class m180423_133951_user_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_file_events}} ADD COLUMN event_group_timestamp bigint DEFAULT NULL;");
        $this->execute("ALTER TABLE {{%user_file_events}} ALTER COLUMN event_group_timestamp SET DEFAULT extract(epoch from now());");
        $this->execute("CREATE INDEX idx_event_group_timestamp
                        ON {{%user_file_events}} USING btree
                        (event_group_timestamp)
                        TABLESPACE pg_default;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180423_133951_user_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180423_133951_user_events cannot be reverted.\n";

        return false;
    }
    */
}
