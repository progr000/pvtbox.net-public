<?php

use yii\db\Migration;

/**
 * Class m190325_185102_bad_logins
 */
class m190325_185102_bad_logins extends Migration
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

            CREATE SEQUENCE {$schema}.{$tablePrefix}bad_logins_record_id_seq
                INCREMENT 1
                START 1
                MINVALUE 1
                MAXVALUE 9223372036854775807
                CACHE 1;

            ALTER SEQUENCE {$schema}.{$tablePrefix}bad_logins_record_id_seq
                OWNER TO {$userName};

            CREATE TABLE {$schema}.{$tablePrefix}bad_logins
            (
                bl_id bigint NOT NULL DEFAULT nextval('{$tablePrefix}bad_logins_record_id_seq'::regclass),
                bl_created timestamp without time zone NOT NULL,
                bl_updated timestamp without time zone NOT NULL,
                bl_ip character varying(32) COLLATE pg_catalog.\"default\" NOT NULL,
                bl_count_tries smallint NOT NULL default 1,
                bl_last_timestamp bigint NOT NULL,
                bl_locked smallint NOT NULL default 0,
                bl_lock_seconds bigint NOT NULL default 0,
                CONSTRAINT idx_bad_logins_primary PRIMARY KEY (bl_id)
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            ALTER TABLE {$tablePrefix}bad_logins
                OWNER to {$userName};

            COMMENT ON TABLE {$tablePrefix}bad_logins
                IS 'Info about locked IP cause its has many tries bad login';

            COMMENT ON COLUMN {$tablePrefix}bad_logins.bl_id
                IS 'ID';

            COMMENT ON COLUMN {$tablePrefix}bad_logins.bl_created
                IS 'Creation Date';

            COMMENT ON COLUMN {$tablePrefix}bad_logins.bl_updated
                IS 'Update Date';

            COMMENT ON COLUMN {$tablePrefix}bad_logins.bl_ip
                IS 'IP';

            COMMENT ON COLUMN {$tablePrefix}bad_logins.bl_count_tries
                IS 'Count of tries bad login';

            COMMENT ON COLUMN {$tablePrefix}bad_logins.bl_last_timestamp
                IS 'Last bad login try';

            COMMENT ON COLUMN {$tablePrefix}bad_logins.bl_locked
                IS 'locked or not (1 or 0)';

            COMMENT ON COLUMN {$tablePrefix}bad_logins.bl_lock_seconds
                IS 'Count seconds for lock';

            CREATE UNIQUE INDEX idx_bad_logins_bl_ip
                ON {$tablePrefix}bad_logins USING btree
                (bl_ip COLLATE pg_catalog.\"default\")
                TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190325_185102_bad_logins cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190325_185102_bad_logins cannot be reverted.\n";

        return false;
    }
    */
}
