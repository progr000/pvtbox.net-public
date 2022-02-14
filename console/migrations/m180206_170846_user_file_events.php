<?php

use yii\db\Migration;

/**
 * Class m180206_170846_user_file_events
 */
class m180206_170846_user_file_events extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute("
            CREATE INDEX idx_17514_parent_after_event
            ON {{%user_file_events}} USING btree
            (parent_after_event)
            TABLESPACE pg_default;
        ");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180206_170846_user_file_events cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180206_170846_user_file_events cannot be reverted.\n";

        return false;
    }
    */
}
