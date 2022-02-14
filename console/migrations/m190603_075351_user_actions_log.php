<?php

use yii\db\Migration;

/**
 * Class m190603_075351_user_actions_log
 */
class m190603_075351_user_actions_log extends Migration
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

            DROP TABLE IF EXISTS {$schema}.{$tablePrefix}user_actions_log;

            DROP SEQUENCE IF EXISTS {$schema}.{$tablePrefix}user_actions_log_record_id_seq;

            CREATE SEQUENCE {$schema}.{$tablePrefix}user_actions_log_record_id_seq
                INCREMENT 1
                START 1
                MINVALUE 1
                MAXVALUE 9223372036854775807
                CACHE 1;

            ALTER SEQUENCE {$schema}.{$tablePrefix}user_actions_log_record_id_seq
                OWNER TO {$userName};

            CREATE TABLE {$schema}.{$tablePrefix}user_actions_log
            (
                record_id bigint NOT NULL DEFAULT nextval('{$tablePrefix}user_actions_log_record_id_seq'::regclass),
                action_created timestamp without time zone NOT NULL,
                action_url character varying(255) COLLATE pg_catalog.\"default\" NOT NULL,
                action_type character varying(32) NOT NULL DEFAULT 'post',
                action_raw_data public.citext COLLATE pg_catalog.\"default\" NOT NULL,
                user_id bigint,
                CONSTRAINT idx_user_actions_log_primary PRIMARY KEY (record_id),
                CONSTRAINT fk_actions_user_id FOREIGN KEY (user_id)
                    REFERENCES {$tablePrefix}users (user_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE SET NULL
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            ALTER TABLE {$tablePrefix}user_actions_log
                OWNER to {$userName};

            COMMENT ON TABLE {$tablePrefix}user_actions_log
                IS 'Information about the actions that the user execute on the site';

            COMMENT ON COLUMN {$tablePrefix}user_actions_log.record_id
                IS 'ID';

            COMMENT ON COLUMN {$tablePrefix}user_actions_log.action_created
                IS 'Date';

            COMMENT ON COLUMN {$tablePrefix}user_actions_log.action_url
                IS 'Url of page, where action is coming';

            COMMENT ON COLUMN {$tablePrefix}user_actions_log.action_type
                IS 'Type of action (post|get)';

            COMMENT ON COLUMN {$tablePrefix}user_actions_log.action_raw_data
                IS 'raw data of action';

            COMMENT ON COLUMN {$tablePrefix}user_actions_log.user_id
                IS 'UserID';
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190603_075351_user_actions_log cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190603_075351_user_actions_log cannot be reverted.\n";

        return false;
    }
    */
}
