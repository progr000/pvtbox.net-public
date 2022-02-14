<?php

use yii\db\Migration;

/**
 * Class m190109_214658_traffic_log
 */
class m190109_214658_traffic_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $schema   = isset(Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema'])
            ? Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema']
            : 'public';

        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';

        $this->db->pdo->exec("
            SET search_path TO {$schema};

            DROP INDEX idx_{$tablePrefix}traffic_log_event_uuid;

            CREATE INDEX idx_traffic_log_event_uuid
                ON {$tablePrefix}traffic_log USING btree
                (event_uuid COLLATE pg_catalog.\"default\", user_id)
                TABLESPACE pg_default;

            CREATE INDEX idx_traffic_log_record_created
                ON {$tablePrefix}traffic_log USING btree
                (record_created)
                TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190109_214658_traffic_log cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190109_214658_traffic_log cannot be reverted.\n";

        return false;
    }
    */
}
