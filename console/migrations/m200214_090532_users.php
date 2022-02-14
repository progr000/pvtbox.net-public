<?php

use yii\db\Migration;

/**
 * Class m200214_090532_users
 */
class m200214_090532_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN license_key_for_sh CHARACTER VARYING(128) DEFAULT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200214_090532_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200214_090532_users cannot be reverted.\n";

        return false;
    }
    */
}
