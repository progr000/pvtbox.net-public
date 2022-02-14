<?php

use yii\db\Migration;

/**
 * Class m181116_112501_user_files
 */
class m181116_112501_user_files extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_files}} ADD COLUMN first_event_id bigint DEFAULT NULL");
        $this->execute("ALTER TABLE {{%user_files}} ADD COLUMN last_event_id bigint DEFAULT NULL");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181116_112501_user_files cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181116_112501_user_files cannot be reverted.\n";

        return false;
    }
    */
}
