<?php

use yii\db\Migration;

/**
 * Class m180308_102522_remote_actions
 */
class m180308_102522_remote_actions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%remote_actions}}
                        ADD COLUMN action_data text COLLATE pg_catalog.\"default\" DEFAULT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180308_102522_remote_actions cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180308_102522_remote_actions cannot be reverted.\n";

        return false;
    }
    */
}
