<?php

use yii\db\Migration;

/**
 * Class m180503_112021_users
 */
class m180503_112021_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN shares_count_in24 smallint NOT NULL DEFAULT 0;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180503_112021_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180503_112021_users cannot be reverted.\n";

        return false;
    }
    */
}
