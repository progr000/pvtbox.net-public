<?php

use yii\db\Migration;

/**
 * Class m180312_102530_citext_extention
 */
class m180312_102530_citext_extention extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //$this->execute("CREATE EXTENSION IF NOT EXISTS citext WITH SCHEMA public;");
        echo 'Run command << CREATE EXTENSION IF NOT EXISTS citext WITH SCHEMA public; >> from user = postgres (superuser) in SQL command line;';
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //$this->execute("DROP EXTENSION IF EXISTS citext;");
        echo 'Run command <DROP EXTENSION IF EXISTS citext;> from user = postgres (superuser) in SQL command line;';
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180312_102530_citext_extention cannot be reverted.\n";

        return false;
    }
    */
}
