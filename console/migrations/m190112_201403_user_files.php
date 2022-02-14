<?php

use yii\db\Migration;

/**
 * Class m190112_201403_user_files
 */
class m190112_201403_user_files extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
       $this->execute("ALTER TABLE {{%user_files}} ADD COLUMN share_is_locked smallint NOT NULL DEFAULT 0;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190112_201403_user_files cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190112_201403_user_files cannot be reverted.\n";

        return false;
    }
    */
}
