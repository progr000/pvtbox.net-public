<?php

use yii\db\Migration;

/**
 * Class m200218_072419_users
 */
class m200218_072419_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN user_oo_address CHARACTER VARYING(255) DEFAULT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200218_072419_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200218_072419_users cannot be reverted.\n";

        return false;
    }
    */
}
