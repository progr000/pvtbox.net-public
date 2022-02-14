<?php

use yii\db\Migration;

/**
 * Class m180927_205328_queued_events
 */
class m180927_205328_queued_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';

        $this->db->pdo->exec("
            ALTER TABLE {$tablePrefix}queued_events ADD COLUMN job_type character varying(20) COLLATE pg_catalog.\"default\" DEFAULT NULL;
            ALTER TABLE {$tablePrefix}queued_events ADD COLUMN job_status character varying(20) COLLATE pg_catalog.\"default\" DEFAULT NULL;
            ALTER TABLE {$tablePrefix}queued_events ADD COLUMN job_created timestamp without time zone;
            ALTER TABLE {$tablePrefix}queued_events ADD COLUMN job_started timestamp without time zone;
            ALTER TABLE {$tablePrefix}queued_events ADD COLUMN job_finished timestamp without time zone;

            CREATE INDEX idx_job_type ON {$tablePrefix}queued_events USING btree (job_type) TABLESPACE pg_default;
            CREATE INDEX idx_job_status ON {$tablePrefix}queued_events USING btree (job_status) TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180927_205328_queued_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180927_205328_queued_events cannot be reverted.\n";

        return false;
    }
    */
}
