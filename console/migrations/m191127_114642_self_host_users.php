<?php

use yii\db\Migration;

/**
 * Class m191127_114642_self_host_users
 */
class m191127_114642_self_host_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN static_timezone integer NOT NULL DEFAULT '0'::integer;");
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN dynamic_timezone integer NOT NULL DEFAULT '0'::integer;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191127_114642_self_host_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191127_114642_self_host_users cannot be reverted.\n";

        return false;
    }
    */
}
