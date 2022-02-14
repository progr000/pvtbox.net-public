<?php

use yii\db\Migration;

/**
 * Class m180417_125017_users
 */
class m180417_125017_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN user_company_name character varying(50) DEFAULT ''::character varying NOT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180417_125017_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180417_125017_users cannot be reverted.\n";

        return false;
    }
    */
}
