<?php

use yii\db\Migration;

/**
 * Class m180810_114939_function_verify_path_length
 */
class m180810_114939_function_verify_path_length extends Migration
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

            CREATE OR REPLACE FUNCTION compare_length(
                path_length integer,
                max_path_length integer,
                is_debug boolean)
                RETURNS boolean
                LANGUAGE 'plpgsql'

            AS \$BODY\$

            begin
              if is_debug then
                raise NOTICE '>> path_length: %, max_path_length: %',
                             path_length, max_path_length;
              end if;
              if path_length > max_path_length then
                raise EXCEPTION 'MAX_PATH_LENGTH_EXCEEDED' using errcode = '50001';
              end if;
              return True;
            end;

            \$BODY\$;

            ALTER FUNCTION compare_length(integer, integer, boolean)
                OWNER TO {$userName};


            -----------------------------------------------------

            CREATE OR REPLACE FUNCTION verify_path_length(
                id bigint,
                parent_id bigint,
                new_name character varying,
                separator character varying,
                max_path_length integer,
                include_deleted boolean,
                is_debug boolean)
                RETURNS boolean
                LANGUAGE 'plpgsql'

            AS \$BODY\$

            declare
               parent_path_length integer;
               res integer;
            begin
                if is_debug then
                    raise NOTICE 'id: %', id;
                    raise NOTICE 'parent_id: %', parent_id;
                    raise NOTICE 'new_name: %', new_name;
                    raise NOTICE 'separator: %', separator;
                    raise NOTICE 'max_path_length: %', max_path_length;
                end if;

                parent_path_length := octet_length(coalesce(get_full_path(parent_id, separator), ''));
                if is_debug then
                    raise NOTICE '>> parent_path_length: %', parent_path_length;
                end if;

                begin

                    with recursive obj_tree as (
                        select
                            f1.file_id,
                            f1.file_parent_id,
                            concat_ws(separator, '', coalesce(new_name, f1.file_name)) as file_path,
                            compare_length(
                                path_length => octet_length(concat_ws(separator, '', coalesce(new_name, f1.file_name))),
                                max_path_length => (max_path_length - parent_path_length),
                                is_debug => is_debug
                            ) as is_checked
                            from {$tablePrefix}user_files f1
                            where f1.file_id = id  -- input parameter
                            and (include_deleted or f1.is_deleted = 0)
                        union all
                        select
                            f2.file_id,
                            f2.file_parent_id,
                            concat_ws(separator, tt.file_path, f2.file_name) as file_path,
                            compare_length(
                                path_length => octet_length(concat_ws(separator, tt.file_path, f2.file_name)),
                                max_path_length => (max_path_length - parent_path_length),
                                is_debug => is_debug
                            ) as is_checked
                            from {$tablePrefix}user_files f2
                            join obj_tree tt on tt.file_id = f2.file_parent_id
                            where (include_deleted or f2.is_deleted = 0)
                        )
                        select count(*) into res from obj_tree;

                    exception when sqlstate '50001' then
                        return false;
                    end;

                    if is_debug then
                    raise NOTICE '>> count: %', res;
                    end if;

                return true;
            end;

            \$BODY\$;

            ALTER FUNCTION verify_path_length(bigint, bigint, character varying, character varying, integer, boolean, boolean)
                OWNER TO {$userName};

        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180810_114939_function_verify_path_length cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180810_114939_function_verify_path_length cannot be reverted.\n";

        return false;
    }
    */
}
