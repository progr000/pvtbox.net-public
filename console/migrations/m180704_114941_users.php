<?php

use yii\db\Migration;

/**
 * Class m180704_114941_users
 */
class m180704_114941_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
          ALTER TABLE {{%users}}
          ADD COLUMN license_period smallint NOT NULL DEFAULT '0'::smallint;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180704_114941_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180704_114941_users cannot be reverted.\n";

        return false;
    }
    */
}
