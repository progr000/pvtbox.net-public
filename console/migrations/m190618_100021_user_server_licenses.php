<?php

use yii\db\Migration;

/**
 * Class m190618_100021_user_server_licenses
 */
class m190618_100021_user_server_licenses extends Migration
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

            DROP TABLE IF EXISTS {$schema}.{$tablePrefix}user_server_licenses;

            DROP SEQUENCE IF EXISTS {$schema}.{$tablePrefix}user_server_licenses_lic_srv_id_seq;

            CREATE SEQUENCE {$schema}.{$tablePrefix}user_server_licenses_lic_srv_id_seq
                INCREMENT 1
                START 1
                MINVALUE 1
                MAXVALUE 9223372036854775807
                CACHE 1;

            ALTER SEQUENCE {$schema}.{$tablePrefix}user_server_licenses_lic_srv_id_seq
                OWNER TO {$userName};

            CREATE TABLE {$schema}.{$tablePrefix}user_server_licenses
            (
                lic_srv_id BIGINT PRIMARY KEY NOT NULL DEFAULT nextval('{$tablePrefix}user_server_licenses_lic_srv_id_seq'::regclass),
                lic_srv_start TIMESTAMP WITHOUT TIME ZONE,
                lic_srv_end TIMESTAMP WITHOUT TIME ZONE,
                lic_srv_period SMALLINT NOT NULL DEFAULT '0'::smallint,
                lic_srv_owner_user_id BIGINT NOT NULL DEFAULT '0'::bigint,
                lic_srv_colleague_user_id BIGINT,
                lic_srv_node_id BIGINT,
                lic_srv_lastpay_timestamp BIGINT NOT NULL DEFAULT (0)::bigint,
                lic_srv_group_id BIGINT NOT NULL DEFAULT (0)::bigint,
                FOREIGN KEY (lic_srv_owner_user_id) REFERENCES {$tablePrefix}users (user_id)
                    MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,
                FOREIGN KEY (lic_srv_colleague_user_id) REFERENCES {$tablePrefix}users (user_id)
                    MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE SET NULL,
                FOREIGN KEY (lic_srv_node_id) REFERENCES {$tablePrefix}user_node (node_id)
                    MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE SET NULL
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            ALTER TABLE {$tablePrefix}user_server_licenses
                OWNER to {$userName};

            CREATE INDEX idx_lic_srv_lastpay_timestamp ON {$tablePrefix}user_server_licenses USING BTREE (lic_srv_lastpay_timestamp);

            CREATE UNIQUE INDEX idx_lic_srv_owner_user_id
                ON {$tablePrefix}user_server_licenses
                USING BTREE (lic_srv_owner_user_id, lic_srv_node_id);

            CREATE INDEX idx_lic_srv_colleague_user_id
                ON {$tablePrefix}user_server_licenses
                USING BTREE (lic_srv_colleague_user_id);

            COMMENT ON TABLE {$tablePrefix}user_server_licenses IS 'Приобретенные серверные лицензии';
            COMMENT ON COLUMN {$tablePrefix}user_server_licenses.lic_srv_id IS 'Id';
            COMMENT ON COLUMN {$tablePrefix}user_server_licenses.lic_srv_start IS 'Дата начала лицензии';
            COMMENT ON COLUMN {$tablePrefix}user_server_licenses.lic_srv_end IS 'Дата завершения лицензии';
            COMMENT ON COLUMN {$tablePrefix}user_server_licenses.lic_srv_period IS 'Период действия лицензии';
            COMMENT ON COLUMN {$tablePrefix}user_server_licenses.lic_srv_owner_user_id IS 'Владелец лицензии';
            COMMENT ON COLUMN {$tablePrefix}user_server_licenses.lic_srv_colleague_user_id IS 'Ид юзера (коллеги) кому присвоена лицензия';
            COMMENT ON COLUMN {$tablePrefix}user_server_licenses.lic_srv_node_id IS 'Ид ноды которой присвоена лицензия';
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190618_100021_user_server_licenses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190618_100021_user_server_licenses cannot be reverted.\n";

        return false;
    }
    */
}
