<?php

use yii\db\Migration;

/**
 * Class m200113_071754_users
 */
class m200113_071754_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN user_promo_code CHARACTER VARYING(30) DEFAULT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200113_071754_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200113_071754_users cannot be reverted.\n";

        return false;
    }
    */
}
