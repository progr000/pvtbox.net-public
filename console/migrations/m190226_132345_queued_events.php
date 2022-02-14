<?php

use yii\db\Migration;

/**
 * Class m190226_132345_queued_events
 */
class m190226_132345_queued_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%queued_events}} DROP CONSTRAINT event_uuid_pkey;");
        ///////$this->execute("DROP INDEX idx_queued_events_event_uuid;");


        $this->execute("CREATE UNIQUE INDEX idx_queued_events_event_uuid
                        ON {{%queued_events}} USING btree
                        (event_uuid)
                        TABLESPACE pg_default;");

        $this->execute("ALTER TABLE {{%queued_events}} ADD CONSTRAINT event_uuid_pkey PRIMARY KEY USING INDEX idx_queued_events_event_uuid;");

        $this->execute("ALTER TABLE {{%queued_events}} DROP CONSTRAINT fk_queued_events_user_id;");
        $this->execute("ALTER TABLE {{%queued_events}} ALTER COLUMN user_id DROP DEFAULT;");
        $this->execute("ALTER TABLE {{%queued_events}} ALTER COLUMN node_id DROP DEFAULT;");
        $this->execute("ALTER TABLE {{%queued_events}} ALTER COLUMN user_id DROP NOT NULL;");
        $this->execute("ALTER TABLE {{%queued_events}} ALTER COLUMN node_id DROP NOT NULL;");

        $this->execute("UPDATE {{%queued_events}} SET node_id=null WHERE node_id=0;");

        $this->execute("ALTER TABLE {{%queued_events}} ADD CONSTRAINT fk_queued_events_user_id FOREIGN KEY (user_id)
                        REFERENCES {{%users}} (user_id) MATCH SIMPLE
                        ON UPDATE CASCADE
                        ON DELETE SET NULL");

        $this->execute("ALTER TABLE {{%queued_events}} ADD CONSTRAINT fk_queued_events_node_id FOREIGN KEY (node_id)
                        REFERENCES {{%user_node}} (node_id) MATCH SIMPLE
                        ON UPDATE CASCADE
                        ON DELETE SET NULL");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190226_132345_queued_events cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190226_132345_queued_events cannot be reverted.\n";

        return false;
    }
    */
}
