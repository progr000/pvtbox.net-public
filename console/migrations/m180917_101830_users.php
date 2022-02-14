<?php

use yii\db\Migration;

/**
 * Class m180917_101830_users
 */
class m180917_101830_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN payment_already_initialized smallint NOT NULL DEFAULT '0'::smallint;");
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN payment_init_date timestamp without time zone");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180917_101830_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180917_101830_users cannot be reverted.\n";

        return false;
    }
    */
}
