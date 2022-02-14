<?php

use yii\db\Migration;

/**
 * Class m190412_130146_user_payments
 */
class m190412_130146_user_payments extends Migration
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

        $this->execute("DROP TABLE {{%user_payments}}");
        $this->execute("DROP SEQUENCE {{%user_payments_pay_id_seq}}");

        $this->db->pdo->exec("
            SET search_path TO {$schema};

            CREATE SEQUENCE {$tablePrefix}user_payments_pay_id_seq
            INCREMENT 1
            START 1
            MINVALUE 1
            MAXVALUE 9223372036854775807
            CACHE 1;

            CREATE TABLE {$tablePrefix}user_payments
            (
                pay_id bigint NOT NULL DEFAULT nextval('{$tablePrefix}user_payments_pay_id_seq'::regclass),
                pay_date timestamp without time zone DEFAULT NULL,
                pay_amount double precision NOT NULL,
                pay_currency character varying(10) COLLATE pg_catalog.\"default\" DEFAULT NULL,
                pay_type character varying(50) COLLATE pg_catalog.\"default\" DEFAULT NULL,
                pay_for character varying(255) COLLATE pg_catalog.\"default\" DEFAULT NULL,
                pay_status character varying(50) COLLATE pg_catalog.\"default\" DEFAULT NULL,
                license_type character varying(20) COLLATE pg_catalog.\"default\" NOT NULL,
                license_count smallint NOT NULL DEFAULT (0)::smallint,
                license_period smallint NOT NULL DEFAULT (0)::smallint,
                license_expire timestamp without time zone DEFAULT NULL,
                user_id bigint DEFAULT NULL,
                merchant_id character varying(32) DEFAULT NULL,
                merchant_unique_pay_id character varying(50) DEFAULT NULL,
                merchant_created timestamp without time zone DEFAULT NULL,
                merchant_updated timestamp without time zone DEFAULT NULL,
                merchant_amount double precision DEFAULT NULL,
                merchant_currency character varying(10) COLLATE pg_catalog.\"default\" DEFAULT NULL,
                merchant_status character varying(50) COLLATE pg_catalog.\"default\" DEFAULT NULL,
                merchant_raw_data text DEFAULT NULL,
                CONSTRAINT idx_user_payments_pays_id_primary PRIMARY KEY (pay_id),
                CONSTRAINT fk_user_payments_user_id FOREIGN KEY (user_id)
                    REFERENCES {$tablePrefix}users (user_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE SET NULL
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            ALTER TABLE {$tablePrefix}user_payments
                OWNER to {$userName};

            COMMENT ON TABLE {$tablePrefix}user_payments
                IS 'История платежей';

            CREATE UNIQUE INDEX idx_merchant_unique_key
                ON {$tablePrefix}user_payments
                USING BTREE (merchant_id, merchant_unique_pay_id);
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190412_130146_user_payments cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190412_130146_user_payments cannot be reverted.\n";

        return false;
    }
    */
}
