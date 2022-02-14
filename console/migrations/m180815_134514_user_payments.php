<?php

use yii\db\Migration;

/**
 * Class m180815_134514_user_payments
 */
class m180815_134514_user_payments extends Migration
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
                pay_date timestamp without time zone,
                pay_sum double precision NOT NULL,
                pay_type character varying(20) COLLATE pg_catalog.\"default\" NOT NULL,
                pay_for character varying(20) COLLATE pg_catalog.\"default\" DEFAULT NULL::character varying,
                license_type character varying(20) COLLATE pg_catalog.\"default\" NOT NULL,
                license_count smallint NOT NULL DEFAULT (0)::smallint,
                user_id bigint NOT NULL DEFAULT '0'::bigint,
                CONSTRAINT idx_user_payments_pays_id_primary PRIMARY KEY (pay_id),
                CONSTRAINT fk_user_payments_user_id FOREIGN KEY (user_id)
                    REFERENCES {$tablePrefix}users (user_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE CASCADE
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            ALTER TABLE {$tablePrefix}user_payments
                OWNER to {$userName};

            COMMENT ON TABLE {$tablePrefix}user_payments
                IS 'История платежей';

            COMMENT ON COLUMN {$tablePrefix}user_payments.pay_id
                IS 'Id';
            COMMENT ON COLUMN {$tablePrefix}user_payments.pay_date
                IS 'Payment date';
            COMMENT ON COLUMN {$tablePrefix}user_payments.pay_sum
                IS 'Payment sum';
            COMMENT ON COLUMN {$tablePrefix}user_payments.pay_type
                IS 'Payment type (card | crypto)';
            COMMENT ON COLUMN {$tablePrefix}user_payments.pay_for
                IS 'Payment info';
            COMMENT ON COLUMN {$tablePrefix}user_payments.license_type
                IS 'Pay for license_type';
            COMMENT ON COLUMN {$tablePrefix}user_payments.license_count
                IS 'Pay for license_count';
            COMMENT ON COLUMN {$tablePrefix}user_payments.user_id
                IS 'Owner of payment';
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180815_134514_user_payments cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_134514_user_payments cannot be reverted.\n";

        return false;
    }
    */
}
