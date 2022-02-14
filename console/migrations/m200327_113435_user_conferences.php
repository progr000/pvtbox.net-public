<?php

use yii\db\Migration;

/**
 * Class m200327_113435_user_conferences
 */
class m200327_113435_user_conferences extends Migration
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

            CREATE SEQUENCE {$schema}.{$tablePrefix}user_conferences_record_id_seq
                INCREMENT 1
                START 1
                MINVALUE 1
                MAXVALUE 9223372036854775807
                CACHE 1;

            ALTER SEQUENCE {$schema}.{$tablePrefix}user_conferences_record_id_seq
                OWNER TO {$userName};

            CREATE TABLE {$schema}.{$tablePrefix}user_conferences
            (
                conference_id bigint PRIMARY KEY NOT NULL DEFAULT nextval('{$tablePrefix}user_conferences_record_id_seq'::regclass),
                conference_created TIMESTAMP WITHOUT TIME ZONE NOT NULL,
                conference_updated TIMESTAMP WITHOUT TIME ZONE NOT NULL,
                conference_unique_hash CHARACTER VARYING(32) NOT NULL,
                conference_name CHARACTER VARYING(50) NOT NULL,
                conference_participants TEXT,
                conference_status smallint NOT NULL DEFAULT 0,
                user_id bigint NOT NULL,
                CONSTRAINT fk_user_conferences_user_id FOREIGN KEY (user_id)
                    REFERENCES {$tablePrefix}users (user_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE CASCADE
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            CREATE UNIQUE INDEX conference_unique_hash_idx ON {$schema}.{$tablePrefix}user_conferences USING BTREE (conference_unique_hash);

            ALTER TABLE {$tablePrefix}user_conferences
                OWNER to {$userName};
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200327_113435_user_conferences cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200327_113435_user_conferences cannot be reverted.\n";

        return false;
    }
    */
}
