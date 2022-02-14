<?php

use yii\db\Migration;

/**
 * Class m180516_071753_users
 */
class m180516_071753_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN license_business_from bigint DEFAULT NULL;");
        $this->execute("COMMENT ON COLUMN {{%users}}.license_business_from IS 'user_id who give license for this user';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180516_071753_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180516_071753_users cannot be reverted.\n";

        return false;
    }
    */
}
