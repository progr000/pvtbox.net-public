<?php

use yii\db\Migration;

/**
 * Class m180927_221217_queued_events
 */
class m180927_221217_queued_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%queued_events}} ADD CONSTRAINT event_uuid_pkey PRIMARY KEY USING INDEX idx_queued_events_event_uuid;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180927_221217_queued_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180927_221217_queued_events cannot be reverted.\n";

        return false;
    }
    */
}
