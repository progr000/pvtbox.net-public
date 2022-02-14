<?php

use yii\db\Migration;

/**
 * Class m181115_204102_create_function_get_all_children_for
 */
class m181115_204102_create_function_get_all_children_for extends Migration
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

            CREATE OR REPLACE FUNCTION get_all_children_for(folder_id bigint)
                RETURNS TABLE(file_id bigint)
                LANGUAGE 'plpgsql'

            AS \$BODY\$

            BEGIN

                    RETURN QUERY (with recursive obj_tree as (
                        SELECT
                            t1.file_id,
                            t1.file_parent_id
                        FROM {$tablePrefix}user_files as t1
                        WHERE t1.file_id = $1
                        UNION ALL
                        SELECT
                            t2.file_id,
                            t2.file_parent_id
                        FROM {$tablePrefix}user_files AS t2
                        JOIN obj_tree ff on ff.file_id = t2.file_parent_id
                    )
                    SELECT obj_tree.file_id FROM obj_tree WHERE obj_tree.file_id != $1);

            END;

            \$BODY\$;

            ALTER FUNCTION get_all_children_for(bigint)
                OWNER TO {$userName};
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181115_204102_create_function_get_all_children_for cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181115_204102_create_function_get_all_children_for cannot be reverted.\n";

        return false;
    }
    */
}
