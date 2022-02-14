<?php

use yii\db\Migration;

/**
 * Class m180712_081252_users
 */
class m180712_081252_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
          ALTER TABLE {{%users}}
          ADD COLUMN admin_full_name character varying(50) COLLATE pg_catalog.\"default\" NOT NULL DEFAULT ''::character varying;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180712_081252_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180712_081252_users cannot be reverted.\n";

        return false;
    }
    */
}
