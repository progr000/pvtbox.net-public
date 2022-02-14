<?php

use yii\db\Migration;

/**
 * Class m181004_205605_redis_safe
 */
class m181004_205605_redis_safe extends Migration
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

            CREATE SEQUENCE {$tablePrefix}redis_safe_rs_id_seq
            INCREMENT 1
            START 1
            MINVALUE 1
            MAXVALUE 9223372036854775807
            CACHE 1;

            CREATE TABLE {$tablePrefix}redis_safe
            (
                rs_id bigint NOT NULL DEFAULT nextval('{$tablePrefix}redis_safe_rs_id_seq'::regclass),
                rs_created timestamp without time zone,
                rs_type character varying(32) COLLATE pg_catalog.\"default\" NOT NULL,
                rs_data text COLLATE pg_catalog.\"default\",
                user_id bigint NOT NULL DEFAULT '0'::bigint,
                node_id bigint DEFAULT NULL,
                CONSTRAINT idx_r_id_primary PRIMARY KEY (rs_id),
                CONSTRAINT fk_redis_safe_user_id FOREIGN KEY (user_id)
                    REFERENCES {$tablePrefix}users (user_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE CASCADE
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            COMMENT ON TABLE {$tablePrefix}redis_safe
                IS 'Не отправленные в редис сообщения';

            COMMENT ON COLUMN {$tablePrefix}redis_safe.rs_id
                IS 'Id';
            COMMENT ON COLUMN {$tablePrefix}redis_safe.rs_created
                IS 'Дата создания записи в таблице';
            COMMENT ON COLUMN {$tablePrefix}redis_safe.rs_type
                IS 'Тип утерянной записи для редис';
            COMMENT ON COLUMN {$tablePrefix}redis_safe.rs_data
                IS 'Данные утерянной записи для редис';
            COMMENT ON COLUMN {$tablePrefix}redis_safe.user_id
                IS 'User ID';
            COMMENT ON COLUMN {$tablePrefix}redis_safe.node_id
                IS 'Node ID';
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181004_205605_redis_safe cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181004_205605_redis_safe cannot be reverted.\n";

        return false;
    }
    */
}
