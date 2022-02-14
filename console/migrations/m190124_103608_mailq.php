<?php

use yii\db\Migration;

/**
 * Class m190124_103608_mailq
 */
class m190124_103608_mailq extends Migration
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

            CREATE SEQUENCE {$schema}.{$tablePrefix}mailq_mail_id_seq
                INCREMENT 1
                START 1
                MINVALUE 1
                MAXVALUE 9223372036854775807
                CACHE 1;

            ALTER SEQUENCE {$schema}.{$tablePrefix}mailq_mail_id_seq
                OWNER TO {$userName};

            CREATE TABLE {$schema}.{$tablePrefix}mailq
            (
                mail_id bigint NOT NULL DEFAULT nextval('{$tablePrefix}mailq_mail_id_seq'::regclass),
                mail_created timestamp without time zone NOT NULL,
                mail_from citext COLLATE pg_catalog.\"default\" NOT NULL,
                mail_to citext COLLATE pg_catalog.\"default\" NOT NULL,
                mail_reply_to citext COLLATE pg_catalog.\"default\",
                mail_subject citext COLLATE pg_catalog.\"default\" NOT NULL,
                mail_body citext COLLATE pg_catalog.\"default\" NOT NULL,
                mailer_letter_id character varying(32) COLLATE pg_catalog.\"default\",
                mailer_answer citext COLLATE pg_catalog.\"default\" NOT NULL,
                mailer_letter_status character varying(32) COLLATE pg_catalog.\"default\" NOT NULL,
                user_id bigint,
                node_id bigint,
                template_key character varying(100) COLLATE pg_catalog.\"default\",
                CONSTRAINT idx_mailq_primary PRIMARY KEY (mail_id),
                CONSTRAINT fk_mailq_user_id FOREIGN KEY (user_id)
                    REFERENCES {$tablePrefix}users (user_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE SET NULL,
                CONSTRAINT fk_mailq_node_id FOREIGN KEY (node_id)
                    REFERENCES {$tablePrefix}user_node (node_id) MATCH SIMPLE
                    ON UPDATE CASCADE
                    ON DELETE SET NULL
            )
            WITH (
                OIDS = FALSE
            )
            TABLESPACE pg_default;

            ALTER TABLE {$tablePrefix}mailq
                OWNER to {$userName};

            COMMENT ON TABLE {$tablePrefix}mailq
                IS 'Info about letter that sent via SMTP-mailer';

            COMMENT ON COLUMN {$tablePrefix}mailq.mail_id
                IS 'ID';

            COMMENT ON COLUMN {$tablePrefix}mailq.mail_created
                IS 'Date';

            COMMENT ON COLUMN {$tablePrefix}mailq.user_id
                IS 'UserID link to users.user_id';

            COMMENT ON COLUMN {$tablePrefix}mailq.node_id
                IS 'NodeID link to user_node.node_id';

            COMMENT ON COLUMN {$tablePrefix}mailq.mailer_letter_id
                IS 'Unique id of letter on mailer system';

            COMMENT ON COLUMN {$tablePrefix}mailq.mailer_answer
                IS 'Mailer answer on try letter send';

            COMMENT ON COLUMN {$tablePrefix}mailq.mailer_letter_status
                IS 'Letter status on mailer system';

            CREATE UNIQUE INDEX idx_mailq_mailer_letter_id
                ON {$tablePrefix}mailq USING btree
                (mailer_letter_id COLLATE pg_catalog.\"default\")
                TABLESPACE pg_default;

            CREATE INDEX idx_mailq_mailer_letter_status
                ON {$tablePrefix}mailq USING btree
                (mailer_letter_status COLLATE pg_catalog.\"default\")
                TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190124_103608_mailq cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190124_103608_mailq cannot be reverted.\n";

        return false;
    }
    */
}
