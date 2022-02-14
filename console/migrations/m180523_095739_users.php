<?php

use yii\db\Migration;

/**
 * Class m180523_095739_users
 */
class m180523_095739_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN previous_license_business_from bigint DEFAULT NULL;");
        $this->execute("COMMENT ON COLUMN {{%users}}.previous_license_business_from IS 'user_id who give previous license for this user';");
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN previous_license_business_finish timestamp without time zone DEFAULT NULL;");
        $this->execute("COMMENT ON COLUMN {{%users}}.previous_license_business_finish IS 'date when previous license closed for user';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180523_095739_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180523_095739_users cannot be reverted.\n";

        return false;
    }
    */
}
