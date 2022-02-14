<?php

use yii\db\Migration;

/**
 * Class m190311_133812_user_files
 */
class m190311_133812_user_files extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_files}} ALTER file_created SET NOT NULL;");
        $this->execute("ALTER TABLE {{%user_files}} ALTER file_updated SET NOT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190311_133812_user_files cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190311_133812_user_files cannot be reverted.\n";

        return false;
    }
    */
}
