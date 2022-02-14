<?php

use yii\db\Migration;

/**
 * Class m190104_075352_traffic_log
 */
class m190104_075352_traffic_log extends Migration
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

            CREATE SEQUENCE {$tablePrefix}traffic_log_record_id_seq
                INCREMENT 1
                START 1
                MINVALUE 1
                MAXVALUE 9223372036854775807
                CACHE 1;

            ALTER SEQUENCE {$tablePrefix}traffic_log_record_id_seq
                OWNER TO {$userName};

            CREATE TABLE {$tablePrefix}traffic_log
            (
                record_id bigint NOT NULL DEFAULT nextval('{$tablePrefix}traffic_log_record_id_seq'::regclass),
                record_created timestamp without time zone NOT NULL,
                user_id bigint NOT NULL DEFAULT '0'::bigint,
                node_id bigint NOT NULL DEFAULT '0'::bigint,
                event_uuid character varying(32) COLLATE pg_catalog.\"default\",
                interval int NOT NULL DEFAULT '0'::int,
                tx_wd bigint NOT NULL DEFAULT '0'::bigint,
                rx_wd bigint NOT NULL DEFAULT '0'::bigint,
                tx_wr bigint NOT NULL DEFAULT '0'::bigint,
                rx_wr bigint NOT NULL DEFAULT '0'::bigint,
                is_share smallint NOT NULL DEFAULT 0,
                CONSTRAINT idx_traffic_log_primary PRIMARY KEY (record_id),
                CONSTRAINT fk_traffic_log_user_id FOREIGN KEY (user_id)
                    REFERENCES {$tablePrefix}users (user_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,
                CONSTRAINT fk_traffic_log_node_id FOREIGN KEY (node_id)
                    REFERENCES {$tablePrefix}user_node (node_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE SET NULL
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            ALTER TABLE {$tablePrefix}traffic_log
                OWNER to {$userName};

            COMMENT ON TABLE {$tablePrefix}traffic_log
                IS 'Информация по трафику от нод';

            COMMENT ON COLUMN {$tablePrefix}traffic_log.record_id
                IS 'ID';

            COMMENT ON COLUMN {$tablePrefix}traffic_log.record_created
                IS 'Date';

            COMMENT ON COLUMN {$tablePrefix}traffic_log.user_id
                IS 'id пользователя, ссылка на users.user_id.';

            COMMENT ON COLUMN {$tablePrefix}traffic_log.node_id
                IS 'id ноды, ссылка на user_node.node_id';

            COMMENT ON COLUMN {$tablePrefix}traffic_log.event_uuid
                IS 'уникальный идентификатор события';

            COMMENT ON COLUMN {$tablePrefix}traffic_log.interval
                IS 'к-во секунд за которое считался трафик (передано/принято)';

            COMMENT ON COLUMN {$tablePrefix}traffic_log.tx_wd
                IS 'передано по webrtc p2p';

            COMMENT ON COLUMN {$tablePrefix}traffic_log.rx_wd
                IS 'принято по webrtc p2p';

            COMMENT ON COLUMN {$tablePrefix}traffic_log.tx_wr
                IS 'передано по webrtc relay';

            COMMENT ON COLUMN {$tablePrefix}traffic_log.rx_wr
                IS 'принято по webrtc relay';

            COMMENT ON COLUMN {$tablePrefix}traffic_log.is_share
                IS 'Признак что трафик был по расшаренному файлу';

            CREATE UNIQUE INDEX idx_{$tablePrefix}traffic_log_event_uuid
                ON {$tablePrefix}traffic_log USING btree
                (event_uuid COLLATE pg_catalog.\"default\", user_id)
                TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190104_075352_traffic_log cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190104_075352_traffic_log cannot be reverted.\n";

        return false;
    }
    */
}
