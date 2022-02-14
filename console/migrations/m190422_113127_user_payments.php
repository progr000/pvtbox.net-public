<?php

use yii\db\Migration;

/**
 * Class m190422_113127_user_payments
 */
class m190422_113127_user_payments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_payments}} ALTER COLUMN merchant_status TYPE character varying(255) COLLATE pg_catalog.\"default\";");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190422_113127_user_payments cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190422_113127_user_payments cannot be reverted.\n";

        return false;
    }
    */
}
