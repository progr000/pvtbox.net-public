<?php

use yii\db\Migration;

/**
 * Class m180716_074009_users
 */
class m180716_074009_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
          ALTER TABLE {{%users}}
          ADD COLUMN pay_type character varying(20) COLLATE pg_catalog.\"default\" NOT NULL DEFAULT 'card'::character varying;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180716_074009_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180716_074009_users cannot be reverted.\n";

        return false;
    }
    */
}
