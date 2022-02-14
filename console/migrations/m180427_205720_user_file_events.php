<?php

use yii\db\Migration;

/**
 * Class m180427_205720_user_file_events
 */
class m180427_205720_user_file_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("UPDATE {{%user_file_events}} SET event_group_timestamp=event_timestamp WHERE event_group_timestamp IS NULL;");
        $this->execute("ALTER TABLE {{%user_file_events}} ALTER COLUMN event_group_timestamp SET NOT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180427_205720_user_file_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180427_205720_user_file_events cannot be reverted.\n";

        return false;
    }
    */
}
