<?php

use yii\db\Migration;

/**
 * Class m180907_122052_user_payments
 */
class m180907_122052_user_payments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';

        $this->db->pdo->exec("
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN merchant_id character varying(32) COLLATE pg_catalog.\"default\" DEFAULT NULL;
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN invoice_id character varying(32) COLLATE pg_catalog.\"default\" DEFAULT NULL;
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN invoice_created timestamp without time zone  DEFAULT NULL;
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN invoice_expires timestamp without time zone DEFAULT NULL;
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN invoice_amount double precision DEFAULT NULL;
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN invoice_currency character varying(10) COLLATE pg_catalog.\"default\" DEFAULT NULL;
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN invoice_status character varying(15) COLLATE pg_catalog.\"default\" DEFAULT NULL;
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN invoice_url character varying(255) COLLATE pg_catalog.\"default\" DEFAULT NULL;
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN checkout_address character varying(100) COLLATE pg_catalog.\"default\" DEFAULT NULL;
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN checkout_amount double precision DEFAULT NULL;
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN checkout_currency character varying(10) COLLATE pg_catalog.\"default\" DEFAULT NULL;
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN invoice_changed timestamp without time zone DEFAULT NULL;

            CREATE UNIQUE INDEX idx_invoice_id ON {$tablePrefix}user_payments USING btree (invoice_id) TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180907_122052_user_payments cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180907_122052_user_payments cannot be reverted.\n";

        return false;
    }
    */
}
