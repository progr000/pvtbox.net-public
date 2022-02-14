<?php

use yii\db\Migration;

/**
 * Class m180313_085815_users_citext
 */
class m180313_085815_users_citext extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //$this->execute("ALTER TABLE {{%users}} ALTER COLUMN user_email TYPE CITEXT;");
        $schema   = isset(Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema'])
            ? "public, " . Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema']
            : 'public';

        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';

        Yii::$app->db->pdo->exec("
            SET search_path TO {$schema};

            ALTER TABLE {$tablePrefix}users ALTER COLUMN user_email TYPE CITEXT;
        ");


        $this->execute("DROP INDEX idx_17465_user_email;");
        $this->execute("CREATE UNIQUE INDEX idx_17465_user_email
                        ON {{%users}} USING btree
                        (user_email COLLATE pg_catalog.\"default\")
                        TABLESPACE pg_default;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN user_email TYPE character varying(50);");
        $this->execute("DROP INDEX idx_17465_user_email;");
        $this->execute("CREATE UNIQUE INDEX idx_17465_user_email
                        ON {{%users}} USING btree
                        (user_email COLLATE pg_catalog.\"default\")
                        TABLESPACE pg_default;");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180313_085815_users_citext cannot be reverted.\n";

        return false;
    }
    */
}
