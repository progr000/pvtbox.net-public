<?php

use yii\db\Migration;

/**
 * Class m181101_083529_cron_info
 */
class m181101_083529_cron_info extends Migration
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

        $this->db->pdo->exec("
            SET search_path TO {$schema};

            CREATE SEQUENCE {$tablePrefix}cron_info_task_id_seq
            INCREMENT 1
            START 1
            MINVALUE 1
            MAXVALUE 9223372036854775807
            CACHE 1;

            CREATE TABLE {$tablePrefix}cron_info
            (
                task_id bigint NOT NULL DEFAULT nextval('{$tablePrefix}cron_info_task_id_seq'::regclass),
                task_name character varying(100) COLLATE pg_catalog.\"default\" NOT NULL,
                task_schedule character varying(255) COLLATE pg_catalog.\"default\" DEFAULT NULL,
                task_last_start timestamp without time zone,
                task_last_finish timestamp without time zone,
                task_log text COLLATE pg_catalog.\"default\",
                CONSTRAINT idx_task_id_primary PRIMARY KEY (task_id)
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            COMMENT ON TABLE {$tablePrefix}cron_info
                IS 'Информация о крон-скриптах';

            COMMENT ON COLUMN {$tablePrefix}cron_info.task_id
                IS 'Id';
            COMMENT ON COLUMN {$tablePrefix}cron_info.task_name
                IS 'Имя задачи';
            COMMENT ON COLUMN {$tablePrefix}cron_info.task_schedule
                IS 'Предпочтительное расписание запуска задачи';
            COMMENT ON COLUMN {$tablePrefix}cron_info.task_last_start
                IS 'Время последнего запуска задачи';
            COMMENT ON COLUMN {$tablePrefix}cron_info.task_last_finish
                IS 'Время последнего завершения задачи';
            COMMENT ON COLUMN {$tablePrefix}cron_info.task_log
                IS 'Лог задачи после последнего выполнения';

            CREATE UNIQUE INDEX idx_cron_info_task_name
                ON {$tablePrefix}cron_info USING btree
                (task_name COLLATE pg_catalog.\"default\")
                TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181101_083529_cron_info cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181101_083529_cron_info cannot be reverted.\n";

        return false;
    }
    */
}
