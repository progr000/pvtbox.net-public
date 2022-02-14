<?php

use yii\db\Migration;

/**
 * Class m200115_101717_self_host_users
 */
class m200115_101717_self_host_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN shu_business_status SMALLINT NOT NULL DEFAULT '0'::smallint;");
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN license_count_available INTEGER NOT NULL DEFAULT '0'::INTEGER ;");
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN license_mismatch SMALLINT NOT NULL DEFAULT '0'::smallint;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200115_101717_self_host_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200115_101717_self_host_users cannot be reverted.\n";

        return false;
    }
    */
}
