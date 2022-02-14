<?php

use yii\db\Migration;

/**
 * Class m200310_065632_software
 */
class m200310_065632_software extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP INDEX idx_software_url;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200310_065632_software cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200310_065632_software cannot be reverted.\n";

        return false;
    }
    */
}
