<?php

use yii\db\Migration;

/**
 * Class m180115_122613_init_migrate
 */
class m180115_122613_init_migrate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $dbname = 'direct_link_test';
        $dsn = Yii::$app->components['db']['dsn'];
        $dsn2 = explode(':', $dsn);
        if (isset($dsn2[1])) {
            $dsn3 = explode(';', $dsn2[1]);
            if (is_array($dsn3)) {
                foreach ($dsn3 as $v) {
                    $dsn4 = explode('=', $v);
                    if ($dsn4[0] == 'dbname' && isset($dsn4[1])) {
                        $dbname = $dsn4[1];
                        break;
                    }
                }
            }
        }
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
            SET statement_timeout = 0;
            SET lock_timeout = 0;
            SET idle_in_transaction_session_timeout = 0;
            SET client_encoding = 'UTF8';
            SET standard_conforming_strings = on;
            SET check_function_bodies = false;
            SET client_min_messages = warning;
            SET row_security = off;
        ");

        if ($schema !== 'public') {
            $this->db->pdo->exec("
                CREATE SCHEMA {$schema};
                SET search_path = {$schema}, public;
                ALTER DATABASE {$dbname} SET search_path TO public, {$schema};

                CREATE TABLE {$schema}.{$tablePrefix}migration
                (
                    version character varying(180) COLLATE pg_catalog.\"default\" NOT NULL,
                    apply_time integer,
                    CONSTRAINT {$tablePrefix}migration_pkey PRIMARY KEY (version)
                )
                WITH (
                    OIDS = FALSE
                )
                TABLESPACE pg_default;

                ALTER TABLE {$schema}.{$tablePrefix}migration
                    OWNER to {$userName};

                INSERT INTO {$schema}.{$tablePrefix}migration VALUES('m000000_000000_base', 1516018986);

                --DROP TABLE public.{$tablePrefix}migration;
                DROP TABLE IF EXISTS public.{$tablePrefix}migration;
            ");
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180115_122613_init_migrate cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180115_122613_init_migrate cannot be reverted.\n";

        return false;
    }
    */
}
