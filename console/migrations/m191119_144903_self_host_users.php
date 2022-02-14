<?php

use yii\db\Migration;

/**
 * Class m191119_144903_self_host_users
 */
class m191119_144903_self_host_users extends Migration
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

            CREATE SEQUENCE {$schema}.{$tablePrefix}shu_id_seq
                INCREMENT 1
                START 1
                MINVALUE 1
                MAXVALUE 9223372036854775807
                CACHE 1;

            ALTER SEQUENCE {$schema}.{$tablePrefix}shu_id_seq
                OWNER TO {$userName};

            CREATE TABLE {$schema}.{$tablePrefix}self_host_users
            (
                shu_id bigint PRIMARY KEY NOT NULL DEFAULT nextval('{$tablePrefix}shu_id_seq'::regclass),
                shu_company CHARACTER VARYING(100) NOT NULL,
                shu_name CHARACTER VARYING(100) NOT NULL,
                shu_email CHARACTER VARYING(50) NOT NULL,
                auth_key CHARACTER VARYING(32) NOT NULL,
                password_hash CHARACTER VARYING(255) NOT NULL,
                password_reset_token CHARACTER VARYING(255),
                shu_created TIMESTAMP WITHOUT TIME ZONE NOT NULL,
                shu_updated TIMESTAMP WITHOUT TIME ZONE NOT NULL,
                shu_status SMALLINT NOT NULL DEFAULT 0,
                shu_role SMALLINT DEFAULT 0,
                shu_support_status SMALLINT NOT NULL DEFAULT 1,
                shu_support_cost NUMERIC(11,2) NOT NULL DEFAULT 0.00,
                shu_brand_status SMALLINT NOT NULL DEFAULT 1,
                shu_brand_cost NUMERIC(11,2) NOT NULL DEFAULT 0.00,
                user_id bigint,
                CONSTRAINT fk_shu_user_id FOREIGN KEY (user_id)
                    REFERENCES {$tablePrefix}users (user_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE SET NULL
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            ALTER TABLE {$tablePrefix}self_host_users
                OWNER to {$userName};

            CREATE UNIQUE INDEX idx_shu_password_reset_token
                ON {$tablePrefix}self_host_users USING BTREE
                (password_reset_token COLLATE pg_catalog.\"default\")
                TABLESPACE pg_default;

        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191119_144903_self_host_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191119_144903_self_host_users cannot be reverted.\n";

        return false;
    }
    */
}
