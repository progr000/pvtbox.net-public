<?php

use yii\db\Migration;

/**
 * Class m200122_092248_self_host_users
 */
class m200122_092248_self_host_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN shu_license_last_check TIMESTAMP WITHOUT TIME ZONE DEFAULT NULL;");
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN shu_license_last_check_ip CHARACTER VARYING(30) DEFAULT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200122_092248_self_host_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200122_092248_self_host_users cannot be reverted.\n";

        return false;
    }
    */
}
