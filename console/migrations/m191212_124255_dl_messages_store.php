<?php

use yii\db\Migration;

/**
 * Class m191212_124255_dl_messages_store
 */
class m191212_124255_dl_messages_store extends Migration
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

            CREATE SEQUENCE {$schema}.{$tablePrefix}messages_store_seq
                INCREMENT 1
                START 1
                MINVALUE 1
                MAXVALUE 9223372036854775807
                CACHE 1;

            ALTER SEQUENCE {$schema}.{$tablePrefix}messages_store_seq
                OWNER TO {$userName};

            CREATE TABLE {$schema}.{$tablePrefix}messages_store
            (
                ms_id bigint PRIMARY KEY NOT NULL DEFAULT nextval('{$tablePrefix}messages_store_seq'::regclass),
                ms_created TIMESTAMP WITHOUT TIME ZONE NOT NULL,
                ms_type character varying(32) COLLATE pg_catalog.\"default\" NOT NULL,
                ms_data citext COLLATE pg_catalog.\"default\" NOT NULL,
                user_id bigint,
                CONSTRAINT fk_ms_user_id FOREIGN KEY (user_id)
                    REFERENCES {$tablePrefix}users (user_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE SET NULL
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            ALTER TABLE {$tablePrefix}messages_store
                OWNER to {$userName};

            CREATE INDEX idx_ms_type
                ON {$tablePrefix}messages_store USING btree
                (ms_type COLLATE pg_catalog.\"default\")
                TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191212_124255_dl_messages_store cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191212_124255_dl_messages_store cannot be reverted.\n";

        return false;
    }
    */
}
