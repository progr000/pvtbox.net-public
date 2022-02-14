<?php

use yii\db\Migration;

/**
 * Class m190220_100144_change_function_get_count_children_for
 */
class m190220_100144_change_function_get_count_children_for extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $schema   = isset(Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema'])
            ? Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema']
            : 'public';

        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';

        $userName = isset(Yii::$app->components['db']['username'])
            ? Yii::$app->components['db']['username']
            : 'username';

        $this->db->pdo->exec("
            SET search_path TO {$schema};

            -- FUNCTION: get_count_children_for(bigint, bool)

            DROP FUNCTION get_count_children_for(bigint, bool);

            CREATE OR REPLACE FUNCTION get_count_children_for(
                _folder_id bigint,
                _with_deleted bool)
                RETURNS TABLE (
                    size           numeric,
                    count_children bigint,
                    count_files    bigint,
                    count_folders  bigint
                )
                LANGUAGE 'plpgsql'

            AS \$BODY\$


                begin

                    RETURN QUERY (
                        with recursive obj_tree as (
                            SELECT
                                file_id,
                                file_size,
                                file_parent_id,
                                is_folder
                                --text(file_name) as  file_name,
                                --1 as depth
                            FROM {$tablePrefix}user_files
                            WHERE file_id = $1
                            UNION ALL
                            SELECT
                                t.file_id,
                                t.file_size,
                                t.file_parent_id,
                                t.is_folder
                                --concat_ws('/', ff.file_name, t.file_name),
                                --ff.depth + 1 AS depth
                            FROM {$tablePrefix}user_files as t
                            INNER JOIN obj_tree as ff on t.file_parent_id = ff.file_id
                            WHERE (_with_deleted OR t.is_deleted = 0)
                        )
                        SELECT
                          sum(file_size) as size,
                          count(*) as count_children,
                          sum(CASE WHEN (is_folder=1) THEN 0::INT ELSE 1::INT END) as count_files,
                          sum(CASE WHEN (is_folder=1) THEN 1::INT ELSE 0::INT END) as count_folders
                        FROM obj_tree
                        WHERE file_id != $1
                    );

                end;


            \$BODY\$;

            ALTER FUNCTION get_count_children_for(bigint, bool)
                OWNER TO {$userName};

        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190220_100144_change_function_get_count_children_for cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190220_100144_change_function_get_count_children_for cannot be reverted.\n";

        return false;
    }
    */
}
