<?php

use yii\db\Migration;

/**
 * Class m191010_194854_users
 */
class m191010_194854_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN has_personal_seller SMALLINT DEFAULT 0;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191010_194854_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191010_194854_users cannot be reverted.\n";

        return false;
    }
    */
}
