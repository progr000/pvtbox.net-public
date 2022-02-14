<?php

use yii\db\Migration;

/**
 * Class m190312_214211_without_time_zone_alter
 */
class m190312_214211_without_time_zone_alter extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%admins}} ALTER COLUMN admin_created TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%admins}} ALTER COLUMN admin_updated TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%colleagues_reports}} ALTER COLUMN report_date TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%cron_info}} ALTER COLUMN task_last_start TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%cron_info}} ALTER COLUMN task_last_finish TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%mailq}} ALTER COLUMN mail_created TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%news}} ALTER COLUMN news_created TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%news}} ALTER COLUMN news_updated TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%notifications}} ALTER COLUMN notif_date TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%pages}} ALTER COLUMN page_created TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%pages}} ALTER COLUMN page_updated TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%paypal_pays}} ALTER COLUMN pp_created TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%paypal_pays}} ALTER COLUMN pp_updated TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%queued_events}} ALTER COLUMN job_created TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%queued_events}} ALTER COLUMN job_started TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%queued_events}} ALTER COLUMN job_finished TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%redis_safe}} ALTER COLUMN rs_created TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%remote_actions}} ALTER COLUMN action_init_time TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%remote_actions}} ALTER COLUMN action_end_time TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%sessions}} ALTER COLUMN sess_created TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%software}} ALTER COLUMN software_created TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%software}} ALTER COLUMN software_updated TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%tikets}} ALTER COLUMN tiket_created TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%tikets_messages}} ALTER COLUMN message_created TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%traffic_log}} ALTER COLUMN record_created TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%transfers}} ALTER COLUMN transfer_created TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%transfers}} ALTER COLUMN transfer_updated TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%user_alerts_log}} ALTER COLUMN alert_created TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%user_collaborations}} ALTER COLUMN collaboration_created TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%user_colleagues}} ALTER COLUMN colleague_invite_date TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%user_colleagues}} ALTER COLUMN colleague_joined_date TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%user_files}} ALTER COLUMN file_created TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%user_files}} ALTER COLUMN file_updated TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%user_files}} ALTER COLUMN share_created TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%user_files}} ALTER COLUMN share_lifetime TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%user_licenses}} ALTER COLUMN lic_start TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%user_licenses}} ALTER COLUMN lic_end TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%user_node}} ALTER COLUMN node_created TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%user_node}} ALTER COLUMN node_updated TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%user_payments}} ALTER COLUMN pay_date TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%user_payments}} ALTER COLUMN invoice_created TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%user_payments}} ALTER COLUMN invoice_expires TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%user_payments}} ALTER COLUMN invoice_changed TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%user_payments}} ALTER COLUMN license_expire TYPE timestamp without time zone");

        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN user_created TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN user_updated TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN previous_license_business_finish TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN license_expire TYPE timestamp without time zone");
        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN payment_init_date TYPE timestamp without time zone");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190312_214211_without_time_zone_alter cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190312_214211_without_time_zone_alter cannot be reverted.\n";

        return false;
    }
    */
}
