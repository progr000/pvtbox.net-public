<?php

use yii\db\Migration;

/**
 * Class m190206_170908_user_alerts_log
 */
class m190206_170908_user_alerts_log extends Migration
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
            SET search_path TO {$schema}, public;

            CREATE SEQUENCE {$schema}.{$tablePrefix}user_alerts_log_record_id_seq
                INCREMENT 1
                START 1
                MINVALUE 1
                MAXVALUE 9223372036854775807
                CACHE 1;

            ALTER SEQUENCE {$schema}.{$tablePrefix}user_alerts_log_record_id_seq
                OWNER TO {$userName};

            CREATE TABLE {$schema}.{$tablePrefix}user_alerts_log
            (
                record_id bigint NOT NULL DEFAULT nextval('{$tablePrefix}user_alerts_log_record_id_seq'::regclass),
                alert_created timestamp without time zone NOT NULL,
                alert_url character varying(255) COLLATE pg_catalog.\"default\" NOT NULL,
                alert_message citext COLLATE pg_catalog.\"default\" NOT NULL,
                alert_close_button smallint NOT NULL DEFAULT '0'::smallint,
                alert_ttl integer NOT NULL DEFAULT '0'::integer,
                alert_view_type character varying(15) COLLATE pg_catalog.\"default\" NOT NULL,
                alert_type character varying(32) COLLATE pg_catalog.\"default\" NOT NULL,
                alert_screen bytea,
                user_id bigint,
                CONSTRAINT idx_user_alerts_log_primary PRIMARY KEY (record_id),
                CONSTRAINT fk_mailq_user_id FOREIGN KEY (user_id)
                    REFERENCES {$tablePrefix}users (user_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE CASCADE
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            ALTER TABLE {$tablePrefix}user_alerts_log
                OWNER to {$userName};

            COMMENT ON TABLE {$tablePrefix}user_alerts_log
                IS 'Information about the alert that the user sees on the site';

            COMMENT ON COLUMN {$tablePrefix}user_alerts_log.record_id
                IS 'ID';

            COMMENT ON COLUMN {$tablePrefix}user_alerts_log.alert_created
                IS 'Date';

            COMMENT ON COLUMN {$tablePrefix}user_alerts_log.alert_url
                IS 'Url of page, where alert is coming';

            COMMENT ON COLUMN {$tablePrefix}user_alerts_log.alert_message
                IS 'Alert text';

            COMMENT ON COLUMN {$tablePrefix}user_alerts_log.alert_close_button
                IS 'Close button is showed or hidden for alert';

            COMMENT ON COLUMN {$tablePrefix}user_alerts_log.alert_ttl
                IS 'Time while alert is showing';

            COMMENT ON COLUMN {$tablePrefix}user_alerts_log.alert_view_type
                IS 'Type of alert window (flash or snack)';

            COMMENT ON COLUMN {$tablePrefix}user_alerts_log.alert_type
                IS 'Type of alert (danger, warning or notice)';

            COMMENT ON COLUMN {$tablePrefix}user_alerts_log.alert_screen
                IS 'Screenshot';

            COMMENT ON COLUMN {$tablePrefix}user_alerts_log.user_id
                IS 'UserID';

            CREATE INDEX idx_user_alerts_log_alert_type
                ON {$tablePrefix}user_alerts_log USING btree
                (alert_type COLLATE pg_catalog.\"default\")
                TABLESPACE pg_default;

            CREATE INDEX idx_user_alerts_log_alert_view_type
                ON {$tablePrefix}user_alerts_log USING btree
                (alert_view_type COLLATE pg_catalog.\"default\")
                TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190206_170908_user_alerts_log cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190206_170908_user_alerts_log cannot be reverted.\n";

        return false;
    }
    */
}
