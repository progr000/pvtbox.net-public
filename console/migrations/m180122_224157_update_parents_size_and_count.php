<?php

use yii\db\Migration;

/**
 * Class m180122_224157_update_parents_size_and_count
 */
class m180122_224157_update_parents_size_and_count extends Migration
{
    /**
     * @inheritdoc
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

            DROP FUNCTION update_parents_size_and_count(bigint, bigint, integer, character varying);

            CREATE OR REPLACE FUNCTION update_parents_size_and_count(
                id bigint,
                diff_size bigint,
                diff_count integer,
                separator character varying)
                RETURNS TABLE(full_path text, file_size bigint, folder_children_count integer)
                LANGUAGE 'plpgsql'

            AS \$BODY\$

            declare

            begin

            return query
            WITH RECURSIVE obj_tree as (
             SELECT
              file_id,
              file_parent_id,
              text(file_name) as  file_name
             FROM {$tablePrefix}user_files
             WHERE file_id = id  --id of file child
             UNION ALL
             SELECT
              t.file_id,
              t.file_parent_id,
              concat_ws(separator, t.file_name, ff.file_name)
             FROM {$tablePrefix}user_files AS t
             JOIN obj_tree ff on t.file_id = ff.file_parent_id
            ),
            ----additional CTE-------------
            full_path as (
              SELECT file_name FROM obj_tree WHERE file_parent_id=0
            )
            -------------------------------
            UPDATE {$tablePrefix}user_files SET
             file_size = CASE WHEN ({$tablePrefix}user_files.file_size + diff_size > 0) THEN ({$tablePrefix}user_files.file_size + diff_size) ELSE 0 END,
             folder_children_count = CASE WHEN ({$tablePrefix}user_files.folder_children_count + diff_count > 0) THEN ({$tablePrefix}user_files.folder_children_count + diff_count) ELSE 0 END
            FROM obj_tree as t2, full_path as fp --------------add here the new CTE-------------------
            --WHERE ({$tablePrefix}user_files.file_id IN (SELECT file_id FROM obj_tree WHERE file_id != 13514121))
            WHERE ({$tablePrefix}user_files.file_id != id)  --id of file child
            AND {$tablePrefix}user_files.file_id=t2.file_id -- джойним вместо вложенного селекта
            RETURNING
            concat(replace(fp.file_name, t2.file_name, ''), {$tablePrefix}user_files.file_name) as full_path,
            {$tablePrefix}user_files.file_size as file_size,
            {$tablePrefix}user_files.folder_children_count as folder_children_count;
            --, {$tablePrefix}user_files.file_id, {$tablePrefix}user_files.file_name, t2.file_name as path, t2.file_parent_id

            end;

            \$BODY\$;


            ALTER FUNCTION update_parents_size_and_count(bigint, bigint, integer, character varying)
                OWNER TO {$userName};
        ");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180122_224157_update_parents_size_and_count cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180122_224157_update_parents_size_and_count cannot be reverted.\n";

        return false;
    }
    */
}
