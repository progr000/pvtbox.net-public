<?php

use yii\db\Migration;

/**
 * Class m200219_204732_software
 */
class m200219_204732_software extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';


        $this->db->pdo->exec("
            --
            -- Data for Name: {$tablePrefix}software; Type: TABLE DATA; Schema: first; Owner: -
            --

            DELETE FROM {$tablePrefix}software;
            INSERT INTO {$tablePrefix}software
            (software_type, software_description, software_file_name, software_url, software_program_type, software_version, software_created, software_updated, software_status, software_sort)
            VALUES
            ('windows', '(7+) x86, x64', '', 'https://installer.pvtbox.net/release/win/PvtboxSetup.exe', 'url', '', '2016-09-26 07:20:35', '2019-06-20 22:28:36', 1, 1),
            ('mac', '(10.12+) ', '', 'https://installer.pvtbox.net/release/osx/Pvtbox.dmg', 'url', '', '2017-08-31 03:30:53', '2019-08-29 04:49:22', 1, 2),

            ('debian', '(Debian amd64)', '', 'https://installer.pvtbox.net/release/deb/amd64/pvtbox_amd64.deb?v=debian', 'url', '', '2018-04-26 21:08:09', '2019-06-24 18:46:33', 1, 3),
            ('ubuntu', '(Ubuntu amd64)', '', 'https://installer.pvtbox.net/release/deb/amd64/pvtbox_amd64.deb?v=ubuntu', 'url', '', '2018-04-26 21:08:09', '2019-06-24 18:46:33', 1, 4),
            ('centos', '(CentOS 7.6+ amd64)', '', 'https://installer.pvtbox.net/release/centos/amd64/pvtbox_amd64.rpm', 'url', '', '2020-03-09 08:33:12', '2020-03-09 08:56:47', 1, 5),
            ('suse', '(OpenSUSE 15+ amd64)', '', 'https://installer.pvtbox.net/release/opensuse/amd64/pvtbox_amd64.rpm', 'url', '', '2020-03-09 08:35:11', '2020-03-09 08:57:01', 1, 6),

            ('ios', '(10.0+)', '', 'https://itunes.apple.com/us/app/private-box-sync/id1453436108?l=en&ls=1&mt=8', 'url', '', '2019-01-14 21:25:04', '2019-06-03 07:39:57', 1, 15),
            ('android', '(5+)', '', 'https://play.google.com/store/apps/details?id=net.pvtbox.android&pcampaignid=MKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1', 'url', '', '2018-04-26 21:05:24', '2019-06-03 07:40:05', 1, 16),
            ('portable', 'for Windows and Mac', '', 'https://installer.pvtbox.net/release/Pvtbox-portable.zip', 'url', '', '2019-08-28 13:53:46', '2019-11-13 10:20:24', 1, 17);
        ");

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200219_204732_software cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200219_204732_software cannot be reverted.\n";

        return false;
    }
    */
}
