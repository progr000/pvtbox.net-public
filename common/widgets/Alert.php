<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\widgets;

use yii\bootstrap\Widget;

/**
 * Alert widget renders a message from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash. You can set message as following:
 *
 * ```php
 * \Yii::$app->session->setFlash('error', 'This is the message');
 * \Yii::$app->session->setFlash('success', 'This is the message');
 * \Yii::$app->session->setFlash('info', 'This is the message');
 * ```
 *
 * Multiple messages could be set as follows:
 *
 * ```php
 * \Yii::$app->session->setFlash('error', ['Error 1', 'Error 2']);
 * ```
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 */
class Alert extends Widget
{
    /**
     * @var array the alert types configuration for the flash messages.
     * This array is setup as $key => $value, where:
     * - $key is the name of the session flash variable
     * - $value is the bootstrap alert type (i.e. danger, success, info, warning)
     */
    public $alertTypes = [
        'error'   => 'alert-error',
        'danger'  => 'alert-danger',
        'success' => 'alert-success',
        'info'    => 'alert-info',
        'warning' => 'alert-warning',
    ];

    /**
     * @var array the options for rendering the close button tag.
     */
    public $closeButton = [];

    public function init()
    {
        parent::init();

        $session = \Yii::$app->session;
        $flashes = $session->getAllFlashes();
        //var_dump($flashes); exit;
        $appendCss = isset($this->options['class']) ? ' ' . $this->options['class'] : '';

        $i = 0;
        foreach ($flashes as $type => $data) {

            //var_dump($data);
            //if (isset($this->alertTypes[$type])) {
                //var_dump($data); exit;
                //var_dump($data); exit;
                /*
                $data = (array) $data;
                foreach ($data as $i => $message) {
                    /* initialize css class for each alert box *--/
                    $this->options['class'] = $this->alertTypes[$type] . $appendCss;

                    /* assign unique id to each alert box *--/
                    $this->options['id'] = $this->getId() . '-' . $type . '-' . $i;

                    echo \yii\bootstrap\Alert::widget([
                        'body' => $message,
                        'closeButton' => strrpos($this->alertTypes[$type], 'ttl') ? false : $this->closeButton,
                        'options' => $this->options,
                    ]);
                }
                */
                $appendCss2 = isset($data['class']) ? ' ' . $data['class'] : '';

                if (is_array($data) && isset($data['message'], $data['ttl'])) {
                    $message = $data['message'];
                    $this->options['data-ttl'] = $data['ttl'];
                    $showClose = isset($data['showClose']) ? $data['showClose'] : false;
                    $alert_id =  isset($data['alert_id']) ? $data['alert_id'] : $this->getId() . '-' . $type . '-' . $i;
                    $auto_close_callback = isset($data['auto_close_callback']) ? $data['auto_close_callback'] : '';
                    $alert_type = isset($data['type']) ? ' alert-' . $data['type'] : '';
                    $this->options['data-type'] = isset($data['type']) ? $data['type'] : 'unknown';
                    $alert_type .= isset($data['showClose']) ? " alert-has-close" : "";
                    if (isset($data['alert_action'])) {
                        $this->options['data-alert-action'] = $data['alert_action'];
                    } else {
                        $this->options['data-alert-action'] = "";
                    }
                    $this->closeButton = [
                        //'data-dismiss' => 'alert',
                        //'aria-hidden' => 'true',
                        'class' => "close close-alert close-alert-{$alert_id}",
                        'data-alert-id' => $alert_id,
                        'data-flash-dialog' => $type,
                    ];
                } else {
                    $message = $data;
                    unset($this->options['data-ttl']);
                    $showClose = true;
                    $alert_id = $this->getId() . '-' . $type . '-' . $i;
                    $auto_close_callback = '';
                    $alert_type = '';
                    /*
                    $this->closeButton = [
                        'class' => "close close-alert close-alert-{$alert_id}",
                        'data-alert-id' => $alert_id,
                        'data-flash-dialog' => $type,
                    ];
                    */
                }

                /* initialize css class for each alert box */
                $this->options['class'] = (isset($this->alertTypes[$type]) ? $this->alertTypes[$type] : '') . $alert_type . $appendCss . $appendCss2;

                /* assign unique id to each alert box */
                $this->options['id'] = $alert_id;
                $i++;
                //var_dump($this->options['id']);
                if (isset($ttl)) { $this->options['data-ttl'] = $ttl; }
                if (!empty($auto_close_callback)) { $this->options['data-auto-close-callback'] = $auto_close_callback; }

                echo \yii\bootstrap\Alert::widget([
                    'body' => $message,
                    'closeButton' => $showClose ? $this->closeButton : false,
                    'options' => $this->options,
                ]);

                $session->removeFlash($type);
            //}
        }
    }
}
