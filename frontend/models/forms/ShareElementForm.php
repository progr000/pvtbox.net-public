<?php

namespace frontend\models\forms;

use Yii;
use yii\base\Model;
use common\models\Users;
use common\models\UserColleagues;
use common\models\UserCollaborations;
use common\models\Licenses;
use common\models\UserLicenses;
use common\models\Preferences;
use common\models\Notifications;
use frontend\models\NodeApi;
use frontend\models\CollaborationApi;

/**
 * ShareElementForm is the model behind the contact form.
 */
class ShareElementForm extends Model
{
    public $share_email;
    public $share_lifetime;
    public $share_password;
    public $share_link;
    public $hash;
    public $share_hash;

    public $colleague_email;
    public $colleague_message;
    public $file_uuid;
    public $action;
    public $access_type;
    public $colleague_id;

    public $owner_user_id;

    public $_required = [];

    /**
     * ShareElementForm constructor.
     * @param array $required
     * @param array $config
     */
    public function __construct(array $required=array(), array $config=array())
    {
        parent::__construct($config);
        if ($required && sizeof($required)) {
            $this->_required = [$required, 'required'];
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $ret = [
            //[['share_email'], 'required'],
            [['share_email', 'colleague_email'], 'email'],
            [['share_lifetime'], 'safe'],
            [['share_password'], 'string', 'min' => 6, 'max' => 32,
                'tooShort' => '{attribute} should  be at least 6 characters and not more than 32 characters or should be empty',
                'tooLong'  => '{attribute} should  be at least 6 characters and not more than 32 characters or should be empty',
                'message'  => '{attribute} should  be at least 6 characters and not more than 32 characters or should be empty',
            ],
            [['share_password'], 'match','pattern' => Users::PASSWORD_PATTERN, 'message' => Yii::t('forms/login-signup-form', 'password_pattern')],
            [['share_link', 'hash'], 'string'],
            [['share_hash', 'file_uuid'], 'string', 'length' => 32],
            [['action'], 'in', 'range' => [
                CollaborationApi::ACTION_ADD,
                CollaborationApi::ACTION_DELETE,
                CollaborationApi::ACTION_EDIT,
            ]],
            [['colleague_message'], 'string'],
            [['access_type'], 'in', 'range' => [
                UserColleagues::PERMISSION_VIEW,
                UserColleagues::PERMISSION_EDIT,
                UserColleagues::PERMISSION_DELETE,
            ]],
            [['colleague_id', 'owner_user_id'], 'integer'],
        ];
        if (sizeof($this->_required)) {
            $ret[] = $this->_required;
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'share_email'     => Yii::t('forms/share-element-form', 'email'),
            'share_password'  => Yii::t('forms/share-element-form', 'Share_password'),
            'share_lifetime'  => Yii::t('forms/share-element-form', 'Expire_date'),
            'colleague_email' => Yii::t('forms/share-element-form', 'email'),
        ];
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function changeCollaboration()
    {
        $data['colleague_message'] = $this->colleague_message;
        $data['owner_user_id']     = $this->owner_user_id;//Yii::$app->user->identity->getId();
        $data['access_type']       = $this->access_type;
        $data['action']            = $this->action;
        $data['uuid']              = $this->file_uuid;

        $required = ['action', 'access_type', 'owner_user_id', 'uuid'];

        switch ($this->action) {
            case CollaborationApi::ACTION_ADD:
                $data['colleague_email'] = $this->colleague_email;
                $required[] = 'colleague_email';
                $model = new CollaborationApi($required);
                if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
                    return [
                        'result'  => "error",
                        'errcode' => NodeApi::ERROR_WRONG_DATA,
                        'info'    => $model->getErrors(),
                    ];
                }

                $test = CollaborationApi::check_is_colleague_joined_before($data['colleague_email'], $data['owner_user_id']);
                if ($test) {
                    return $model->colleagueAdd();
                } else {
                    return $model->colleagueInvite();
                }
                //return $model->colleagueAdd();
                /* если все же захотим ИНВАЙТ + ДЖОЙН то тут поменять ретурн АДД на ретурн ИНВАЙТ */
                break;
            case CollaborationApi::ACTION_EDIT:
                $data['colleague_id'] = $this->colleague_id;
                $required[] = 'colleague_id';
                $model = new CollaborationApi($required);
                if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
                    return [
                        'result'  => "error",
                        'errcode' => NodeApi::ERROR_WRONG_DATA,
                        'info'    => $model->getErrors(),
                    ];
                }
                return $model->colleagueEdit();
                break;
            case CollaborationApi::ACTION_DELETE:
                $data['colleague_id'] = $this->colleague_id;
                $data['is_from_recursion'] = true;
                $required[] = 'colleague_id';
                $model = new CollaborationApi($required);
                if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
                    return [
                        'result'  => "error",
                        'errcode' => NodeApi::ERROR_WRONG_DATA,
                        'info'    => $model->getErrors(),
                    ];
                }
                return $model->colleagueDelete();
                break;
            default:
                return false;
        }
    }

    /**
     * @return array
     */
    public function cancelCollaboration()
    {
        $data['owner_user_id'] = Yii::$app->user->identity->getId();
        $data['uuid']          = $this->file_uuid;

        $required = ['owner_user_id', 'uuid'];
        $model = new CollaborationApi($required);
        if ($model->load(['CollaborationApi' => $data]) && $model->validate()) {
            return $model->collaborationDelete();
        }

        return [
            'status' => false,
            'info'   => "Failed init CollaborationApi model.",
            'debug'  => $model->getErrors(),
        ];
    }

    /**
     * @return array
     */
    public function leaveCollaboration()
    {
        $query = "SELECT
                    t1.user_id, t1.collaboration_id, t2.colleague_id
                  FROM {{%user_collaborations}} as t1
                  INNER JOIN {{%user_colleagues}} as t2 ON t1.collaboration_id = t2.collaboration_id
                  WHERE (t1.file_uuid = :file_uuid)
                  AND (t2.user_id = :user_id)";
        $res2 = Yii::$app->db->createCommand($query, [
            'file_uuid' => $this->file_uuid,
            'user_id'   => Yii::$app->user->identity->getId(),
        ])->queryOne();

        if (!sizeof($res2)) {
            return [
                'status' => false,
                'info'   => "Access error. You are not in this collaboration.",
            ];
        }

        $data['is_from_recursion'] = true;
        $data['colleague_id']      = $res2['colleague_id'];
        $data['owner_user_id']     = $res2['user_id'];
        $data['access_type']       = UserColleagues::PERMISSION_DELETE;
        $data['action']            = CollaborationApi::ACTION_DELETE;
        $data['uuid']              = $this->file_uuid;
        $data['is_colleague_self_leave'] = true;

        $required = ['action', 'access_type', 'colleague_id', 'owner_user_id', 'uuid'];
        $model = new CollaborationApi($required);
        if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
            return [
                'status' => false,
                'info'   => "Access error. You are not in this collaboration.",
                'debug'  => $model->getErrors(),
            ];
        }

        return $model->colleagueDelete();
    }

    /**
     * @return array
     */
    public function acceptCollaboration()
    {
        Yii::$app->session->remove('is_from_guest_sign');
        Yii::$app->session->remove('is_from_guest_login');
        Yii::$app->session->remove('is_from_guest_colleague_id');

        /* Ищем коллегу по его ИД */
        $UserColleague = UserColleagues::findOne(['colleague_id' => $this->colleague_id]);
        if (!$UserColleague) {
            return [
                'status'   => false,
                'redirect' => Yii::$app->user->isGuest ? ['/site/index'] : ['/user/files'],
                'info'     => "Collaboration is deleted now. You can't join it.",
                //'Can't find UserColleague with colleague_id={$this->colleague_id}",
            ];
        }

        //http://dlink.frontend.home/user/accept-collaboration?colleague_id=416666
        /* Если не залогинен посетитель */
        if (Yii::$app->user->isGuest) {
            if ($UserColleague->user_id) {
                $User = Users::findIdentity($UserColleague->user_id);
            } else {
                $User = Users::findOne(['user_email' => $UserColleague->colleague_email]);
            }

            Yii::$app->session->set('is_from_guest_colleague_id', $UserColleague->colleague_id);
            if ($User) {
                Yii::$app->session->set('is_from_guest_login', $User->user_email);
                return [
                    'status'   => false,
                    'redirect' => ['/site/login'],
                    'info'     => "You must be logged for accept collaboration.",
                ];
            } else {
                Yii::$app->session->set('is_from_guest_sign', $UserColleague->colleague_email);
                return [
                    'status'   => false,
                    'redirect' => ['/site/signup', 'colleague_id' => $this->colleague_id],
                    'info'     => "Signup for free with email &lt;<b>{$UserColleague->colleague_email}</b>&gt; for accept collaboration.",
                ];
            }
        /* Если залогинен */
        } else {

            /* Если не заполнено поле user_id в записи о коллеге */
            if (!$UserColleague->user_id) {
                if ($UserColleague->colleague_email !== Yii::$app->user->identity->user_email) {
                    return [
                        'status' => false,
                        'info' => "Wrong link (user_email mismatch). Access denied.",
                    ];
                } else {
                    $UserColleague->user_id = Yii::$app->user->identity->getId();
                    if (!$UserColleague->save()) {
                        return [
                            'status' => false,
                            'info'   => "Can't change user_id from null to id of User",
                            'debug'   => $UserColleague->getErrors(),
                        ];
                    }
                }
            } else if ($UserColleague->user_id !== Yii::$app->user->identity->getId()) {
                return [
                    'status' => false,
                    'info' => "Wrong link (user_id mismatch). Access denied.",
                ];
            }

            /* Ищем коллаборацию для найденного коллеги */
            $UserCollaboration = UserCollaborations::findOne(['collaboration_id' => $UserColleague->collaboration_id]);
            if (!$UserCollaboration) {
                return [
                    'status' => false,
                    'info' => "Can't find UserCollaboration for colleague_id={$this->colleague_id}",
                ];
            }

            /* Джойним юзера в эту папку (коллаборацию) */
            $data['colleague_message'] = '';
            $data['action'] = CollaborationApi::ACTION_EDIT;
            $data['access_type'] = $UserColleague->colleague_permission;
            $data['colleague_id'] = $UserColleague->colleague_id;
            $data['collaboration_id'] = $UserColleague->collaboration_id;
            $data['owner_user_id'] = $UserCollaboration->user_id;
            $data['uuid'] = $UserCollaboration->file_uuid;

            $required = [
                'action',
                'access_type',
                'colleague_id',
                'collaboration_id',
                //'owner_user_id',
                //'uuid'
            ];
            $model = new CollaborationApi($required);
            if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
                return [
                    'result' => "error",
                    'errcode' => NodeApi::ERROR_WRONG_DATA,
                    'info' => $model->getErrors(),
                ];
            }
            $ret = $model->colleagueJoin();

            /* проверим есть ли еще папки от владельца этой коллабы */
            /* в которые он успел инвайтнуть юзера, до того как юзер заджойнился */
            /* если есть, то в эти папки джойним юзера автоматически уже*/
            if ($ret['status']) {
                $query = "SELECT
                            t2.colleague_permission,
                            t2.colleague_id,
                            t2.collaboration_id,
                            t1.user_id as owner_user_id,
                            t1.file_uuid
                          FROM {{%user_collaborations}} as t1
                          INNER JOIN {{%user_colleagues}} as t2 ON t1.collaboration_id = t2.collaboration_id
                          WHERE (t1.user_id = :owner_user_id)
                          AND (t2.user_id = :colleague_user_id)
                          AND (t1.collaboration_id != :current_collaboration_id)
                          AND (t2.colleague_status = :STATUS_INVITED)";
                $res = Yii::$app->db
                    ->createCommand($query, [
                        'owner_user_id'            => $UserCollaboration->user_id,
                        'colleague_user_id'        => $UserColleague->user_id,
                        'current_collaboration_id' => $UserCollaboration->collaboration_id,
                        'STATUS_INVITED'           => UserColleagues::STATUS_INVITED,
                    ])
                    ->queryAll();

                if (is_array($res)) {
                    foreach ($res as $v) {

                        unset($data);
                        $data['colleague_message'] = '';
                        $data['action'] = CollaborationApi::ACTION_EDIT;
                        $data['access_type'] = $v['colleague_permission'];
                        $data['colleague_id'] = $v['colleague_id'];
                        $data['collaboration_id'] = $v['collaboration_id'];
                        $data['owner_user_id'] = $v['owner_user_id'];
                        $data['uuid'] = $v['file_uuid'];

                        $required = [
                            'action',
                            'access_type',
                            'colleague_id',
                            'collaboration_id',
                            //'owner_user_id',
                            //'uuid'
                        ];
                        $model_other = new CollaborationApi($required);
                        if ($model_other->load(['CollaborationApi' => $data]) && $model_other->validate()) {
                            $model_other->colleagueJoin();
                        }
                    }
                }
            }

            return $ret;
        }
    }

    /**
     * Метод используется админ панелью для добавления коллеги
     * но т.к. коллега добавляет без указания папки приходится делать такую штуку
     * @param \common\models\Users $owner_user
     * @return bool
     */
    public function adminPanelCreateNullCollaboration($owner_user)
    {
        /** @var \common\models\Users $owner_user */
        //$owner_user = Yii::$app->user->identity;
        $owner_user_id = $owner_user->getId();

        /* при попытке добавить себя в список коллаборантов - ошибка */
        if ($owner_user->user_email == $this->colleague_email) {
            return [
                'status' => false,
                'type' => 'error',
                'info' => "Cant_add_self_into_the_list",
            ];
        }

        /* при попытке добавить в список коллегу который уже есть в этом списке - варнинг */
        $UserColleagueCheck = UserColleagues::find()
            ->alias('t1')
            ->select([
                't1.*',
            ])
            ->innerJoin('{{%user_collaborations}} as t2', 't1.collaboration_id = t2.collaboration_id')
            ->where('(t2.user_id = :user_id) AND (t1.colleague_email = :colleague_email)', [
                'user_id' => $owner_user_id,
                'colleague_email' => $this->colleague_email,
            ])
            ->all();
        //var_dump($UserColleagueCheck); exit;
        if (sizeof($UserColleagueCheck)) {
            return [
                'status' => true,
                'type' => 'warning',
                'info' => "This_user_already_added_into_the_list",
            ];
        }

        /* проверка доступных лицензий */
        $User = Users::findByEmail($this->colleague_email);
        if (!$User || in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])) {
            $UserLicense = UserLicenses::getFreeLicense($owner_user_id);
            if (!$UserLicense) {
                return [
                    'status' => false,
                    'type' => 'error',
                    'info' => Yii::t('app/flash-messages', "license_restriction_businessAdmin_invite_free_or_trial_but_no_available_licenses"),
                ];
            }
        }

        /* Проверка что пользователь ранее не был удален из коллег и период блокировки не закончен */
        if ($User && in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])) {
            if ($User->previous_license_business_from == $owner_user_id) {
                $InviteLockPeriod = Preferences::getValueByKey('InviteLockPeriod', 24, 'integer') * 3600;
                $UserLastDate = strtotime($User->previous_license_business_finish);
                if ($UserLastDate + $InviteLockPeriod >= time()) {
                    return [
                        'status' => false,
                        'type' => 'error',
                        'info' => Yii::t('app/flash-messages', "license_restriction_businessAdmin_invites_the_user_repeatedly"),
                    ];
                }
            }
        }

        /**/
        $UserCollaboration = new UserCollaborations();
        $UserCollaboration->user_id = $owner_user_id;
        $UserCollaboration->file_uuid = null;
        $UserCollaboration->collaboration_status = UserCollaborations::STATUS_DEACTIVATED;
        if ($UserCollaboration->save()) {
            $UserColleague = new UserColleagues();
            $UserColleague->colleague_status = UserColleagues::STATUS_INVITED;
            $UserColleague->colleague_permission = UserColleagues::PERMISSION_VIEW;
            $UserColleague->colleague_email = $this->colleague_email;
            $UserColleague->collaboration_id = $UserCollaboration->collaboration_id;
            $UserColleague->user_id = $User ? $User->user_id : null;

            if ($UserColleague->save()) {

                /* смена лицензии у юзера и списываем лицензию у админа */
                if ($User && in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL]))
                {
                    $owner_user->license_count_available--;
                    $owner_user->license_count_used++;
                    $owner_user->save();

                    if (isset($UserLicense)) { $User->license_expire = $UserLicense->lic_end; }
                    $User->license_business_from = $owner_user->user_id;
                    $User->license_type = Licenses::TYPE_PAYED_BUSINESS_USER;
                    $User->upl_limit_nodes = $owner_user->upl_limit_nodes;
                    $User->upl_shares_count_in24 = $owner_user->upl_shares_count_in24;
                    $User->upl_max_shares_size = $owner_user->upl_max_shares_size;
                    $User->upl_max_count_children_on_copy = $owner_user->upl_max_count_children_on_copy;
                    $User->upl_block_server_nodes_above_bought = $owner_user->upl_block_server_nodes_above_bought;
                    $User->save();

                    if (isset($UserLicense)) {
                        $UserLicense->lic_colleague_user_id = $User->user_id;
                        $UserLicense->lic_colleague_email = $User->user_email;
                        $UserLicense->save();
                    }
                } elseif (!$User) {
                    if (isset($UserLicense)) {
                        $UserLicense->lic_colleague_user_id = null;
                        $UserLicense->lic_colleague_email = $this->colleague_email;
                        $UserLicense->save();
                    }
                }

                return [
                    'status' => true,
                    'type' => 'warning',
                    'ttl' => 60*1000,
                    'showClose' => true,
                    'info' => 'To_complete_invitation_select_folder',
                ];
            }
        } else {
            return [
                'status' => false,
                'type' => 'error',
                'info' => "System error on add to the list",
            ];
        }

    }

    /**
     * @param bool $needTransaction
     * @throws \Exception
     * @throws \Throwable
     */
    public function adminPanelColleagueDelete($needTransaction=true)
    {
        $CollaboratedFolder = UserColleagues::find()
            ->alias('t1')
            ->select([
                't1.colleague_id',
                't1.colleague_email',
                't1.colleague_status',
                't1.colleague_permission',
                't1.user_id as colleague_user_id',
                't2.collaboration_id',
                't2.user_id as owner_user_id',
                't2.file_uuid',
            ])
            ->innerJoin("{{%user_collaborations}} as t2", "(t1.collaboration_id = t2.collaboration_id)")
            ->where("(t1.colleague_email=:colleague_email) AND (t2.user_id = :user_id)", [
                'user_id'         => $this->owner_user_id,
                'colleague_email' => $this->colleague_email,
            ])
            ->andWhere("t2.file_uuid IS NOT NULL")
            //->having("t2.file_uuid IS NOT NULL")
            ->asArray()
            ->all();

        //var_dump($CollaboratedFolder);
        //exit;

        if ($CollaboratedFolder) {
            foreach ($CollaboratedFolder as $item) {

                unset($data);
                $data['colleague_message'] = '';
                $data['owner_user_id']     = $this->owner_user_id;
                $data['access_type']       = UserColleagues::PERMISSION_DELETE;
                $data['action']            = CollaborationApi::ACTION_DELETE;
                $data['colleague_id']      = $item['colleague_id'];
                $data['is_from_recursion'] = true;
                $data['uuid']              = $item['file_uuid'];

                $required = ['action', 'access_type', 'owner_user_id', 'colleague_id', 'uuid'];

                $model = new CollaborationApi($required);
                if ($model->load(['CollaborationApi' => $data]) && $model->validate()) {
                    $model->colleagueDelete($needTransaction);
                }
            }
        } else {
            /* Чистка нулл-коллабораций у коллеги */
            $NullCollaborations = UserCollaborations::find()
                ->alias('t1')
                ->select([
                    't1.collaboration_id',
                ])
                ->innerJoin('{{%user_colleagues}} as t2', '(t1.collaboration_id = t2.collaboration_id)')
                ->where('(t2.colleague_email=:colleague_email) AND (t1.file_uuid IS NULL) AND (t1.user_id=:user_id)', [
                    'colleague_email' => $this->colleague_email,
                    'user_id' => $this->owner_user_id,
                ])->all();
            if ($NullCollaborations) {
                foreach ($NullCollaborations as $collaboration) {
                    $collaboration->delete();
                }
            }
        }

        /* снятие лицензии с юзера и возврат ее админу если это бизнес-юзер */
        $UserOwner = Users::findIdentity($this->owner_user_id);
        $User_for_Colleague = Users::findByEmail($this->colleague_email);
        if ($User_for_Colleague && $UserOwner) {

            /* отменить коллабы в которых БИЗНЕС-АДИМН($UserOwner) является коллегой для БИЗНЕС-ЮЗЕРА($User_for_Colleague) а не овнером */
            // (найти все коллаборации которые принадлежат $User_for_Colleague и в которых присутствует коллега $UserOwner)
            // для всех этих коллабораций выполнить colleagueDelete($UserOwner)
            // возможно эту часть стоит поместить в самое начало метода (для обработки и триал таким же образом)
            $query = "SELECT
                        t1.colleague_id,
                        t1.colleague_status,
                        t1.colleague_permission,
                        t1.colleague_invite_date,
                        t1.colleague_joined_date,
                        t2.file_uuid,
                        t3.user_email as colleague_email,
                        t3.user_id,
                        t1.collaboration_id,
                        0 as is_owner,
                        t3.license_type,
                        0 as awaiting_permissions
                      FROM {{%user_colleagues}} as t1
                      INNER JOIN {{%user_collaborations}} as t2 ON t1.collaboration_id = t2.collaboration_id
                      INNER JOIN {{%users}} as t3 ON t2.user_id = t3.user_id
                      WHERE (t2.user_id <> :user_id)
                      AND (t1.user_id = :user_id)
                      AND (t3.user_email = :colleague_email)
                      AND (t1.colleague_permission != :PERMISSION_OWNER)
                      AND (t1.colleague_status = :STATUS_JOINED)";
            $selfLeave = Yii::$app->db->createCommand($query, [
                'user_id'           => $this->owner_user_id,
                'colleague_email'   => $this->colleague_email,
                'PERMISSION_OWNER'  => UserColleagues::PERMISSION_OWNER,
                'STATUS_JOINED'     => UserColleagues::STATUS_JOINED,
            ])->queryAll();
            foreach ($selfLeave as $v) {
                $data['is_from_recursion'] = true;
                $data['colleague_id'] = $v['colleague_id'];
                $data['owner_user_id'] = $v['user_id'];
                $data['access_type'] = UserColleagues::PERMISSION_DELETE;
                $data['action'] = CollaborationApi::ACTION_DELETE;
                $data['uuid'] = $v['file_uuid'];
                $data['is_colleague_self_leave'] = true;

                $required = ['action', 'access_type', 'colleague_id', 'owner_user_id', 'uuid'];
                $model = new CollaborationApi($required);
                if ($model->load(['CollaborationApi' => $data]) && $model->validate()) {
                    $model->colleagueDelete();
                }
            }

            /* тут снятие лицензий уже для случая когда коллега БИЗНЕС-ЮЗЕР зарегистрирован в системе */
            if (($User_for_Colleague->license_business_from == $UserOwner->user_id) && ($User_for_Colleague->license_type == Licenses::TYPE_PAYED_BUSINESS_USER)) {
                $UserOwner->license_count_available++;
                $UserOwner->license_count_used--;
                $UserOwner->save();

                $User_for_Colleague->previous_license_business_from = $UserOwner->user_id;
                $User_for_Colleague->previous_license_business_finish = date(SQL_DATE_FORMAT);
                $User_for_Colleague->license_business_from = null;
                $User_for_Colleague->license_type = Licenses::TYPE_FREE_DEFAULT;
                $User_for_Colleague->license_expire = null;
                $User_for_Colleague->upl_limit_nodes = null;
                $User_for_Colleague->upl_shares_count_in24 = null;
                $User_for_Colleague->upl_max_shares_size = null;
                $User_for_Colleague->upl_max_count_children_on_copy = null;
                $User_for_Colleague->upl_block_server_nodes_above_bought = null;
                $User_for_Colleague->save();

                $UserLicense = UserLicenses::getLicenseUsedBy($UserOwner->user_id, $User_for_Colleague->user_id);
                if ($UserLicense) {
                    $UserLicense->lic_colleague_user_id = null;
                    $UserLicense->lic_colleague_email = null;
                    $UserLicense->save();
                }
            }

            /* Нотифицируем юзера о том что бизнес-админ лишил его лицензии */
            $notif = new Notifications();
            $notif->user_id = $User_for_Colleague->user_id;
            $notif->notif_isnew = Notifications::IS_NEW;
            $notif->notif_type = Notifications::TYPE_BUSINESS_ADMIN_REMOVE_YOUR_LICENSE;
            $notif->notif_data = serialize([
                'search' => [
                    '{business_admin_email}',
                    '{license_type}',
                ],
                'replace' => [
                    $UserOwner->user_email,
                    Licenses::getType($User_for_Colleague->license_type),
                ],
            ]);
            $notif->save();

        } elseif ($UserOwner) {
            /* тут снятие лицензий для случая когда коллега еще не зарегистрирован в системе был но бы приглашен БИЗНЕС_АДМИНОМ  */
            $UserOwner->license_count_available++;
            $UserOwner->license_count_used--;
            $UserOwner->save();

            $UserLicense = UserLicenses::getLicenseUsedBy($UserOwner->user_id, null, $this->colleague_email);
            if ($UserLicense) {
                $UserLicense->lic_colleague_user_id = null;
                $UserLicense->lic_colleague_email = null;
                $UserLicense->save();
            }
        }

    }
}
