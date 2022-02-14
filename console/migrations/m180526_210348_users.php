<?php

use yii\db\Migration;

/**
 * Class m180526_210348_users
 */
class m180526_210348_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN license_expire timestamp without time zone DEFAULT NULL;");
        $this->execute("COMMENT ON COLUMN {{%users}}.license_expire IS 'date when license is expire';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180526_210348_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180526_210348_users cannot be reverted.\n";

        return false;
    }
    */
}
