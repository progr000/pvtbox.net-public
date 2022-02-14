<?php

use yii\db\Migration;

/**
 * Class m180409_094905_repair_date_with_zone_on_date_without_zone
 */
class m180409_094905_repair_date_with_zone_on_date_without_zone extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%admins}} ALTER COLUMN admin_created TYPE timestamp");
        $this->execute("ALTER TABLE {{%admins}} ALTER COLUMN admin_updated TYPE timestamp");

        $this->execute("ALTER TABLE {{%colleagues_reports}} ALTER COLUMN report_date TYPE timestamp");

        $this->execute("ALTER TABLE {{%news}} ALTER COLUMN news_created TYPE timestamp");
        $this->execute("ALTER TABLE {{%news}} ALTER COLUMN news_updated TYPE timestamp");

        $this->execute("ALTER TABLE {{%notifications}} ALTER COLUMN notif_date TYPE timestamp");

        $this->execute("ALTER TABLE {{%pages}} ALTER COLUMN page_created TYPE timestamp");
        $this->execute("ALTER TABLE {{%pages}} ALTER COLUMN page_updated TYPE timestamp");

        $this->execute("ALTER TABLE {{%paypal_pays}} ALTER COLUMN pp_updated TYPE timestamp");
        $this->execute("ALTER TABLE {{%paypal_pays}} ALTER COLUMN pp_updated TYPE timestamp");

        $this->execute("ALTER TABLE {{%remote_actions}} ALTER COLUMN action_init_time TYPE timestamp");
        $this->execute("ALTER TABLE {{%remote_actions}} ALTER COLUMN action_end_time TYPE timestamp");

        $this->execute("ALTER TABLE {{%sessions}} ALTER COLUMN sess_created TYPE timestamp");

        $this->execute("ALTER TABLE {{%software}} ALTER COLUMN software_created TYPE timestamp");
        $this->execute("ALTER TABLE {{%software}} ALTER COLUMN software_updated TYPE timestamp");

        $this->execute("ALTER TABLE {{%tikets}} ALTER COLUMN tiket_created TYPE timestamp");

        $this->execute("ALTER TABLE {{%tikets_messages}} ALTER COLUMN message_created TYPE timestamp");

        $this->execute("ALTER TABLE {{%transfers}} ALTER COLUMN transfer_created TYPE timestamp");
        $this->execute("ALTER TABLE {{%transfers}} ALTER COLUMN transfer_updated TYPE timestamp");

        $this->execute("ALTER TABLE {{%user_colleagues}} ALTER COLUMN colleague_invite_date TYPE timestamp");
        $this->execute("ALTER TABLE {{%user_colleagues}} ALTER COLUMN colleague_joined_date TYPE timestamp");

        $this->execute("ALTER TABLE {{%user_files}} ALTER COLUMN share_created TYPE timestamp");
        $this->execute("ALTER TABLE {{%user_files}} ALTER COLUMN share_lifetime TYPE timestamp");

        $this->execute("ALTER TABLE {{%user_files}} ALTER COLUMN file_created TYPE timestamp USING to_timestamp(file_created)::timestamp without time zone");
        $this->execute("ALTER TABLE {{%user_files}} ALTER COLUMN file_updated TYPE timestamp USING to_timestamp(file_updated)::timestamp without time zone");

        $this->execute("ALTER TABLE {{%user_node}} ALTER COLUMN node_created TYPE timestamp");
        $this->execute("ALTER TABLE {{%user_node}} ALTER COLUMN node_updated TYPE timestamp");

        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN user_created TYPE timestamp");
        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN user_updated TYPE timestamp");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180409_094905_repair_date_with_zone_on_date_without_zone cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180409_094905_repair_date_with_zone_on_date_without_zone cannot be reverted.\n";

        return false;
    }
    */
}
