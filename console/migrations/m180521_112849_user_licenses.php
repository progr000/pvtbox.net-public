<?php

use yii\db\Migration;

/**
 * Class m180521_112849_user_licenses
 */
class m180521_112849_user_licenses extends Migration
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

            CREATE SEQUENCE {$tablePrefix}user_licenses_lic_id_seq
            INCREMENT 1
            START 1
            MINVALUE 1
            MAXVALUE 9223372036854775807
            CACHE 1;

            CREATE TABLE {$tablePrefix}user_licenses
            (
                lic_id bigint NOT NULL DEFAULT nextval('{$tablePrefix}user_licenses_lic_id_seq'::regclass),
                lic_start timestamp without time zone,
                lic_end timestamp without time zone,
                lic_period smallint NOT NULL DEFAULT '0'::smallint,
                lic_owner_user_id bigint NOT NULL DEFAULT '0'::bigint,
                lic_colleague_user_id bigint DEFAULT NULL,
                CONSTRAINT idx_user_licenses_lic_id_primary PRIMARY KEY (lic_id),
                CONSTRAINT fk_lic_owner_user_id FOREIGN KEY (lic_owner_user_id)
                    REFERENCES {$tablePrefix}users (user_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE CASCADE
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            COMMENT ON TABLE {$tablePrefix}user_licenses
                IS 'Приобретенные лицензии';
            COMMENT ON COLUMN {$tablePrefix}user_licenses.lic_id
                IS 'Id';
            COMMENT ON COLUMN {$tablePrefix}user_licenses.lic_start
                IS 'Дата начала лицензии';
            COMMENT ON COLUMN {$tablePrefix}user_licenses.lic_end
                IS 'Дата завершения лицензии';
            COMMENT ON COLUMN {$tablePrefix}user_licenses.lic_period
                IS 'Период действия лицензии';
            COMMENT ON COLUMN {$tablePrefix}user_licenses.lic_owner_user_id
                IS 'Владелец лицензии';
            COMMENT ON COLUMN {$tablePrefix}user_licenses.lic_colleague_user_id
                IS 'Ид юзера (коллеги) кому выдана лицензия';

            CREATE UNIQUE INDEX idx_owner_colleague_ids
                ON {$tablePrefix}user_licenses USING btree
                (lic_owner_user_id, lic_colleague_user_id)
                TABLESPACE pg_default;
        ");


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180521_112849_user_licenses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180521_112849_user_licenses cannot be reverted.\n";

        return false;
    }
    */
}
