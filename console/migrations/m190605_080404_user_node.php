<?php

use yii\db\Migration;

/**
 * Class m190605_080404_user_node
 */
class m190605_080404_user_node extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_node}} ADD COLUMN is_server SMALLINT NOT NULL DEFAULT 0::SMALLINT;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190605_080404_user_node cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190605_080404_user_node cannot be reverted.\n";

        return false;
    }
    */
}
