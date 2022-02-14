<?php
/* @var $this yii\web\View */
/* @var $UserModel common\models\Users */
/* @var $licenseCountInfo array */
/* @var $serverLicenseCountInfo array */
/* @var $NodeInfo array */

use yii\widgets\DetailView;
use common\models\Users;
use common\models\Licenses;
use common\models\Preferences;

/** ******************************* */
$attributes_personal_info = [
    'user_id',
    'user_name',
    'user_email:email',
    [
        'attribute' => 'user_status',
        'label' => 'User-Status',
        'value' => Users::statusLabel($UserModel->user_status),
    ],
    [
        'attribute' => 'user_status',
        'label' => 'Email-Status',
        'value' => $UserModel->user_status == Users::STATUS_CONFIRMED ? 'Confirmed' : 'Unconfirmed',
    ],
    'user_created',
    'user_last_ip',
    'user_promo_code',
];

/****BEGIN-CUT-IT-IN-SH****/
if (!Yii::$app->params['self_hosted']) {

    /** ******************************* */
    $attributes_license_info = [
        [
            'attribute' => 'license_type',
            'value' => Licenses::getType($UserModel->license_type, true),
        ],
        [
            'attribute' => 'license_expire',
            'label' => 'License expiration date',
            'format' => 'raw',
            'value' => function ($data) {
                /** @var \common\models\Users $data */
                if ($data->license_type == Licenses::TYPE_FREE_TRIAL) {
                    $TrialDays = Licenses::getCountDaysTrialLicense();
                    $BonusPeriodLicense_hours = Preferences::getValueByKey('BonusPeriodLicense', 72, 'integer');
                    if ($data->user_status == Users::STATUS_CONFIRMED) {
                        $BonusTrialForEmailConfirm_days = Preferences::getValueByKey('BonusTrialForEmailConfirm', 7, 'integer');
                        $BonusTrialForEmailConfirm_days_text = " + bonus for email confirmation {$BonusTrialForEmailConfirm_days} days";
                    } else {
                        $BonusTrialForEmailConfirm_days = 0;
                        $BonusTrialForEmailConfirm_days_text = "";
                    }
                    $bonus_plus = $TrialDays * 86400 + $BonusPeriodLicense_hours * 3600 + $BonusTrialForEmailConfirm_days * 86400;
                    $expire = date(SQL_DATE_FORMAT, strtotime($data->user_created) + $bonus_plus);

                    $ret = $expire . "<span class=\"small\" style=\"color: #FF0000;\"> (The license is designed for {$TrialDays} days + bonus {$BonusPeriodLicense_hours} hours{$BonusTrialForEmailConfirm_days_text})</span>";
                    return $ret;
                } else {
                    $str = $data->license_expire;
                    if (in_array($data->license_type, [
                            Licenses::TYPE_PAYED_BUSINESS_ADMIN,
                            Licenses::TYPE_PAYED_PROFESSIONAL]) &&
                        !$data->license_expire
                    ) {
                        $str = "(not set) <small style=\"color: #FF0000; font-weight: bold;\">(For correct work you must set a value for Pro/Business licenses)</small>";
                    }
                    return $str;
                }
            }
        ],
        [
            'attribute' => 'license_period',
            'format' => 'raw',
            'value' => function () use ($UserModel) {
                $str = Licenses::getBilledByPeriod($UserModel->license_period, true);
                if (in_array($UserModel->license_type, [
                        Licenses::TYPE_PAYED_BUSINESS_ADMIN,
                        Licenses::TYPE_PAYED_PROFESSIONAL]) && $UserModel->license_period == Licenses::PERIOD_NOT_SET
                ) {
                    $str .= " <small style=\"color: #FF0000; font-weight: bold;\">(For correct work you must set a value for Pro/Business licenses)</small>";
                }
                return $str;
            }
        ],
    ];
    if ($UserModel->license_business_from) {
        $attributes_license_info[] = [
            'attribute' => 'license_business_from',
            'format' => 'raw',
            'value' => function () use ($UserModel) {
                if ($UserModel->license_business_from) {
                    $ret = Users::findIdentity($UserModel->license_business_from);
                    if ($ret) {
                        return "<a href=\"/users/view?id={$ret->user_id}\">{$ret->user_email}</a>";
                    }
                }
                return null;
            }
        ];
    }
    if ($UserModel->previous_license_business_from) {
        $attributes_license_info[] = [
            'attribute' => 'previous_license_business_from',
            'format' => 'raw',
            'value' => function () use ($UserModel) {
                if ($UserModel->previous_license_business_from) {
                    $ret = Users::findIdentity($UserModel->previous_license_business_from);
                    if ($ret) {
                        return "<a href=\"/users/view?id={$ret->user_id}\">{$ret->user_email}</a>";
                    }
                }
                return null;
            }
        ];
        $attributes_license_info[] = [
            'attribute' => 'previous_license_business_finish',
            'format' => 'raw',
            'value' => function () use ($UserModel) {
                if ($UserModel->previous_license_business_from) {
                    return $UserModel->previous_license_business_finish;
                }
                return null;
            }
        ];
    }


    /** ******************************* */
    $attributes_payment_info = [
        [
            'attribute' => 'pay_type',
            'format' => 'raw',
            'value' => function () use ($UserModel) {
                $str = Users::getPayTypeName($UserModel->pay_type);
                if (in_array($UserModel->license_type, [
                        Licenses::TYPE_PAYED_BUSINESS_ADMIN,
                        Licenses::TYPE_PAYED_PROFESSIONAL]) && $UserModel->pay_type == Users::PAY_NOTSET
                ) {
                    $str .= " <small style=\"color: #FF0000; font-weight: bold;\">(For correct work you must set a value for Pro/Business licenses)</small>";
                }
                return $str;
            }
        ],
        [
            'attribute' => 'payment_already_initialized',
            'value' => function ($data) {
                return $data->payment_already_initialized == Users::PAYMENT_NOT_INITIALIZED ? 'No' : 'Yes';
            },
        ],
    ];

    /** ******************************* */
    $attributes_seller_info = [
        [
            'attribute' => 'has_personal_seller',
            'format' => 'raw',
            'value' => function () use ($UserModel) {
                return $UserModel->has_personal_seller ? "Yes" : "No";
            }
        ],
        [
            'attribute' => 'user_ref_id',
            'label' => 'Seller Id',
        ],
    ];

    /** ******************************* */
    $attributes_personal_limitation = [
        [
            'attribute' => 'upl_limit_nodes',
            'label' => 'Limit nodes<br />(upl_limit_nodes)',
            'format' => 'raw',
            'value' => function ($data) {
                /** @var $data \common\models\Users */
                return $data->upl_limit_nodes === null ? "defined by license <b>(" . ($data->_ucl_limit_nodes > 0 ? $data->_ucl_limit_nodes : "no limit") . ")</b>" : $data->upl_limit_nodes;
            },
        ],
        [
            'attribute' => 'upl_shares_count_in24',
            'label' => 'Limit count shares peer day<br />(upl_shares_count_in24):',
            'format' => 'raw',
            'value' => function ($data) {
                /** @var $data \common\models\Users */
                return $data->upl_shares_count_in24 === null ? "defined by license <b>(" . ($data->_ucl_shares_count_in24 > 0 ? $data->_ucl_shares_count_in24 : "no limit") . ")</b>" : $data->upl_shares_count_in24;
            },
        ],
        [
            'attribute' => 'upl_max_shares_size',
            'label' => 'Limit share size<br />(upl_max_shares_size)',
            'format' => 'raw',
            'value' => function ($data) {
                /** @var $data \common\models\Users */
                return $data->upl_max_shares_size === null ? "defined by license <b>(" . ($data->_ucl_max_shares_size > 0 ? $data->_ucl_max_shares_size : "no limit") . ")</b>" : $data->upl_max_shares_size;
            },
        ],
        [
            'attribute' => 'upl_max_count_children_on_copy',
            'label' => 'Maximum children on copy<br />(upl_max_count_children_on_copy)',
            'format' => 'raw',
            'value' => function ($data) {
                /** @var $data \common\models\Users */
                return $data->upl_max_count_children_on_copy === null ? "defined by license <b>(" . ($data->_ucl_max_count_children_on_copy > 0 ? $data->_ucl_max_count_children_on_copy : "no limit") . ")</b>" : $data->upl_max_count_children_on_copy;
            },
        ],
        [
            'attribute' => 'upl_block_server_nodes_above_bought',
            'label' => 'Block login from server node above bought<br />(upl_block_server_nodes_above_bought)',
            'format' => 'raw',
            'value' => function ($data) {
                /** @var $data \common\models\Users */
                return (
                $data->upl_block_server_nodes_above_bought === null
                    ? "defined by license <b>(" . ($data->_ucl_block_server_nodes_above_bought ? "Yes" : "No") . ")</b>"
                    : (
                $data->upl_block_server_nodes_above_bought == 1
                    ? 'Yes'
                    : 'No'
                )
                );
            },
        ],
    ];
    if ($UserModel->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
        $attributes_personal_limitation[] = [
            'attribute' => 'enable_admin_panel',
            'label' => 'Enable access to Admin-panel',
            'value' => function ($data) {
                return $data->enable_admin_panel ? 'Yes' : 'No';
            }
        ];
    }

}
/****END-CUT-IT-IN-SH****/

/** ******************************* */
if ($UserModel->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
    $form_add_licenses = $this->render('add-business-count-licenses', ['UserModel' => $UserModel]);
    $form_add_server_licenses = $this->render('add-business-count-server-licenses', ['UserModel' => $UserModel]);
} else {
    $form_add_licenses = "";
    $form_add_server_licenses = "";
}
if (Yii::$app->params['self_hosted']) {
    $form_add_licenses = "";
    $form_add_server_licenses = "";
}
$attributes_next = [
    //'user_updated',
    [
        'attribute' => '_count_events',
        'value' => $UserModel->getCountEvents(),
    ],
    [
        'attribute' => '_count_optimized_events',
        'value' => $UserModel->getCountOptimizedEvents(),
    ],
    [
        'attribute' => 'Node info:',
        'format' => 'raw',
        'value' => function ($model) use ($NodeInfo) {
            $str = "";
            $str .= "UserTotalNodes = <b>{$NodeInfo['user_total_nodes']}</b>;<br />";
            $str .= "UserServerNodes = <b>{$NodeInfo['user_server_nodes']}</b>;<br />";
            if ($model->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                $str .= "ColleaguesTotalNodes = <b>{$NodeInfo['colleagues_total_nodes']}</b>;<br />";
                $str .= "ColleaguesServerNodes = <b>{$NodeInfo['colleagues_server_nodes']}</b>;<br />";
            }
            return $str;
        }
    ],
    [
        'attribute' => '',
        'label' => 'License Info: ',
        'format' => 'raw',
        'visible' => ($UserModel->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN),
        'value' => function () use ($UserModel, $licenseCountInfo, $form_add_licenses) {
            if ($UserModel->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                return "Total: " . $licenseCountInfo['total'] . "<br />" .
                "Available: " . $licenseCountInfo['unused'] . "<br />" .
                "Used: " . $licenseCountInfo['used'] . "<br /><br />" .
                $form_add_licenses;
            } else {
                return "";
            }
        },
    ],
    [
        'attribute' => '',
        'label' => 'Server-license Info: ',
        'format' => 'raw',
        'visible' => ($UserModel->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN),
        'value' => function () use ($UserModel, $serverLicenseCountInfo, $form_add_server_licenses) {
            if ($UserModel->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                return "Total: " . $serverLicenseCountInfo['total'] . "<br />" .
                "Available: " . $serverLicenseCountInfo['unused'] . "<br />" .
                "Used: " . $serverLicenseCountInfo['used'] . "" .
                $form_add_server_licenses;
            } else {
                return "";
            }
        },
    ],
];
/****BEGIN-CUT-IT-IN-SH****/
if (!Yii::$app->params['self_hosted']) {
    $attributes_next[] = [
        'attribute' => '',
        'label' => 'Delete Old Patches Info: ',
        'format' => 'raw',
        'visible' => true,
        'value' => function () use ($UserModel) {
            return $this->render('user-dop-form', ['UserModel' => $UserModel]);
        },
    ];
}
/****END-CUT-IT-IN-SH****/
?>




<!-- ******************************* -->
<div class="delimiter first-el">Personal user info</div>
<?= DetailView::widget([
    'model' => $UserModel,
    'attributes' => $attributes_personal_info,
    'options' => [
        'class' => 'table table-striped table-bordered detail-view user-info-table'
    ],
]); ?>


<!--BEGIN-CUT-IT-IN-SH-->
<?php if (!Yii::$app->params['self_hosted']) { ?>
<!-- ******************************* -->
<div class="delimiter">License info</div>
<?= DetailView::widget([
    'model' => $UserModel,
    'attributes' => $attributes_license_info,
    'options' => [
        'class' => 'table table-striped table-bordered detail-view user-info-table'
    ],
]); ?>


<!-- ******************************* -->
<div class="delimiter">Payment info</div>
<?= DetailView::widget([
    'model' => $UserModel,
    'attributes' => $attributes_payment_info,
    'options' => [
        'class' => 'table table-striped table-bordered detail-view user-info-table'
    ],
]); ?>


<!-- ******************************* -->
<div class="delimiter">Seller info</div>
<?= DetailView::widget([
    'model' => $UserModel,
    'attributes' => $attributes_seller_info,
    'options' => [
        'class' => 'table table-striped table-bordered detail-view user-info-table'
    ],
]); ?>

<!-- ******************************* -->
<div class="delimiter">Personal user limitation</div>
<?= DetailView::widget([
    'model' => $UserModel,
    'attributes' => $attributes_personal_limitation,
    'options' => [
        'class' => 'table table-striped table-bordered detail-view user-info-table'
    ],
]); ?>

<?php } ?>
<!--END-CUT-IT-IN-SH-->

<!-- ******************************* -->
<div class="delimiter first-el">Statistics and manage</div>
<?= DetailView::widget([
    'model' => $UserModel,
    'attributes' => $attributes_next,
    'options' => [
        'class' => 'table table-striped table-bordered detail-view user-info-table'
    ],
]); ?>

<!-- ******************************* -->
<div class="delimiter first-el"></div>

