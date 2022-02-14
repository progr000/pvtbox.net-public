<?php

use yii\db\Migration;

/**
 * Class m200401_092436_create_function_get_participants_for_conference
 */
class m200401_092436_create_function_get_participants_for_conference extends Migration
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

            -- FUNCTION: get_participants_for_conference(bigint, bigint)

            -- DROP FUNCTION get_participants_for_conference(bigint, bigint);

            CREATE OR REPLACE FUNCTION get_participants_for_conference(
                __user_id bigint,
                __conference_id bigint)
                RETURNS TABLE(
                    participant_email citext,
                    user_id bigint,
                    user_enabled bigint
                )
                LANGUAGE 'plpgsql'

            AS \$BODY\$

				/*
				Выбирает всех возможных участников для конфы
				на основе таблиц conference_participants и user_colleagues
				*/
                begin

                    RETURN QUERY (

                        SELECT
                            ta.participant_email as participant_email,
                            ta.user_id as user_id,
                            sum(ta.user_enabled)::bigint as user_enabled
                        FROM (
                            SELECT
                                t1.participant_id    AS participant_id,
                                t1.participant_email AS participant_email,
                                t1.conference_id     AS conference_id,
                                t1.user_id           AS user_id,
                                (CASE WHEN (t1.conference_id = $2) THEN 1 ELSE 0 END)::SMALLINT AS user_enabled,
                                (CASE WHEN (t1.conference_id = $2) THEN t1.participant_status ELSE NULL END)::SMALLINT AS participant_status
                            FROM dl_conference_participants AS t1
                            INNER JOIN dl_user_conferences AS t2
                                ON (t1.conference_id = t2.conference_id) AND (t2.user_id = $1)
                            WHERE (t1.user_id != $1)
                            OR (t1.user_id IS NULL)

                            UNION ALL

                            SELECT
                                NULL               AS participant_id,
                                t1.colleague_email AS participant_email,
                                NULL               AS conference_id,
                                t1.user_id         AS user_id,
                                0::SMALLINT        AS user_enabled,
                                NULL::SMALLINT     AS participant_status
                            FROM dl_user_colleagues AS t1
                            INNER JOIN dl_user_collaborations AS t2
                                ON (t1.collaboration_id = t2.collaboration_id) AND (t2.user_id = $1)
                            WHERE (t1.user_id != $1)
                            OR (t1.user_id IS NULL)
                        ) AS ta
                        GROUP BY ta.participant_email, ta.user_id
                        ORDER BY user_enabled DESC,
                        --user_id ASC NULLS LAST,
                        participant_email ASC

					);

                end;


            \$BODY\$;

            ALTER FUNCTION get_participants_for_conference(bigint, bigint)
                OWNER TO {$userName};

        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //echo "m200401_092436_create_function_get_participants_for_conference cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200401_092436_create_function_get_participants_for_conference cannot be reverted.\n";

        return false;
    }
    */
}
