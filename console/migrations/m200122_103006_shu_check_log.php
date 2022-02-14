<?php

use yii\db\Migration;

/**
 * Class m200122_103006_shu_check_log
 */
class m200122_103006_shu_check_log extends Migration
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

            CREATE SEQUENCE {$schema}.{$tablePrefix}shu_check_log_record_id_seq
                INCREMENT 1
                START 1
                MINVALUE 1
                MAXVALUE 9223372036854775807
                CACHE 1;

            ALTER SEQUENCE {$schema}.{$tablePrefix}shu_check_log_record_id_seq
                OWNER TO {$userName};

            CREATE TABLE {$schema}.{$tablePrefix}shu_check_log
            (
                record_id bigint PRIMARY KEY NOT NULL DEFAULT nextval('{$tablePrefix}shu_check_log_record_id_seq'::regclass),
                shu_id bigint,
                check_ip CHARACTER VARYING(30) DEFAULT NULL,
                check_created TIMESTAMP WITHOUT TIME ZONE NOT NULL,
                check_data PUBLIC.CITEXT DEFAULT NULL,
                CONSTRAINT fk_shu_check_log_shu_id FOREIGN KEY (shu_id)
                    REFERENCES {$tablePrefix}self_host_users (shu_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE CASCADE
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            ALTER TABLE {$tablePrefix}shu_check_log
                OWNER to {$userName};
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200122_103006_shu_check_log cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200122_103006_shu_check_log cannot be reverted.\n";

        return false;
    }
    */
}
