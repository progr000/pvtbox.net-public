<?php

use yii\db\Migration;

/**
 * Class m181116_134747_user_file_events_trigger
 */
class m181116_134747_user_file_events_trigger extends Migration
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

            CREATE FUNCTION user_file_events_trigger() RETURNS trigger AS \$user_file_events_trigger\$

                BEGIN

                    IF NEW.event_type <> 0 THEN

                        -- update only user.last_event_id
                        UPDATE {$tablePrefix}user_files
                        SET
                            last_event_id = NEW.event_id
                        WHERE file_id = NEW.file_id;

                    ELSE

                        -- update user.last_event_id and user.first_event_id
                        UPDATE {$tablePrefix}user_files
                        SET
                            last_event_id = NEW.event_id,
                            first_event_id = NEW.event_id
                        WHERE file_id = NEW.file_id;

                    END IF;

                    RETURN NEW;
                END;


            \$user_file_events_trigger\$ LANGUAGE plpgsql;


            ALTER FUNCTION user_file_events_trigger()
                OWNER TO {$userName};


            CREATE TRIGGER user_file_events_trigger AFTER INSERT ON {$tablePrefix}user_file_events
                FOR EACH ROW EXECUTE PROCEDURE user_file_events_trigger();
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181116_134747_user_file_events_trigger cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181116_134747_user_file_events_trigger cannot be reverted.\n";

        return false;
    }
    */
}
