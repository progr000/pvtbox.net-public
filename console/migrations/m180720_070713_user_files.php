<?php

use yii\db\Migration;

/**
 * Class m180720_070713_user_files
 */
class m180720_070713_user_files extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
          ALTER TABLE {{%user_files}}
          ADD COLUMN file_lastmtime bigint NOT NULL DEFAULT '0'::bigint;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180720_070713_user_files cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180720_070713_user_files cannot be reverted.\n";

        return false;
    }
    */
}
