<?php

use yii\db\Migration;

/**
 * Class m180816_070707_user_payments
 */
class m180816_070707_user_payments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_payments}} ALTER COLUMN pay_for TYPE character varying(255)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180816_070707_user_payments cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180816_070707_user_payments cannot be reverted.\n";

        return false;
    }
    */
}
