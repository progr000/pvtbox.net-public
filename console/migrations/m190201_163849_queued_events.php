<?php

use yii\db\Migration;

/**
 * Class m190201_163849_queued_events
 */
class m190201_163849_queued_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%queued_events}} ADD COLUMN queue_id character varying(20) COLLATE pg_catalog.\"default\" DEFAULT NULL;");
        $this->execute("CREATE INDEX idx_queued_events_queued_id
                        ON {{%queued_events}} USING btree
                        (queue_id COLLATE pg_catalog.\"default\")
                        TABLESPACE pg_default;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190201_163849_queued_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190201_163849_queued_events cannot be reverted.\n";

        return false;
    }
    */
}
