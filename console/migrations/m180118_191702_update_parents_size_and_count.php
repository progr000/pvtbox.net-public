<?php

use yii\db\Migration;

/**
 * Class m180118_191702_update_parents_size_and_count
 */
class m180118_191702_update_parents_size_and_count extends Migration
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

            CREATE OR REPLACE FUNCTION update_parents_size_and_count(
                id bigint,
                diff_size bigint,
                diff_count integer,
                separator character varying)
                RETURNS text
                LANGUAGE 'plpgsql'

            AS \$BODY\$

            declare
                _file record;
                _file_ids bigint[];
                _full_path text;
                _i integer;
            begin
                _full_path = '';
                _i = 0;
                for _file in SELECT * FROM (
                    with recursive obj_tree as (
                        SELECT
                            file_id,
                            file_parent_id,
                            text(file_name) as  file_name
                        FROM {$tablePrefix}user_files
                        WHERE file_id = id
                          UNION ALL
                        SELECT
                            t.file_id,
                            t.file_parent_id,
                            concat_ws(separator, t.file_name, ff.file_name)
                        FROM {$tablePrefix}user_files AS t
                        JOIN obj_tree ff on t.file_id = ff.file_parent_id
                    )
                    SELECT * FROM obj_tree WHERE file_id != id
                ) as files loop
                    _file_ids[_i] = _file.file_id;
                    if _file.file_parent_id = 0 then
                        _full_path = _file.file_name;
                    end if;
                    _i  = _i + 1;
                end loop;

                UPDATE {$tablePrefix}user_files SET
                    file_size = CASE WHEN (file_size + diff_size > 0) THEN (file_size + diff_size) ELSE 0 END,
                    folder_children_count = CASE WHEN (folder_children_count + diff_count > 0) THEN (folder_children_count + diff_count) ELSE 0 END
                WHERE file_id = ANY (_file_ids);

                return _full_path ;
            end;


            \$BODY\$;

            ALTER FUNCTION update_parents_size_and_count(bigint, bigint, integer, character varying)
                OWNER TO {$userName};
        ");


        /*
        Пример использования:
        SELECT * FROM update_parents_size_and_count(13761625, 1256,  1, '/');  // добавили файл с размером 1256
        SELECT * FROM update_parents_size_and_count(13761625,  558,  0, '/');   // изменили файл - его размер увеличен
        SELECT * FROM update_parents_size_and_count(13761625, -550,  0, '/');   // изменили файл - его размер уменьшен
        SELECT * FROM update_parents_size_and_count(13761625, -256, -1, '/'); // Удалили файл с таким размером
        SELECT * FROM update_parents_size_and_count(13761625,    0, -1, '/');    // удалили файл пустой
        и т.п.
        Вернет полный путь к файлу с переданным ИД
        Затем нужно в цикле пройти по всем реальным каталогам этого пути и обновить
        информацию о размере папки и количестве файлов в ней в ее служебном текстовом файле .dirInfoFile

        // Для проверки того что функция работает, можно использовать такой селект для просмотра записей после изменений
        with recursive obj_tree as (
            SELECT
                file_id,
                file_parent_id,
                file_name,
                file_size,
                folder_children_count
            FROM {$tablePrefix}user_files
            WHERE file_id = 13761625
              UNION ALL
            SELECT
                t.file_id,
                t.file_parent_id,
                t.file_name,
                t.file_size,
                t.folder_children_count
            FROM {$tablePrefix}user_files AS t
            JOIN obj_tree ff on t.file_id = ff.file_parent_id
        )
        SELECT * FROM obj_tree WHERE file_id != 13761625;
        */
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180118_191702_update_parents_size_and_count cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180118_191702_update_parents_size_and_count cannot be reverted.\n";

        return false;
    }
    */
}
