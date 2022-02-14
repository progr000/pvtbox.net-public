<?php

use yii\db\Migration;

/**
 * Class m191011_131914_admins
 */
class m191011_131914_admins extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%admins}} ADD COLUMN admin_role SMALLINT DEFAULT 0;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191011_131914_admins cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191011_131914_admins cannot be reverted.\n";

        return false;
    }
    */
}
