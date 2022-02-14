<?php

use yii\db\Migration;

/**
 * Class m180410_065615_change_type_record_copy
 */
class m180410_065615_change_type_record_copy extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TYPE record_copy ALTER ATTRIBUTE file_created TYPE timestamp");
        $this->execute("ALTER TYPE record_copy ALTER ATTRIBUTE file_updated TYPE timestamp");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180410_065615_change_type_record_copy cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180410_065615_change_type_record_copy cannot be reverted.\n";

        return false;
    }
    */
}
