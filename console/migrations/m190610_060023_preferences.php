<?php

use yii\db\Migration;

/**
 * Class m190610_060023_preferences
 */
class m190610_060023_preferences extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%preferences}} ALTER COLUMN pref_value TYPE TEXT");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190610_060023_preferences cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190610_060023_preferences cannot be reverted.\n";

        return false;
    }
    */
}
