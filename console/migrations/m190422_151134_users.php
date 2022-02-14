<?php

use yii\db\Migration;

/**
 * Class m190422_151134_users
 */
class m190422_151134_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("UPDATE {{%users}} SET payment_already_initialized=1 WHERE license_type IN ('PAYED_PROFESSIONAL', 'PAYED_BUSINESS_ADMIN')");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190422_151134_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190422_151134_users cannot be reverted.\n";

        return false;
    }
    */
}
