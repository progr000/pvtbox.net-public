<?php

use yii\db\Migration;

/**
 * Class m180312_104454_user_files_citext
 */
class m180312_104454_user_files_citext extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //$this->execute("ALTER TABLE {{%user_files}} ALTER COLUMN file_name TYPE CITEXT;");
        $schema   = isset(Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema'])
            ? "public, " . Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema']
            : 'public';

        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';

        Yii::$app->db->pdo->exec("
            SET search_path TO {$schema};

            ALTER TABLE {$tablePrefix}user_files ALTER COLUMN file_name TYPE CITEXT;
        ");


        $this->execute("DROP INDEX idx_17498_file_name;");
        $this->execute("CREATE UNIQUE INDEX idx_17498_file_name
                        ON {{%user_files}} USING btree
                        (file_name COLLATE pg_catalog.\"default\", file_parent_id, user_id, is_deleted)
                        TABLESPACE pg_default;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE {{%user_files}} ALTER COLUMN file_name TYPE character varying(255);");
        $this->execute("DROP INDEX idx_17498_file_name;");
        $this->execute("CREATE UNIQUE INDEX idx_17498_file_name
                        ON {{%user_files}} USING btree
                        (file_name COLLATE pg_catalog.\"default\", file_parent_id, user_id, is_deleted)
                        TABLESPACE pg_default;");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180312_104454_user_files_citext cannot be reverted.\n";

        return false;
    }
    */
}
