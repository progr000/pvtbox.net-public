<?php

use yii\db\Migration;

/**
 * Class m180226_175242_queued_events
 */
class m180226_175242_queued_events extends Migration
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

        $userName = isset(Yii::$app->components['db']['username'])
            ? Yii::$app->components['db']['username']
            : 'username';

        $this->db->pdo->exec("
            SET search_path TO {$schema};

            CREATE TABLE {$tablePrefix}queued_events
            (
                event_uuid character varying(32) COLLATE pg_catalog.\"default\",
                job_id character varying(32) COLLATE pg_catalog.\"default\" NOT NULL DEFAULT ''::character varying,
                node_id bigint NOT NULL DEFAULT '0'::bigint,
                user_id bigint NOT NULL DEFAULT '0'::bigint,
                CONSTRAINT fk_queued_events_user_id FOREIGN KEY (user_id)
                    REFERENCES {$tablePrefix}users (user_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE SET NULL,
                CONSTRAINT fk_queued_events_node_id FOREIGN KEY (node_id)
                    REFERENCES {$tablePrefix}user_node (node_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE SET NULL
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            ALTER TABLE {$tablePrefix}queued_events
                OWNER to {$userName};

            COMMENT ON TABLE {$tablePrefix}queued_events
                IS 'евенты которые еще не попали в базу но стоят в очереди';

            COMMENT ON COLUMN {$tablePrefix}queued_events.event_uuid
                IS 'уникальный идентификатор события которое однозначно определяет состояние файла в момент события';

            COMMENT ON COLUMN {$tablePrefix}queued_events.job_id
                IS 'id задачи, которая находится в очереди';

            COMMENT ON COLUMN {$tablePrefix}queued_events.node_id
                IS 'id ноды, на которой возникло событие ссылка на user_node.node_id';

            COMMENT ON COLUMN {$tablePrefix}queued_events.user_id
                IS 'id пользователя у которого возникло событие, ссылка на users.user_id.';

            CREATE UNIQUE INDEX idx_queued_events_event_uuid
                ON {$tablePrefix}queued_events USING btree
                (event_uuid COLLATE pg_catalog.\"default\", user_id)
                TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180226_175242_queued_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180226_175242_queued_events cannot be reverted.\n";

        return false;
    }
    */
}
