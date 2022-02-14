<?php

use yii\db\Migration;

/**
 * Class m190603_072400_software
 */
class m190603_072400_software extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%software}} ALTER COLUMN software_version TYPE CHARACTER VARYING(50)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190603_072400_software cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190603_072400_software cannot be reverted.\n";

        return false;
    }
    */
}
