<?php

use yii\db\Migration;

/**
 * Class m180115_124900_init_extention
 */
class m180115_123000_init_extention extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $userName = isset(Yii::$app->components['db']['username'])
            ? Yii::$app->components['db']['username']
            : 'username';

        //$this->execute('CREATE EXTENSION "uuid-ossp";');
        /*
        CREATE DATABASE direct_link
            WITH
            OWNER = {$userName}
            ENCODING = 'UTF8'
            LC_COLLATE = 'ru_UA.UTF-8'
            LC_CTYPE = 'ru_UA.UTF-8'
            TABLESPACE = pg_default
            CONNECTION LIMIT = -1;

        CREATE SCHEMA first
            AUTHORIZATION {$userName};

        ALTER DATABASE direct_link
            SET search_path TO public, first;
        */

        echo 'Run command << CREATE EXTENSION "uuid-ossp" WITH SCHEMA public; >> from user = postgres (superuser) in SQL command line;';
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180115_124900_init_extention cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180115_124900_init_extention cannot be reverted.\n";

        return false;
    }
    */
}
