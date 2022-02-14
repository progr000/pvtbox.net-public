<?php

use yii\db\Migration;

/**
 * Class m181118_205156_user_files
 */
class m181118_205156_user_files extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_files}} ALTER COLUMN first_event_id DROP NOT NULL;");
        $this->execute("ALTER table {{%user_files}} ALTER COLUMN last_event_id DROP NOT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181118_205156_user_files cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181118_205156_user_files cannot be reverted.\n";

        return false;
    }
    */
}
