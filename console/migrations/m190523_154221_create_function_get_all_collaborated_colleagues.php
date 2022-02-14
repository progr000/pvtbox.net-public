<?php

use yii\db\Migration;

/**
 * Class m190523_154221_create_function_get_all_collaborated_colleagues
 */
class m190523_154221_create_function_get_all_collaborated_colleagues extends Migration
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
            SET search_path TO {$schema}, public;

            -- FUNCTION: get_all_collaborated_colleagues(bigint)

            -- DROP FUNCTION get_all_collaborated_colleagues(bigint);

            CREATE OR REPLACE FUNCTION get_all_collaborated_colleagues(
                __user_id bigint)
                RETURNS TABLE(
                    colleague_id bigint,
                    colleague_status {$tablePrefix}user_colleagues_colleague_status,
                    colleague_permission {$tablePrefix}user_colleagues_colleague_permission,
                    colleague_invite_date timestamp without time zone,
                    colleague_joined_date timestamp without time zone,
                    colleague_email citext, user_id bigint,
                    owner_collaboration_user_id bigint,
                    is_owner integer,
                    license_type text,
                    awaiting_permissions integer
                )
                LANGUAGE 'plpgsql'

                COST 100
                VOLATILE
                ROWS 1000
            AS \$BODY\$

				/*
				Выбирает всех коллег для бизнес админа объединяя с теми что в таблице {$tablePrefix}user_licenses
				*/
                begin

                    RETURN QUERY (

						SELECT
							0 as colleague_id,
							'invited' as colleague_status,
							'view' as colleague_permission,
							t1.lic_start as colleague_invite_date,
							t1.lic_start as colleague_joined_date,
							t1.lic_colleague_email as colleague_email,
							t3.user_id as user_id,
							t3.license_business_from as owner_collaboration_user_id,
							0 as is_owner,
							t3.license_type::text as license_type,
							0 as awaiting_permissions
						FROM {$tablePrefix}user_licenses as t1
						INNER JOIN {$tablePrefix}users as t3 ON t1.lic_colleague_email = t3.user_email
						WHERE (t1.lic_owner_user_id = $1)
						AND (t1.lic_colleague_email NOT IN (SELECT _colleague_email FROM get_real_collaborated_colleagues($1)))

						UNION

						SELECT * FROM get_real_collaborated_colleagues($1)

						ORDER BY is_owner DESC, colleague_email ASC

					);

                end;


            \$BODY\$;

            ALTER FUNCTION get_all_collaborated_colleagues(bigint)
                OWNER TO {$userName};

        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190523_154221_create_function_get_all_collaborated_colleagues cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190523_154221_create_function_get_all_collaborated_colleagues cannot be reverted.\n";

        return false;
    }
    */
}
