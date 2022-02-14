<?php

use yii\db\Migration;

/**
 * Class m181116_150130_repair_to_fill_user_file_events_for_last_and_first_event_id
 */
class m181116_150130_repair_to_fill_user_file_events_for_last_and_first_event_id extends Migration
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

            -- FUNCTION: __repair_to_fill_user_file_events_for_last_and_first_event_id()

            -- DROP FUNCTION __repair_to_fill_user_file_events_for_last_and_first_event_id();

            CREATE OR REPLACE FUNCTION __repair_to_fill_user_file_events_for_last_and_first_event_id(
                )
                RETURNS bigint
                LANGUAGE 'plpgsql'

            AS \$BODY\$

                declare
                    _file record;
                    _count bigint;
					_test record;
					_last_id bigint;

					_test_count_res bigint;
					_is_exists bool;

                BEGIN

					_count = 0;
					_test_count_res = 1;
					_is_exists = true;
					_last_id = 0;

					WHILE _is_exists LOOP

							raise NOTICE '>> _last_id = %', _last_id;

							_test_count_res = 0;
							for _file in select * from (SELECT file_id
                    			FROM {$tablePrefix}user_files
                    			WHERE ((last_event_id IS NULL) OR (first_event_id IS NULL))
								AND (file_id > _last_id)
								ORDER BY file_id ASC
		                    	LIMIT 10000) as files
							loop

								_last_id = _file.file_id;
								-- raise NOTICE '>> file_id = %', _file.file_id;
								_test_count_res = _test_count_res + 1;
								_count = _count + 1;
								UPDATE {$tablePrefix}user_files
								SET
									last_event_id = (SELECT max(event_id) FROM {$tablePrefix}user_file_events WHERE file_id = _file.file_id),
									first_event_id = (SELECT min(event_id) FROM {$tablePrefix}user_file_events WHERE file_id = _file.file_id)
								WHERE file_id = _file.file_id;

							end loop;

							commit;
							raise NOTICE '>> Count records updated in this iterration = %', _test_count_res;
							raise NOTICE '>> Total count records updated after this iterration = %', _count;

							if _test_count_res = 0 then _is_exists = false; end if;

					END LOOP;

					raise NOTICE '>> Total count records updated = %', _count;

                    RETURN _count;
                END;

            \$BODY\$;

            ALTER FUNCTION __repair_to_fill_user_file_events_for_last_and_first_event_id()
                OWNER TO {$userName};

        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181116_150130_repair_to_fill_user_file_events_for_last_and_first_event_id cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181116_150130_repair_to_fill_user_file_events_for_last_and_first_event_id cannot be reverted.\n";

        return false;
    }
    */
}
