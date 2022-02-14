<?php

use yii\db\Migration;

/**
 * Class m200414_093355_user_conferences
 */
class m200414_093355_user_conferences extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_conferences}} ALTER  conference_unique_hash DROP NOT NULL;");
        $this->execute("ALTER TABLE {{%user_conferences}} RENAME COLUMN conference_unique_hash TO room_uuid;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200414_093355_user_conferences cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200414_093355_user_conferences cannot be reverted.\n";

        return false;
    }
    */
}
