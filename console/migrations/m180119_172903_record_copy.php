<?php

use yii\db\Migration;

/**
 * Class m180119_172903_record_copy
 */
class m180119_172903_record_copy extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TYPE record_copy
            ADD ATTRIBUTE folder_children_count int;
        ");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180119_172903_record_copy cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180119_172903_record_copy cannot be reverted.\n";

        return false;
    }
    */
}
