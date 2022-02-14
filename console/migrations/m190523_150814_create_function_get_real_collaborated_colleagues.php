<?php

use yii\db\Migration;

/**
 * Class m190523_150814_create_function_get_real_collaborated_colleagues
 */
class m190523_150814_create_function_get_real_collaborated_colleagues extends Migration
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

            -- FUNCTION: get_real_collaborated_colleagues(bigint)

            -- DROP FUNCTION get_real_collaborated_colleagues(bigint);

            CREATE OR REPLACE FUNCTION get_real_collaborated_colleagues(
                __user_id bigint)
                RETURNS TABLE(
                    _colleague_id bigint,
                    _colleague_status {$tablePrefix}user_colleagues_colleague_status,
                    _colleague_permission {$tablePrefix}user_colleagues_colleague_permission,
                    _colleague_invite_date timestamp without time zone,
                    _colleague_joined_date timestamp without time zone,
                    _colleague_email citext,
                    _user_id bigint,
                    _owner_collaboration_user_id bigint,
                    _is_owner integer,
                    _license_type text,
                    _awaiting_permissions integer
                )
                LANGUAGE 'plpgsql'

                COST 100
                VOLATILE
                ROWS 1000
            AS \$BODY\$

				/*
				Выбирает всех коллег для бизнес админа (для его админ панели)
				в выборку включаются все кто есть в таблице коллег
				как те кого пригласил админ так и те кто его пригласил
				Так же включается в список и сам админ.
				Но эта функция не выбирает таких коллег которых нет в {$tablePrefix}user_colleagues
				но эти коллеги могли покинуть колабу а сама лицензия не списана.
				Поэтому будет добавлена вторая функция которая будет использовать данные этой функции
				для объединения выбрки реальных коллег и тех которые имеются в {$tablePrefix}user_licenses
				*/
                begin

                    RETURN QUERY (

						SELECT
                			max(colleague_id) as _colleague_id,
                			max(colleague_status) as _colleague_status,
                			min(colleague_permission) as _colleague_permission,
                			max(colleague_invite_date) as _colleague_invite_date,
                			max(colleague_joined_date) as _colleague_joined_date,
			                colleague_email as _colleague_email,
             			  	user_id as _user_id,
             			   	owner_collaboration_user_id as _owner_collaboration_user_id,
                			max(is_owner) as _is_owner,
                			max(license_type) as _license_type,
                			max(awaiting_permissions) as _awaiting_permissions
                		FROM (
                    		SELECT
                      			0 as colleague_id,
                      			'joined' as colleague_status,
                      			'owner' as colleague_permission,
                      			user_created as colleague_invite_date,
                      			user_created as colleague_joined_date,
                      			user_email as colleague_email,
                      			user_id,
                      			0 as collaboration_id,
                      			user_id as owner_collaboration_user_id,
                      			1 as is_owner,
                      			license_type,
                      			0 as awaiting_permissions
                    		FROM {$tablePrefix}users
                    		WHERE user_id = $1

                    		UNION

                    		SELECT DISTINCT ON (t1.colleague_email, is_owner)
                      			t1.*,
                      			t3.license_business_from as owner_collaboration_user_id,
                      			(CASE WHEN (t1.colleague_permission = 'owner') THEN 1 ELSE 0 END) as is_owner,
                      			t3.license_type,
                      			(CASE WHEN (t2.file_uuid IS NULL) THEN 1 ELSE 0 END) as awaiting_permissions
                    		FROM {$tablePrefix}user_colleagues as t1
                    		INNER JOIN {$tablePrefix}user_collaborations as t2 ON t1.collaboration_id = t2.collaboration_id
                    		LEFT JOIN {$tablePrefix}users as t3 ON t1.user_id = t3.user_id
                    		WHERE (t2.user_id = $1)
                    		AND (t1.colleague_permission != 'owner')
                    		AND (t1.colleague_status != 'queued_del')

                    		UNION

                    		SELECT
                        		0 as colleague_id,
                        		t1.colleague_status,
                        		t1.colleague_permission,
                        		t1.colleague_invite_date,
                        		t1.colleague_joined_date,
                        		t3.user_email as colleague_email,
                        		t3.user_id,
                        		t1.collaboration_id,
                        		t3.license_business_from as owner_collaboration_user_id,
                        		0 as is_owner,
                        		t3.license_type,
                        		0 as awaiting_permissions
                    		FROM {$tablePrefix}user_colleagues as t1
                    		INNER JOIN {$tablePrefix}user_collaborations as t2 ON t1.collaboration_id = t2.collaboration_id
                    		INNER JOIN {$tablePrefix}users as t3 ON t2.user_id = t3.user_id
                    		WHERE (t2.user_id <> $1)
                    		AND (t1.user_id = $1)
                    		AND (t1.colleague_permission != 'owner')
                    		AND (t1.colleague_status = 'joined')
                		) as res
                		GROUP BY _user_id, _colleague_email, _owner_collaboration_user_id
                		ORDER BY _is_owner DESC, _colleague_email ASC, _colleague_status DESC

					);

                end;


            \$BODY\$;

            ALTER FUNCTION get_real_collaborated_colleagues(bigint)
                OWNER TO {$userName};

        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190523_150814_create_function_get_real_collaborated_colleagues cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190523_150814_create_function_get_real_collaborated_colleagues cannot be reverted.\n";

        return false;
    }
    */
}
