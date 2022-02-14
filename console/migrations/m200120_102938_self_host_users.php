<?php

use yii\db\Migration;

/**
 * Class m200120_102938_self_host_users
 */
class m200120_102938_self_host_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN license_count_used INTEGER NOT NULL DEFAULT '0'::INTEGER;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200120_102938_self_host_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200120_102938_self_host_users cannot be reverted.\n";

        return false;
    }
    */
}
