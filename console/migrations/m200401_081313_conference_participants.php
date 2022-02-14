<?php

use yii\db\Migration;

/**
 * Class m200401_081313_conference_participants
 */
class m200401_081313_conference_participants extends Migration
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

            CREATE SEQUENCE {$schema}.{$tablePrefix}conference_participants_record_id_seq
                INCREMENT 1
                START 1
                MINVALUE 1
                MAXVALUE 9223372036854775807
                CACHE 1;

            ALTER SEQUENCE {$schema}.{$tablePrefix}conference_participants_record_id_seq
                OWNER TO {$userName};

            CREATE TABLE {$schema}.{$tablePrefix}conference_participants
            (
                participant_id bigint PRIMARY KEY NOT NULL DEFAULT nextval('{$tablePrefix}conference_participants_record_id_seq'::regclass),
                participant_status smallint NOT NULL DEFAULT 0,
                participant_invite_date TIMESTAMP WITHOUT TIME ZONE,
                participant_joined_date TIMESTAMP WITHOUT TIME ZONE,
                participant_last_activity TIMESTAMP WITHOUT TIME ZONE,
                participant_email PUBLIC.CITEXT NOT NULL,
                conference_id bigint NOT NULL,
                user_id bigint,
                CONSTRAINT fk_conference_participants_conference_id FOREIGN KEY (conference_id)
                    REFERENCES {$tablePrefix}user_conferences (conference_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,
                CONSTRAINT fk_conference_participants_user_id FOREIGN KEY (user_id)
                    REFERENCES {$tablePrefix}users (user_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE CASCADE
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;


            CREATE UNIQUE INDEX conference_participants_participant_email
                ON {$schema}.{$tablePrefix}conference_participants
                USING BTREE (participant_email, conference_id);

            CREATE UNIQUE INDEX conference_participants_participant_email_user_id_conference_id
                ON {$schema}.{$tablePrefix}conference_participants
                USING BTREE (participant_email, user_id, conference_id);

            CREATE UNIQUE INDEX conference_participants_user_id_conference_id
                ON {$schema}.{$tablePrefix}conference_participants
                USING BTREE (user_id, conference_id);

            CREATE INDEX conference_participants_conference_id
                ON {$schema}.{$tablePrefix}conference_participants
                USING BTREE (conference_id);


            ALTER TABLE {$tablePrefix}conference_participants
                OWNER to {$userName};
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200401_081313_conference_participants cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200401_081313_conference_participants cannot be reverted.\n";

        return false;
    }
    */
}
