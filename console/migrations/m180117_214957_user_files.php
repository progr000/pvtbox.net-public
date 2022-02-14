<?php

use yii\db\Migration;

/**
 * Class m180117_214957_user_files
 */
class m180117_214957_user_files extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_files}} ADD folder_children_count int NOT NULL DEFAULT '0'::int");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180117_214957_user_files cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180117_214957_user_files cannot be reverted.\n";

        return false;
    }
    */
}
