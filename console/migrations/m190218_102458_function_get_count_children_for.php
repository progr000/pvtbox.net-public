<?php

use yii\db\Migration;

/**
 * Class m190218_102458_function_get_count_children_for
 */
class m190218_102458_function_get_count_children_for extends Migration
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

            -- DROP FUNCTION get_count_children_for(bigint, bool);

            CREATE OR REPLACE FUNCTION get_count_children_for(
                _folder_id bigint,
                _with_deleted bool)
                RETURNS bigint
                LANGUAGE 'plpgsql'

            AS \$BODY\$


                declare
                    _count bigint;

                begin

                    with recursive obj_tree as (
                        SELECT
                          file_id,
                          file_parent_id,
                          is_folder,
                          text(file_name) as  file_name,
                          1 as depth
                        FROM {$tablePrefix}user_files
                        WHERE file_id = 24078406
                        UNION ALL
                        SELECT
                          t.file_id,
                          t.file_parent_id,
                          t.is_folder,
                          concat_ws('/', ff.file_name, t.file_name),
                          ff.depth + 1 AS depth
                        FROM {$tablePrefix}user_files as t
                        INNER JOIN obj_tree as ff on t.file_parent_id = ff.file_id
                        WHERE (_with_deleted OR t.is_deleted = 0)
                    )
                    SELECT count(*) INTO _count FROM obj_tree;
                    RETURN _count;
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
        echo "m190218_102458_function_get_count_children_for cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190218_102458_function_get_count_children_for cannot be reverted.\n";

        return false;
    }
    */
}
