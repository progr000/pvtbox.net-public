<?php

use yii\db\Migration;

/**
 * Class m190312_162731_user_colleagues
 */
class m190312_162731_user_colleagues extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $schema   = isset(Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema'])
            ? "public, " . Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema']
            : 'public';

        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';

        Yii::$app->db->pdo->exec("
            SET search_path TO {$schema};

            ALTER TABLE {$tablePrefix}user_colleagues ALTER COLUMN colleague_email TYPE CITEXT;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190312_162731_user_colleagues cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190312_162731_user_colleagues cannot be reverted.\n";

        return false;
    }
    */
}
