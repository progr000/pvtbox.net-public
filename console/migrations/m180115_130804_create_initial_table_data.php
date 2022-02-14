<?php

use yii\db\Migration;

/**
 * Class m180115_130804_create_initial_table_data
 */
class m180115_130804_create_initial_table_data extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';


        $this->db->pdo->exec("
--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.6
-- Dumped by pg_dump version 9.6.6

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

-- SET search_path = first, public;

--
-- Data for Name: {$tablePrefix}admins; Type: TABLE DATA; Schema: first; Owner: -
--

INSERT INTO {$tablePrefix}admins (admin_id, admin_name, admin_email, auth_key, password_hash, password_reset_token, admin_created, admin_updated, admin_status) VALUES (2, 'admin', 'admin@pvtbox.net', 'rYx6RKkTSmAtxHEiFavtGFyxpUxm2Ykt', '\$2y\$13\$gyqh6HREvtbCSuTNuk2kIORs/PfTR9XdtHmheN.KETbPO1BaZAQLS', NULL, '2015-11-18 15:59:48+02', '2015-11-18 15:59:54+02', 1);


--
-- Name: {$tablePrefix}admins_admin_id_seq; Type: SEQUENCE SET; Schema: first; Owner: -
--

SELECT pg_catalog.setval('{$tablePrefix}admins_admin_id_seq', 2, true);


--
-- Data for Name: {$tablePrefix}licenses; Type: TABLE DATA; Schema: first; Owner: -
--

INSERT INTO {$tablePrefix}licenses (license_id, license_type, license_description, license_limit_bytes, license_limit_days, license_limit_nodes) VALUES (1, 'FREE_DEFAULT', 'Бесплатная', 104857600, 0, 3);
INSERT INTO {$tablePrefix}licenses (license_id, license_type, license_description, license_limit_bytes, license_limit_days, license_limit_nodes) VALUES (2, 'FREE_TRIAL', 'Пробная на 30 дней', 0, 14, 0);
INSERT INTO {$tablePrefix}licenses (license_id, license_type, license_description, license_limit_bytes, license_limit_days, license_limit_nodes) VALUES (3, 'PAYED_PROFESSIONAL', 'Платная (Pro)', 0, 0, 5);
INSERT INTO {$tablePrefix}licenses (license_id, license_type, license_description, license_limit_bytes, license_limit_days, license_limit_nodes) VALUES (4, 'PAYED_BUSINESS_ADMIN', 'Бизнес версия (Admin)', 0, 0, 0);
INSERT INTO {$tablePrefix}licenses (license_id, license_type, license_description, license_limit_bytes, license_limit_days, license_limit_nodes) VALUES (5, 'PAYED_BUSINESS_USER', 'Бизнес версия (User)', 0, 0, 0);


--
-- Name: {$tablePrefix}licenses_license_id_seq; Type: SEQUENCE SET; Schema: first; Owner: -
--

SELECT pg_catalog.setval('{$tablePrefix}licenses_license_id_seq', 4, true);


--
-- Data for Name: {$tablePrefix}mail_templates; Type: TABLE DATA; Schema: first; Owner: -
--

INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (1, 'downloadMobile', 'en', 'robot@null.null', 'robot', 'Link for downloading', '<div class=\"download-mobile\">
    <p>Hello,</p>

    <p>Follow the link below to download and install the application:</p>

  <p><a href=\"{{download-app-url}}\">{{download-app-url}}</a></p>
</div>', 'Hello,

Follow the link below to download and install the application:

{{download-app-url}}');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (2, 'downloadMobile', 'de', 'robot@null.null', 'null', 'null', 'null', 'null');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (3, 'downloadMobile', 'fr', 'robot@null.null', 'null', 'null', 'null', 'null');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (6, 'newRegister', 'en', 'robot@null.null', 'robot', 'Confirm registration on site {{app_name}}', '<div class=\"password-reset\">
    <p>Hello {{user_name}},</p>

    <p>Follow the link below to confirm your registration:</p>

  <p><a href=\"{{confirm-registration-url}}\">{{confirm-registration-url}}</a></p>
</div>', 'Hello {{user_name}},

Follow the link below to confirm your registration:

{{confirm-registration-url}}');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (7, 'newRegister', 'de', 'robot@null.null', 'null', 'null', 'null', 'null');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (8, 'newRegister', 'fr', 'robot@null.null', 'null', 'null', 'null', 'null');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (11, 'passwordChange', 'en', 'robot@null.null', 'robot', 'Password Change for {{app_name}}', '<div class=\"password-reset\">
    <p>Hello {{user_name}},</p>

    <p>Follow the link below to change your password:</p>

  <p><a href=\"{{change-password-url}}\">{{change-password-url}}</a></p>
</div>', 'Hello {{user_name}},

Follow the link below to change your password:

{{change-password-url}}
');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (12, 'passwordChange', 'de', 'robot@null.null', 'null', 'null', 'null', 'null');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (13, 'passwordChange', 'fr', 'robot@null.null', 'null', 'null', 'null', 'null');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (16, 'passwordReset', 'en', 'robot@null.null', 'robot', 'Password reset for {{app_name}}', '<div class=\"password-reset\">
    <p>Hello {{user_name}},</p>

    <p>Follow the link below to reset your password:</p>

    <p><a href=\"{{reset-password-url}}\">{{reset-password-url}}</a></p>
</div>', 'Hello {{user_name}},

Follow the link below to reset your password:

{{reset-password-url}}');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (17, 'passwordReset', 'de', 'robot@null.null', 'null', 'null', 'null', 'null');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (18, 'passwordReset', 'fr', 'robot@null.null', 'null', 'null', 'null', 'null');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (21, 'setupDevices', 'en', 'robot@null.null', 'null', 'null', 'null', 'null');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (22, 'setupDevices', 'de', 'robot@null.null', 'null', 'null', 'null', 'null');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (23, 'setupDevices', 'fr', 'robot@null.null', 'null', 'null', 'null', 'null');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (26, 'shareSendToEmail', 'en', 'robot@null.null', 'null', 'Отправка ссылки на шару на емейл', 'Отправка ссылки на шару на емейл', 'Отправка ссылки на шару на емейл');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (27, 'shareSendToEmail', 'de', 'robot@null.null', 'null', 'Отправка ссылки на шару на емейл', 'Отправка ссылки на шару на емейл', 'Отправка ссылки на шару на емейл');
INSERT INTO {$tablePrefix}mail_templates (template_id, template_key, template_lang, template_from_email, template_from_name, template_subject, template_body_html, template_body_text) VALUES (28, 'shareSendToEmail', 'fr', 'robot@null.null', 'null', 'Отправка ссылки на шару на емейл', 'Отправка ссылки на шару на емейл', 'Отправка ссылки на шару на емейл');


--
-- Name: {$tablePrefix}mail_templates_template_id_seq; Type: SEQUENCE SET; Schema: first; Owner: -
--

SELECT pg_catalog.setval('{$tablePrefix}mail_templates_template_id_seq', 28, true);


--
-- Data for Name: {$tablePrefix}pages; Type: TABLE DATA; Schema: first; Owner: -
--

INSERT INTO {$tablePrefix}pages (page_id, page_created, page_updated, page_status, page_lang, page_title, page_name, page_alias, page_keywords, page_description, page_text) VALUES (1, '2016-09-29 17:24:25+03', '2017-11-03 15:01:46+02', 1, 'en', 'Возможности', 'Возможности', '_feature_', '', '', '<!-- .features -->
<div class=\"features\">
<div class=\"features__cont\">
<div class=\"title\">
<h2>Private Box Features</h2>
</div>

<div class=\"features__text\">
<p>Private Box обладает функциями присущими продвинутым традиционным облачным сервисам, в тоже время благодаря децентрализованной структуре хранения данных и надежному шифрованию обеспечивает дополнительные возможности для вас и вашего бизнеса.</p>
</div>

<div class=\"features__block\">
<div class=\"features__box features__box--confidentiality\" id=\"confidentiality\">
<div class=\"features__box-cont\">
<h4>Конфиденциальность</h4>

<p>Устанавливая Private Box на свои доверенные устройства(PC, mac, server, tablet, etc) вы храните информацию только на своих устройствах, а следовательно достигается наивысшая степень конфиденциальности. Отныне вы сами хозяин своей информации.</p>
</div>
</div>

<div class=\"features__box features__box--security\" id=\"security\">
<div class=\"features__box-cont\">
<h4>Безопасность</h4>

<p>В Private Box встроено end-2-end шифрование, это означает что данные нельзя перехватить, прослушать или подменить по пути следования, они зашифрованы. Данные шифруются по алгоритмам RSA, ECDSA, TLS с длиной ключа 2048 бит. Никто кроме вас не может получить доступа к передаваемой информации.</p>
</div>
</div>

<div class=\"features__box features__box--speed\" id=\"speed\">
<div class=\"features__box-cont\">
<h4>Скорость</h4>

<p>При передаче или синхронизации файлов между устройствами, данные не просто передаются между ними по кратчайшему пути, что обеспечивает преимущество в скорости, и дополнительно к этому, ввиду децентрализованной структуры, каждое ваше подключенное устройство одновременно является сервером для передачи контента, а следовательно контент скачивается быстрее. Ведь 10 компьютеров передадут файл быстрее чем 1.</p>
</div>
</div>

<div class=\"features__box features__box--synchronization\">
<div class=\"features__box-cont\">
<h4>Автоматическая и выборочная синхронизация</h4>

<p>Устройства (PC, mac, linux, smartphone etc) объединенные под вашим аккаунтом автоматически синхронизируют контент друг с другом. Что позволяет быть уверенным в том что ваши файлы в сохранности, и даже в случае утраты какого-либо устройства, ваши данные не пострадают.Дополнительно можно настроить выборочную синхронизацию, например для экономии места на каком-то устройстве.</p>
</div>
</div>

<div class=\"features__box features__box--collaboration\">
<div class=\"features__box-cont\">
<h4>Secure file sharing and collaboration</h4>

<p>Вы можете в любой момент поделиться файлом/папкой с любым человеком, лишь отправив ему созданную ссылку, установив время уничтожения ссылки(например сразу после завершения скачки). Человек сможет скачать файл как через обычный браузер так и установив приложение Private Box (в этом случае скачка будет происходить быстрее). Также вы можете пригласить любого человека для коллаборации над папкой, назначив этому человеку права доступа к контенту, в этом случае человек сможет загружать в том числе и свои файлы в эту общую папку. Права доступа можно изменить в любое время. File sharing and collaboration проиходят через защищенные протоколы.</p>
</div>
</div>

<div class=\"features__box features__box--capabilities\">
<div class=\"features__box-cont\">
<h4>Административные возможности и контроль бизнеса</h4>

<p>Для бизнес аккаунтов доступна централизованная административная панель где вы сможете просматривать текущих и добавлять новых сотрудников, назначать им различные права для доступа к папкам, детально просматривать действия каждого сотрудника, время входа, время активности etc.Информация о ваших работниках всегда у вас перед глазами, вы полностью контролируете рабочий процесс.</p>
</div>
</div>

<div class=\"features__box features__box--platforms\">
<div class=\"features__box-cont\">
<h4>Работа на всех платформах</h4>

<p>Private Box поддерживает основные платформы: Windows, Mac, Linux, Android, iOS. Вам доступны программы под десктопные, мобильные устройства и расширенный веб интерфейс. Изящные программы работают быстро и производительно, не перегружая ваши устройства. Интуитивно понятный интерфейс.</p>
</div>
</div>
</div>

<div class=\"title\">
<h2>Get Private. Get Private Box</h2>
</div>

<div class=\"features__button\"><a class=\"features__button-link signup-dialog\" href=\"javascript:void(0)\" style=\"\">Try for free %CountDaysTrialLicense days</a> <span class=\"features__button-or\">or</span> <a class=\"btn-average\" href=\"/pricing\">Purchase now!</a> <span class=\"features__button-info\">You can always upgrade your account at any time</span></div>
</div>
</div>
<!-- END .features -->');
INSERT INTO {$tablePrefix}pages (page_id, page_created, page_updated, page_status, page_lang, page_title, page_name, page_alias, page_keywords, page_description, page_text) VALUES (2, '2016-09-30 16:00:16+03', '2017-11-03 15:02:00+02', 1, 'en', 'page2', 'Pricing', '_pricing_', '', '', '<!-- .pricing -->
<div class=\"pricing\">
<div class=\"pricing__cont\">
<div class=\"title\">
<h2>Private Box pricing</h2>
</div>

<div class=\"pricing__text\">
<p>Private Box предлагает простые и прозрачные тарифы.</p>
</div>

<div class=\"pricing__button\" data-toggle=\"buttons\"><label class=\"btn btn-radio-min active\"><input autocomplete=\"off\" name=\"radio-login\" type=\"radio\" />Billed monthly</label> <label class=\"btn btn-radio-min\"><input autocomplete=\"off\" name=\"radio-login\" type=\"radio\" />Billed annually</label></div>

<div class=\"pricing__block\">
<div class=\"pricing__box\">
<div class=\"pricing__box-cont\">
<div class=\"pricing__head\"><span class=\"pricing__head-title\">Starter</span> <span class=\"pricing__head-inform\">for personal use</span> <span class=\"pricing__head-price\"><b>Free</b></span></div>

<div class=\"pricing__body\">
<div class=\"pricing__body-button\"><a class=\"btn-default\" href=\"/download\">Download now</a></div>

<div class=\"pricing__body-version\">&nbsp;</div>

<div class=\"pricing__body-list\">
<ul>
	<li>Extra fast</li>
	<li>Strong Encryption</li>
	<li>Secure links</li>
	<li>Device to device file transfer</li>
	<li>Available on all platforms</li>
</ul>
</div>
</div>
</div>
</div>

<div class=\"pricing__box\">
<div class=\"pricing__box-cont\">
<div class=\"pricing__head\"><span class=\"pricing__head-title\">PRO</span> <span class=\"pricing__head-inform\">for personal use</span> <span class=\"pricing__head-price\">\$ 3.99</span> <span class=\"pricing__head-time\">per Month</span>

<div class=\"pricing__head-free\"><a class=\"signup-dialog\" href=\"javascript:void(0)\">Try %CountDaysTrialLicense days for free</a> <span>or</span></div>
</div>

<div class=\"pricing__body\">
<div class=\"pricing__body-button\"><a class=\"btn-default\" href=\"/purchase/professional\">Purchase now</a></div>

<div class=\"pricing__body-version\"><span><b>All from Starter version</b></span></div>

<div class=\"pricing__body-list\">
<ul>
	<li>Unlimited devices</li>
	<li>Unlimited data transfer</li>
	<li>Automatic &amp; Selective Sync</li>
	<li>File &amp; Folder sharing</li>
	<li>Secure Collaboration</li>
	<li>Real time permissions changing</li>
	<li>Password protected links</li>
	<li>Auto destructed links with a timer</li>
	<li>Remote wipe</li>
</ul>
</div>
</div>
</div>
</div>

<div class=\"pricing__box\">
<div class=\"pricing__box-cont\">
<div class=\"pricing__head\"><span class=\"pricing__head-title\">Business</span> <span class=\"pricing__head-inform\">for business use</span> <span class=\"pricing__head-price\">\$ 4.99</span> <span class=\"pricing__head-time\">per user/ Month</span> <span class=\"pricing__head-start\">Starting from 3 licenses</span>

<div class=\"pricing__head-free\"><a class=\"signup-dialog\" href=\"javascript:void(0)\">Try %CountDaysTrialLicense days for free</a> <span>or</span></div>

<div class=\"pricing__sticker\"><span><b>IDEAL FOR</b><b>BUSINESS</b></span></div>
</div>

<div class=\"pricing__body\">
<div class=\"pricing__body-button\"><a class=\"btn-default\" href=\"/purchase/business\">Purchase now</a></div>

<div class=\"pricing__body-version\"><span><b>All from PRO version</b></span></div>

<div class=\"pricing__body-list\">
<ul>
	<li>Full control of team members</li>
	<li>Management of every member&#39;s permissions</li>
	<li>Detailed log of events</li>
	<li>Detailed log of actions</li>
	<li>Priority e-mail support</li>
</ul>
</div>
</div>
</div>
</div>
</div>
<span class=\"pricing__info\">На всех тарифах предусмотрен <b>бесплатный</b> пробный %CountDaysTrialLicense дневный период!</span>

<div class=\"means-payment\"><span>We accept:</span> <a class=\"means-payment__visa\" href=\"javascript:void(0)\">&nbsp;</a> <a class=\"means-payment__masterCard\" href=\"javascript:void(0)\">&nbsp;</a> <a class=\"means-payment__maestro\" href=\"javascript:void(0)\">&nbsp;</a> <a class=\"means-payment__americanExpress\" href=\"javascript:void(0)\">&nbsp;</a> <a class=\"means-payment__bitcoin\" href=\"javascript:void(0)\">&nbsp;</a> <a class=\"means-payment__payPal\" href=\"javascript:void(0)\">&nbsp;</a></div>
</div>
</div>
<!-- END .pricing -->');
INSERT INTO {$tablePrefix}pages (page_id, page_created, page_updated, page_status, page_lang, page_title, page_name, page_alias, page_keywords, page_description, page_text) VALUES (3, '2016-10-03 15:52:09+03', '2017-09-14 19:03:26+03', 1, 'en', 'Помощь', 'FAQ', 'faq', '', '', '<!-- .features -->
<div class=\"features\">
<div class=\"features__cont\">
<div class=\"title\">
<h2>Private Box FAQ</h2>
</div>

<div class=\"features__text\">
<p>Здесь вы найдете ответы на наиболее часто задаваемые вопросы.</p>
</div>

<div class=\"features__block\">
<div class=\"features__box features__box--confidentiality\">
<div class=\"features__box-cont\">
<h4>Question1.</h4>
<p>Answer1.</p>
</div>
</div>

<div class=\"features__box features__box--security\">
<div class=\"features__box-cont\">
<h4>Question2.</h4>
<p>Answer2.</p>
</div>
</div>

<div class=\"features__box features__box--speed\">
<div class=\"features__box-cont\">
<h4>Question3</h4>
<p>Answer3.</p>
</div>
</div>

</div>

<div class=\"title\">
<h2>Get Private. Get Private Box</h2>
</div>

<div class=\"features__button\"><a class=\"features__button-link signup-dialog\" href=\"javascript:void(0)\">Try for free 30 days</a> <span class=\"features__button-or\">or</span> <a class=\"btn-average\" href=\"/page/pricing\">Purchase now!</a> <span class=\"features__button-info\">You can always upgrade your account at any time</span></div>
</div>
</div>
<!-- END .features -->');
INSERT INTO {$tablePrefix}pages (page_id, page_created, page_updated, page_status, page_lang, page_title, page_name, page_alias, page_keywords, page_description, page_text) VALUES (4, '2017-09-29 13:24:35+03', '2017-09-29 13:37:38+03', 1, 'en', 'Rules', 'Rules', 'rules', '', '', '<!-- .features -->
<div class=\"features\">
<div class=\"features__cont\">
<div class=\"title\">
<h2>Private Box Rules</h2>
</div>

<div class=\"features__text\">
<p>Здесь Rules.</p>
</div>


<div class=\"title\">
<h2>Get Private. Get Private Box</h2>
</div>

<div class=\"features__button\"><a class=\"features__button-link signup-dialog\" href=\"javascript:void(0)\">Try for free 30 days</a> <span class=\"features__button-or\">or</span> <a class=\"btn-average\" href=\"/page/pricing\">Purchase now!</a> <span class=\"features__button-info\">You can always upgrade your account at any time</span></div>
</div>
</div>
<!-- END .features -->');
INSERT INTO {$tablePrefix}pages (page_id, page_created, page_updated, page_status, page_lang, page_title, page_name, page_alias, page_keywords, page_description, page_text) VALUES (5, '2017-09-29 13:38:25+03', '2017-09-29 13:39:29+03', 1, 'en', 'Privacy Policy', 'Privacy Policy', 'privacy-policy', '', '', '<!-- .features -->
<div class=\"features\">
<div class=\"features__cont\">
<div class=\"title\">
<h2>Private Box Privacy Policy</h2>
</div>

<div class=\"features__text\">
<p>Здесь Privacy Policy.</p>
</div>

<div class=\"title\">
<h2>Get Private. Get Private Box</h2>
</div>

<div class=\"features__button\"><a class=\"features__button-link signup-dialog\" href=\"javascript:void(0)\">Try for free 30 days</a> <span class=\"features__button-or\">or</span> <a class=\"btn-average\" href=\"/page/pricing\">Purchase now!</a> <span class=\"features__button-info\">You can always upgrade your account at any time</span></div>
</div>
</div>
<!-- END .features -->');


--
-- Name: {$tablePrefix}pages_page_id_seq; Type: SEQUENCE SET; Schema: first; Owner: -
--

SELECT pg_catalog.setval('{$tablePrefix}pages_page_id_seq', 5, true);


--
-- Data for Name: {$tablePrefix}preferences; Type: TABLE DATA; Schema: first; Owner: -
--

INSERT INTO {$tablePrefix}preferences (pref_id, pref_title, pref_key, pref_value, pref_category) VALUES (3, 'E-Mail администратора', 'adminEmail', 'support@pvtbox.net', 1);
INSERT INTO {$tablePrefix}preferences (pref_id, pref_title, pref_key, pref_value, pref_category) VALUES (4, 'Время жизни токена для пароля (в секундах)', 'user.passwordResetTokenExpire', '86400', 1);
INSERT INTO {$tablePrefix}preferences (pref_id, pref_title, pref_key, pref_value, pref_category) VALUES (5, 'Публичный ключ ReCaptcha', 'reCaptchaPublicKey', '6LeSzhETAAAAALYy2L7WF-FhYeToeUQWiHcsg1kK', 3);
INSERT INTO {$tablePrefix}preferences (pref_id, pref_title, pref_key, pref_value, pref_category) VALUES (6, 'Секретный ключ ReCaptcha', 'reCaptchaSecretKey', '6LeSzhETAAAAANyHzQ9QByF0wKwUsImUoQkNma5e', 3);
INSERT INTO {$tablePrefix}preferences (pref_id, pref_title, pref_key, pref_value, pref_category) VALUES (7, 'Информация о акаунте на Google (например логин и пароль)', 'reCaptchaGoogleAcc', 'alyna.matveeva.88@gmail.com::qwertyasdfgh123456', 3);
INSERT INTO {$tablePrefix}preferences (pref_id, pref_title, pref_key, pref_value, pref_category) VALUES (8, 'E-Mail PayPal - аккаунта (seller)', 'paypalSellerEmail', 'support@pvtbox.net', 2);
INSERT INTO {$tablePrefix}preferences (pref_id, pref_title, pref_key, pref_value, pref_category) VALUES (9, 'Количество разрешенных регистраций с одгого ИП до появления Рекапчи', 'RegisterCountNoCaptcha', '2', 3);
INSERT INTO {$tablePrefix}preferences (pref_id, pref_title, pref_key, pref_value, pref_category) VALUES (10, 'Количество разрешенных неверных логинов с одного ИП до появления Рекапчи', 'LoginCountNoCaptcha', '3', 3);
INSERT INTO {$tablePrefix}preferences (pref_id, pref_title, pref_key, pref_value, pref_category) VALUES (11, 'Ключ доступа к АПИ для сигнального сервера', 'SignalAccessKey', 'CDSBISUv32773687cbdj43cbidepd32323frevfvffwecfvDXSXNWKJdcds', 4);
INSERT INTO {$tablePrefix}preferences (pref_id, pref_title, pref_key, pref_value, pref_category) VALUES (12, 'Количество восстановлений пароля с одного ИП до появления Рекапчи', 'ResetPasswordCountNoCaptcha', '2', 3);
INSERT INTO {$tablePrefix}preferences (pref_id, pref_title, pref_key, pref_value, pref_category) VALUES (13, 'Время хранения патчей для откатов изменений (в секундах)', 'RestorePatchTTL', '7200', 1);
INSERT INTO {$tablePrefix}preferences (pref_id, pref_title, pref_key, pref_value, pref_category) VALUES (14, 'Количество запросов в саппорт одного ИП до появления Рекапчи', 'ContactCountNoCaptcha', '2', 3);


--
-- Name: {$tablePrefix}preferences_pref_id_seq; Type: SEQUENCE SET; Schema: first; Owner: -
--

SELECT pg_catalog.setval('{$tablePrefix}preferences_pref_id_seq', 14, true);


--
-- Data for Name: {$tablePrefix}servers; Type: TABLE DATA; Schema: first; Owner: -
--

INSERT INTO {$tablePrefix}servers (server_id, server_type, server_title, server_url, server_ip, server_port, server_login, server_password, server_status) VALUES (2, 'STUN', 'Сервер STUN', 'stun.null.null:443', '0.0.0.0', 0, '                                                  ', '                                                  ', 1);
INSERT INTO {$tablePrefix}servers (server_id, server_type, server_title, server_url, server_ip, server_port, server_login, server_password, server_status) VALUES (4, 'TURN', 'Сервер TURN', '93.190.137.196:443', '0.0.0.0', 0, 'test                                              ', '1234                                              ', 1);
INSERT INTO {$tablePrefix}servers (server_id, server_type, server_title, server_url, server_ip, server_port, server_login, server_password, server_status) VALUES (6, 'TURN', 'Сервер TURN 2', '95.213.164.12:3478', '0.0.0.0', 0, 'test                                              ', '1234                                              ', 0);
INSERT INTO {$tablePrefix}servers (server_id, server_type, server_title, server_url, server_ip, server_port, server_login, server_password, server_status) VALUES (7, 'TURN', 'Turn-сервер axbu', '185.86.79.39:443?transport=tcp', '0.0.0.0', 0, 'test                                              ', '1234                                              ', 1);
INSERT INTO {$tablePrefix}servers (server_id, server_type, server_title, server_url, server_ip, server_port, server_login, server_password, server_status) VALUES (8, 'PROXY', 'https://node.null.null/', 'https://node.null.null/', '0.0.0.0', 0, '                                                  ', '                                                  ', 1);
INSERT INTO {$tablePrefix}servers (server_id, server_type, server_title, server_url, server_ip, server_port, server_login, server_password, server_status) VALUES (5, 'SIGN', 'Сервер SIGN', 'signalserver.null.null:8888', '0.0.0.0', 0, '                                                  ', '                                                  ', 1);


--
-- Name: {$tablePrefix}servers_server_id_seq; Type: SEQUENCE SET; Schema: first; Owner: -
--

SELECT pg_catalog.setval('{$tablePrefix}servers_server_id_seq', 8, true);


--
-- Name: {$tablePrefix}software_software_id_seq; Type: SEQUENCE SET; Schema: first; Owner: -
--

SELECT pg_catalog.setval('{$tablePrefix}software_software_id_seq', 10, true);


--
-- PostgreSQL database dump complete
--
        ");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180115_130804_create_initial_table_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180115_130804_create_initial_table_data cannot be reverted.\n";

        return false;
    }
    */
}
