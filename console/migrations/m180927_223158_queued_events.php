<?php

use yii\db\Migration;

/**
 * Class m180927_223158_queued_events
 */
class m180927_223158_queued_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%queued_events}} DROP CONSTRAINT fk_queued_events_node_id;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180927_223158_queued_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180927_223158_queued_events cannot be reverted.\n";

        return false;
    }
    */
}
