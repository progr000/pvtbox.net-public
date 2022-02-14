<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\helpers\Json;
use common\helpers\FileSys;
use common\models\UserCollaborations;
use common\models\UserColleagues;
use common\models\Users;
use common\models\UserFiles;
use common\models\UserFileEvents;
use common\models\Notifications;
use common\models\Licenses;
use common\models\MailTemplatesStatic;
use common\models\UserLicenses;
use common\models\Preferences;
use common\models\QueuedEvents;
use common\models\RedisSafe;
use common\models\ColleaguesReports;
use frontend\models\Jobs\ColleagueAddJob;
use frontend\models\Jobs\ColleagueDeleteJob;

/**
 * CollaborationApi
 *
 * @property string $colleague_email
 * @property string $action
 * @property string $access_type
 * @property integer $colleague_id
 * @property integer $collaboration_id
 * @property string $colleague_message
 * @property integer $owner_user_id
 * @property integer $colleague_user_id
 * @property string $uuid
 * @property boolean $is_from_system
 * @property bool $is_colleague_self_leave
 *
 * @property \common\models\UserFiles $CollaboratedFolder
 * @property \common\models\Users $UserOwner
 * @property \common\models\UserCollaborations $UserCollaboration
 * @property \yii\redis\Connection $redis
 * @property \yii\queue\file\Queue $queue
 * @property \yii\mutex\FileMutex $mutex
 *
 */
class CollaborationApi extends Model
{
    const IS_JOIN    = 'join';
    const IS_INCLUDE = 'include';

    const ACTION_ADD    = 'add';
    const ACTION_DELETE = 'delete';
    const ACTION_EDIT   = 'edit';

    protected $CollaboratedFolder;
    protected $UserOwner;
    protected $UserCollaboration;
    protected $redis;
    protected $mutex;
    protected $queue;

    public $node_hash, $user_hash;

    public $colleague_email;
    public $action;
    public $access_type;
    public $colleague_id;
    public $collaboration_id;
    public $colleague_message;
    public $owner_user_id;
    public $colleague_user_id;
    public $uuid;
    public $is_from_system = false;
    public $is_colleague_self_leave;

    public $dynamic_rules = null;

    /**************************** +++ GLOBAL +++ ***************************/
    /**
     * NodeApi constructor.
     * @param array $required_fields Поля которые будут проверяться на наличие в джсоне
     --* @param \common\models\UserFiles $CollaboratedFolder
     */
    public function __construct(array $required_fields = []/*, UserFiles $CollaboratedFolder*/)
    {
        if (is_array($required_fields) && sizeof($required_fields)) {
            $this->dynamic_rules = [[$required_fields, 'required', 'message' => 'Fields ' . implode(', ', $required_fields) . ' are required.']];
        }
        $this->redis = Yii::$app->redis;
        $this->mutex = Yii::$app->mutex;
        $this->queue = (isset(Yii::$app->queue) && method_exists(Yii::$app->queue, 'push')) ? Yii::$app->queue : false;
        //$this->CollaboratedFolder = $CollaboratedFolder;

        parent::__construct();
    }

    /**
     * Правила валидации данных
     * @return array
     */
    public function rules()
    {
        $rules = [
            [['user_hash', 'node_hash'], 'string', 'length' => 128],
            [['uuid'], 'string', 'length' => 32],
            [['action'], 'in', 'range' => [self::ACTION_ADD, self::ACTION_DELETE, self::ACTION_EDIT]],
            [['colleague_message'], 'string'],
            [['access_type'], 'in', 'range' => [
                UserColleagues::PERMISSION_VIEW,
                UserColleagues::PERMISSION_EDIT,
                UserColleagues::PERMISSION_DELETE
            ]],
            [['collaboration_id', 'colleague_id', 'owner_user_id', 'colleague_user_id'], 'integer'],
            [['colleague_email'], 'email'],
            [['is_from_system', 'is_colleague_self_leave'], 'boolean'],
            [['is_colleague_self_leave'], 'default', 'value' => false],
        ];
        if (is_array($this->dynamic_rules)) {
            return array_merge($this->dynamic_rules, $rules);
        } else {
            return $rules;
        }
    }
    /**************************** --- GLOBAL --- ***************************/


    /************************ +++ COLLABORATION  +++ ***********************/
    /**
     * @param integer $user_id
     */
    public function initOwner($user_id)
    {
        $this->owner_user_id = $user_id;
    }

    private function initColleagueIDByUserID()
    {
        $UserColleague = UserColleagues::findOne([
            'collaboration_id' => $this->UserCollaboration->collaboration_id,
            'user_id'          => $this->colleague_user_id,
        ]);
        if ($UserColleague) {
            $this->colleague_id = $UserColleague->colleague_id;
            return true;
        }

        return false;
    }

    /**
     *
     */
    public function collaborationInfo()
    {
        /* Проверим что папка-файл реально существует */
        $UserFile = UserFiles::findOne([
            'file_uuid'  => $this->uuid,
            'is_folder'  => UserFiles::TYPE_FOLDER,
            'user_id'    => $this->owner_user_id,
            'is_deleted' => UserFiles::FILE_UNDELETED,
        ]);
        if (!$UserFile) {
            return [
                'status' => false,
                'info'   => "Folder not found.",
            ];
        }

        /* проверка что файл не удаили до этого */
        if ($UserFile->last_event_type == UserFileEvents::TYPE_DELETE) {
            return [
                'status'  => false,
                'info'    => "File was deleted. You can't do any actions with this file."
            ];
        }

        /* Сейчас запрещено создавать коллаборацию для папок которые не в корне. Тут это можно потом убрать. */
        if ($UserFile->file_parent_id) {
            return [
                'status' => false,
                'info'   => "Collaboration for non-root folder not implemented",
                //'debug'  => '',
            ];
        }

        $UserCollaboration = UserCollaborations::findOne(['file_uuid' => $this->uuid]);
        if ($UserCollaboration) {
            $data['collaboration_id'] = $UserCollaboration->collaboration_id;
            $data['collaboration_owner'] = $UserCollaboration->user_id;
            $data['collaboration_is_owner'] = ($UserCollaboration->user_id == $this->owner_user_id);
            $data['access_type'] = null;
            $UserColleagues = UserColleagues::find()
                ->where(['collaboration_id' => $UserCollaboration->collaboration_id])
                //->andWhere('(user_id!=:user_id) OR (user_id IS NULL)', [':user_id' => $this->owner_user_id])
                //->asArray()
                ->all();
            if ($UserColleagues) {
                /** @var \common\models\UserColleagues $colleague */
                foreach ($UserColleagues as $colleague) {
                    $data['colleagues'][] = UserColleagues::prepareColleagueData($colleague);
                    if ($this->owner_user_id == $colleague->user_id) { $data['access_type'] = $colleague->colleague_permission; }
                }
            } else {
                $data['colleagues'] = [];
            }

            return [
                'status' => true,
                'data'   => $data,
            ];
        }
        return [
            'status' => true,
            'data'   => null,
        ];
        /*
        return [
            'status' => false,
            'info'   => "Collaboration for folder with file_uuid={$this->uuid} not found.",
        ];
        */
    }

    /**
     * Инициализация работы с папкой коллаборации (создание если нет)
     * @return array
     */
    protected function collaborationInit()
    {
        /* ищем овнера коллаборации */
        $this->UserOwner = Users::getPathNodeFS($this->owner_user_id);
        if (!$this->UserOwner) {
            return [
                'status' => false,
                'info'   => 'User-Owner not found.',
            ];
        }

        /* Если коллаборация создается фришной лицензией */
        /*
        if (!$this->is_from_system) {
            if ($this->UserOwner->license_type == Licenses::TYPE_FREE_DEFAULT) {
                return [
                    'status' => false,
                    'info' => 'license-restriction',
                ];
            }
        }
        */

        /* Проверим что папка-файл реально существует */
        $this->CollaboratedFolder = UserFiles::findOne([
            'file_uuid'  => $this->uuid,
            'is_folder'  => UserFiles::TYPE_FOLDER,
            'user_id'    => $this->UserOwner->user_id,
            'is_deleted' => UserFiles::FILE_UNDELETED,
        ]);
        if (!$this->CollaboratedFolder) {
            return [
                'status' => false,
                'info'   => 'Folder not found.',
            ];
        }

        /* проверка что файл не удаили до этого */
        if ($this->CollaboratedFolder->last_event_type == UserFileEvents::TYPE_DELETE) {
            return [
                'status'  => false,
                'info'    => "File was deleted. You can't do any actions with this file."
            ];
        }

        /* Сейчас запрещено создавать коллаборацию для папок которые не в корне. Тут это можно потом убрать. */
        if ($this->CollaboratedFolder->file_parent_id) {
            return [
                'status' => false,
                'info'   => "Can't create new collaboration for non-root folder",
                //'debug'  => '',
            ];
        }

        /* Проверим что папка не состоит в коллаборации более верхнего уровня ее родительской папки*/
        if (!$this->CollaboratedFolder->is_collaborated && $this->CollaboratedFolder->collaboration_id) {
            return [
                'status' => false,
                'info'   => 'This folder is under other collaboration of a parent folder.',
            ];
        }

        /* Находим нужную нам коллабоарацию или создаем ее если ее нет и это новая коллаборация */
        $this->UserCollaboration = UserCollaborations::findOne(['file_uuid' => $this->CollaboratedFolder->file_uuid]);
        //var_dump($this->UserCollaboration); var_dump($this->action); exit;
        if (!$this->UserCollaboration && ($this->action == 'add')) {

            if (($this->UserOwner->license_type == Licenses::TYPE_PAYED_BUSINESS_USER) && (Yii::$app->params['self_hosted'])) {
                // в этом случае колаба не будет создана
                return [
                    'status' => false,
                    'info' => Yii::t('app/flash-messages', "license-restriction-businessUser-invite-non-registered"),
                ];
            }

            $this->UserCollaboration                       = new UserCollaborations();
            //var_dump($this->UserCollaboration); exit;
            $this->UserCollaboration->file_uuid            = $this->CollaboratedFolder->file_uuid;
            $this->UserCollaboration->collaboration_status = UserCollaborations::STATUS_ACTIVE;
            $this->UserCollaboration->user_id              = $this->UserOwner->user_id;
            if ($this->UserCollaboration->save()) {

                /* Отправляем в редис */
                try {
                    $this->redis->publish("collaboration:{$this->UserCollaboration->collaboration_id}:create", "create");
                    $this->redis->save();
                } catch (\Exception $e) {}

                /* Создаем владельца коллаборации в списке коллег */
                $UserColleague                        = new UserColleagues();
                $UserColleague->collaboration_id      = $this->UserCollaboration->collaboration_id;
                $UserColleague->user_id               = $this->UserCollaboration->user_id;
                $UserColleague->colleague_status      = UserColleagues::STATUS_JOINED;
                $UserColleague->colleague_permission  = UserColleagues::PERMISSION_OWNER;
                $UserColleague->colleague_email       = $this->UserOwner->user_email;
                $UserColleague->colleague_joined_date = date(SQL_DATE_FORMAT);
                if (!$UserColleague->save()) {
                    return [
                        'status' => false,
                        'info'   => "Database error: Can't init new collaboration (Colleague-Owner false).",
                        'debug'  => $UserColleague->getErrors(),
                    ];
                }

                /* создаем репорт для овнера запись о созданной ИМ коллаборации */
                $Report = ColleaguesReports::createNewReport(
                    [
                        'data' => [
                            'event_type_int'                  => ColleaguesReports::EXT_RPT_TYPE_COLLABORATION_CREATED,
                            'event_id'                        => 0,
                            'file_id'                         => $this->CollaboratedFolder->file_id,
                            'file_parent_id'                  => $this->CollaboratedFolder->file_parent_id,
                            'file_parent_id_before_event'     => $this->CollaboratedFolder->file_parent_id,
                            'file_name'                       => $this->CollaboratedFolder->file_name,
                            'file_name_before_event'          => $this->CollaboratedFolder->file_name,
                            'parent_folder_name_after_event'  => '',
                            'parent_folder_name_before_event' => '',
                            'is_folder'                       => $this->CollaboratedFolder->is_folder,
                        ]
                    ],
                    $UserColleague,
                    NodeApi::registerNodeFM($this->UserOwner)
                );

                /* Отправляем в редис */
                try {
                    $this->redis->publish("collaboration:{$this->UserCollaboration->collaboration_id}:useradd", $UserColleague->user_id);
                    $this->redis->sadd("collaboration:{$UserColleague->user_id}:folders", $this->UserCollaboration->file_uuid);
                    $this->redis->del("collaboration:{$this->UserCollaboration->collaboration_id}:users");
                    $this->redis->sadd("collaboration:{$this->UserCollaboration->collaboration_id}:users", $UserColleague->user_id);
                    $this->redis->sadd("user:{$UserColleague->user_id}:collaborations", $this->UserCollaboration->collaboration_id);
                    $this->redis->save();
                } catch (\Exception $e) {
                    RedisSafe::createNewRecord(
                        RedisSafe::TYPE_COLLABORATION_CHANGES,
                        $UserColleague->user_id,
                        null,
                        Json::encode([
                            'collaboration_id' => $this->UserCollaboration->collaboration_id,
                            'action'           => 'useradd',
                            'chanel'           => "collaboration:{$this->UserCollaboration->collaboration_id}:useradd",
                            'user_id'          => $UserColleague->user_id,
                        ])
                    );
                }

            } else {
                return [
                    'status' => false,
                    'info'   => "Database error: Can't init new collaboration.",
                    'debug'  => $this->UserCollaboration->getErrors(),
                ];
            }
        }

        /* Если не удалось найти или создать коллаборацию - то выход с ошибкой */
        if (!$this->UserCollaboration) {
            return [
                'status' => false,
                'info'   => "Database error: Can't find or init new collaboration.",
                //'debug'  => $this->UserCollaboration->getErrors(),
            ];
        }

        /* Проверим что управление коллаборацией происводит ее владелец */
        if ($this->UserCollaboration->user_id != $this->UserOwner->user_id) {
            return [
                'status' => false,
                'info'   => 'Access error. You are not owner.',
            ];
        }

        return true;
    }

    /**
     * Отмена коллаборации для папки
     * @return array
     */
    public function collaborationDelete()
    {
        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();

        /* Находим нужную нам коллабоарацию или создаем ее если ее нет и это новая коллаборация */
        $initStatus = $this->collaborationInit();
        if (is_array($initStatus)) {
            return $initStatus;
        }

        /* Проверим что папка-файл находится в коллаборации на данный момент */
        if (!$this->CollaboratedFolder->is_collaborated) {
            return [
                'status' => false,
                'info'   => "This folder isn't under collaboration.",
            ];
        }

        /* */
        $Colleagues = UserColleagues::find()
            /*
            ->where("(collaboration_id = :collaboration_id) AND ((user_id != :owner_user_id) OR (user_id IS NULL))", [
                'collaboration_id' => $this->UserCollaboration->collaboration_id,
                'owner_user_id'    => $this->UserCollaboration->user_id,
            ])
            */
            ->where("(collaboration_id = :collaboration_id) AND (colleague_permission != :colleague_permission)", [
                'collaboration_id'     => $this->UserCollaboration->collaboration_id,
                'colleague_permission' => UserColleagues::PERMISSION_OWNER,
            ])
            ->all();
        if ($Colleagues) {
            foreach ($Colleagues as $colleague) {
                /** @var \common\models\UserColleagues $colleague */
                $this->colleague_id      = $colleague->colleague_id;
                $ret = $this->colleagueDelete();
                /*
                 * Тут теперь отключена проверка в связи с очередью
                if (!$ret['status']) {
                    $transaction->rollBack();
                    return $ret;
                }
                */
            }
        }
        $transaction->commit();
        return [
            'status' => true,
        ];
    }

    /**
     * @param $UserCollaboration_user_id
     * @param $UserColleague_colleague_email
     * @throws \Exception
     * @throws \Throwable
     */
    protected static function clear_null_collaboration($UserCollaboration_user_id, $UserColleague_colleague_email)
    {
        $res = Yii::$app->db->createCommand("
            DELETE FROM {{%user_collaborations}} as t1
            USING {{%user_colleagues}} as t2
            WHERE (t1.collaboration_id = t2.collaboration_id)
            AND ((t2.colleague_email=:colleague_email))
            AND (t1.user_id = :user_id)
            AND (t1.file_uuid IS NULL);
        ", [
            'user_id'         => $UserCollaboration_user_id,
            'colleague_email' => $UserColleague_colleague_email,
        ])->execute();
        //var_dump($res);exit;
        /*
        $NullCollaborations = UserCollaborations::find()
            ->alias('t1')
            ->select([
                't1.collaboration_id',
            ])
            ->innerJoin('{{%user_colleagues}} as t2', '(t1.collaboration_id = t2.collaboration_id)')
            ->where('(t2.colleague_email=:colleague_email) AND (t1.user_id = :user_id) AND (t1.file_uuid IS NULL)', [
                'user_id'         => $UserCollaboration_user_id,
                'colleague_email' => $UserColleague_colleague_email,
            ])->all();
        if ($NullCollaborations) {
            foreach ($NullCollaborations as $collaboration) {
                $collaboration->delete();
            }
        }
        */
    }

    /**
     * Проверяет принял ли ранее коллега приглашение от этого овнера в другую коллаборацию
     * @param string $colleague_email
     * @param integer $owner_user_id
     * @return bool
     */
    public static function check_is_colleague_joined_before($colleague_email, $owner_user_id)
    {
        $query = "SELECT
                    t1.colleague_id
                  FROM {{%user_colleagues}} as t1
                  INNER JOIN {{%user_collaborations}} as t2 ON t1.collaboration_id = t2.collaboration_id
                  WHERE (t1.colleague_email = :colleague_email)
                  AND (t2.user_id = :owner_user_id)
                  AND (t1.colleague_status IN (:STATUS_JOINED, :STATUS_QUEUED_ADD))";
        $test = Yii::$app->db->createCommand($query, [
            'colleague_email'   => $colleague_email,
            'owner_user_id'     => $owner_user_id,
            'STATUS_JOINED'     => UserColleagues::STATUS_JOINED,
            'STATUS_QUEUED_ADD' => UserColleagues::STATUS_QUEUED_ADD,
        ])->queryOne();
        return ($test && is_array($test));
    }

    /**
     * Приглашение коллеги в коллаборацию
     * @return array
     */
    public function colleagueInvite()
    {
        /* colleague_email - required */

        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();

        /* Находим нужную нам коллабоарацию или создаем ее если ее нет и это новая коллаборация */
        $initStatus = $this->collaborationInit();
        if (is_array($initStatus)) {
            $transaction->rollBack();
            return $initStatus;
        }

        /* Проверим есть ли уже такой коллега в списке */
        $isColleagueExists = UserColleagues::findOne([
            'colleague_email'  => $this->colleague_email,
            'collaboration_id' => $this->UserCollaboration->collaboration_id,
        ]);
        if ($isColleagueExists) {
            $transaction->rollBack();
            if ($isColleagueExists->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                return [
                    'status' => false,
                    'info'   => "Can't add self into collaboration.",
                ];
            } elseif ($isColleagueExists->colleague_status == UserColleagues::STATUS_QUEUED_ADD) {
                return [
                    'status' => false,
                    'info'   => "Colleague already set in queue fro add on this collaboration.",
                    'hidden_info' => true,
                ];
            } elseif ($isColleagueExists->colleague_status == UserColleagues::STATUS_QUEUED_DEL) {
                return [
                    'status' => false,
                    'info'   => "Colleague set in queue for delete from this collaboration. Wait while it will be deleted.",
                    'hidden_info' => true,
                ];
            } elseif ($isColleagueExists->colleague_status == UserColleagues::STATUS_INVITED) {
                return [
                    'status' => false,
                    'info'   => "Colleague already invited to this collaboration. Please wait until invitation is accepted.",
                ];
            } else {
                return [
                    'status' => false,
                    'info'   => "Colleague already exist in this collaboration.",
                ];
            }
        }

        /* Ищем пользователя соответствующего емейлу приглашаемого коллеги */
        $User_for_Colleague = Users::findByEmail($this->colleague_email);


        /*  +++ проверки возможности коллаборации между юзерами */
        /* Если приглашаемый коллега уже зарегистрирован в системе */
        if ($User_for_Colleague) {
            /* Если инвайт пришел от юзера TYPE_FREE_DEFAULT то несколько вариантов зависит от того какая у приглашенного лицензия */
            if ($this->UserOwner->license_type == Licenses::TYPE_FREE_DEFAULT) {
                /* любой кроме бизнес-админа - запрет */
                if ($User_for_Colleague->license_type != Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                    $license_restriction = Yii::t('app/flash-messages', "license_restriction_free_invite_any");
                    $license_restriction_type = "error";
                }
            }
            /* если инвайт пришел от юзера TYPE_FREE_TRIAL к юзеру TYPE_FREE_DEFAULT то запретить */
            elseif ($this->UserOwner->license_type == Licenses::TYPE_FREE_TRIAL && $User_for_Colleague->license_type == Licenses::TYPE_FREE_DEFAULT) {
                $license_restriction =  Yii::t('app/flash-messages', "license_restriction_trial_invite_free");
                $license_restriction_type = "error";
            }
            /* если инвайт пришел от юзера TYPE_PAYED_PROFESSIONAL к юзеру TYPE_FREE_DEFAULT то запретить */
            elseif ($this->UserOwner->license_type == Licenses::TYPE_PAYED_PROFESSIONAL && $User_for_Colleague->license_type == Licenses::TYPE_FREE_DEFAULT) {
                $license_restriction = Yii::t('app/flash-messages', "license_restriction_pro_invite_free");
                $license_restriction_type = "error";
            }
            /* если инвайт пришел от юзера TYPE_PAYED_BUSINESS_USER к юзеру TYPE_FREE_DEFAULT то запретить */
            elseif ($this->UserOwner->license_type == Licenses::TYPE_PAYED_BUSINESS_USER && $User_for_Colleague->license_type == Licenses::TYPE_FREE_DEFAULT) {
                $license_restriction = Yii::t('app/flash-messages', "license_restriction_businessUser_invite_free");
                $license_restriction_type = "error";
            }
            /* если инвайт пришел от TYPE_PAYED_BUSINESS_ADMIN к юзерам TYPE_FREE_DEFAULT или TYPE_FREE_TRIAL то они получают лицензию TYPE_PAYED_BUSINESS_USER (сразу в момент инвайта) */
            /* с TYPE_PAYED_BUSINESS_ADMIN списывается лицензия сразу в момент инвайта */
            elseif ($this->UserOwner->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN && in_array($User_for_Colleague->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])) {
                /* если у TYPE_PAYED_BUSINESS_ADMIN есть свободные лицензии - тогда списываем одну и сообщаем ему об этом */
                $UserLicense = UserLicenses::getFreeLicense($this->UserOwner->user_id);
                if ($UserLicense) {

                    /* Проверка что пользователь ранее не был удален из коллег и период блокировки не закончен */
                    if ($User_for_Colleague->previous_license_business_from == $this->UserOwner->user_id) {
                        $InviteLockPeriod = Preferences::getValueByKey('InviteLockPeriod', 24, 'integer') * 3600;
                        $UserLastDate = strtotime($User_for_Colleague->previous_license_business_finish);
                        if ($UserLastDate + $InviteLockPeriod >= time()) {
                            $transaction->rollBack();
                            return [
                                'status' => false,
                                'info' => Yii::t('app/flash-messages', "license_restriction_businessAdmin_invites_the_user_repeatedly"),
                            ];
                        }
                    }

                    //$User_for_Colleague->license_type = Licenses::TYPE_PAYED_BUSINESS_USER;
                    //$User_for_Colleague->save();
                    $this->UserOwner->license_count_available--;
                    $this->UserOwner->license_count_used++;
                    $this->UserOwner->save();

                    $User_for_Colleague->license_expire = $UserLicense->lic_end;
                    $User_for_Colleague->license_type = Licenses::TYPE_PAYED_BUSINESS_USER;
                    $User_for_Colleague->license_business_from = $this->UserOwner->user_id;
                    $User_for_Colleague->upl_limit_nodes = $this->UserOwner->upl_limit_nodes;
                    $User_for_Colleague->upl_shares_count_in24 = $this->UserOwner->upl_shares_count_in24;
                    $User_for_Colleague->upl_max_shares_size = $this->UserOwner->upl_max_shares_size;
                    $User_for_Colleague->upl_max_count_children_on_copy = $this->UserOwner->upl_max_count_children_on_copy;
                    $User_for_Colleague->upl_block_server_nodes_above_bought = $this->UserOwner->upl_block_server_nodes_above_bought;
                    $User_for_Colleague->save();

                    $UserLicense->lic_colleague_user_id = $User_for_Colleague->user_id;
                    $UserLicense->lic_colleague_email = $User_for_Colleague->user_email;
                    $UserLicense->save();

                    $license_restriction = Yii::t('app/flash-messages', "license_minus_businessAdmin_invite_free_or_trial");
                    $license_restriction_type = "success";
                /* если же свободных лицензий нет - сообщаем об этом */
                } else {
                    $transaction->rollBack();
                    return [
                        'status' => false,
                        'info'   => Yii::t('app/flash-messages', "license_restriction_businessAdmin_invite_free_or_trial_but_no_available_licenses"),
                    ];
                }
            }
        /* Иначе если коллега еще не зарегистрирован в системе */
        } else {
            /* Если инвайт пришел от юзера TYPE_FREE_DEFAULT то запрет */
            if ($this->UserOwner->license_type == Licenses::TYPE_FREE_DEFAULT) {
                $license_restriction = Yii::t('app/flash-messages', "license_restriction_free_invite_non_registered");
                $license_restriction_type = "error";
            }
            /* если инвайт пришел от юзера TYPE_FREE_TRIAL то ... */
            elseif ($this->UserOwner->license_type == Licenses::TYPE_FREE_TRIAL) {
                //$license_restriction = "license-restriction-trial-invite-non-registered";
                //$license_restriction_type = "success";
            }
            /* если инвайт пришел от юзера TYPE_PAYED_PROFESSIONAL то ... */
            elseif ($this->UserOwner->license_type == Licenses::TYPE_PAYED_PROFESSIONAL) {
                //$license_restriction = "license-restriction-pro-invite-non-registered";
                //$license_restriction_type = "success";
            }
            /* если инвайт пришел от юзера TYPE_PAYED_BUSINESS_USER к юзеру TYPE_FREE_DEFAULT то запретить */
            elseif ($this->UserOwner->license_type == Licenses::TYPE_PAYED_BUSINESS_USER) {
                //$license_restriction = "license-restriction-businessUser-invite-non-registered";
                //$license_restriction_type = "success";
                if (Yii::$app->params['self_hosted']) {
                    $transaction->rollBack();
                    return [
                        'status' => false,
                        'info' => Yii::t('app/flash-messages', "license-restriction-businessUser-invite-non-registered"),
                    ];
                }
            }
            /* если инвайт пришел от TYPE_PAYED_BUSINESS_ADMIN (пользователь после регистрации получит лицензию TYPE_PAYED_BUSINESS_USER в момент джойна) */
            /* с TYPE_PAYED_BUSINESS_ADMIN списывается лицензия сразу в момент инвайта */
            elseif ($this->UserOwner->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                /* если у TYPE_PAYED_BUSINESS_ADMIN есть свободные лицензии - тогда списываем одну и сообщаем ему об этом */
                $UserLicense = UserLicenses::getFreeLicenseForNonRegistered($this->UserOwner->user_id, $this->colleague_email);
                if ($UserLicense) {
                    //$User_for_Colleague->license_type = Licenses::TYPE_PAYED_BUSINESS_USER;
                    //$User_for_Colleague->save();
                    $this->UserOwner->license_count_available--;
                    $this->UserOwner->license_count_used++;
                    $this->UserOwner->save();

                    $UserLicense->lic_colleague_user_id = null;
                    $UserLicense->lic_colleague_email = $this->colleague_email;
                    $UserLicense->save();

                    $license_restriction =  Yii::t('app/flash-messages', "license_minus_businessAdmin_invite_non_registered");
                    $license_restriction_type = "success";
                /* если же свободных лицензий нет - сообщаем об этом но инвайт отправляем */
                } else {
                    $transaction->rollBack();
                    return [
                        'status' => false,
                        'info'   => Yii::t('app/flash-messages', "license_restriction_businessAdmin_invite_non_registered_but_no_available_licenses"),
                    ];
                }
            }
        }
        /*  --- проверки возможности коллаборации между юзерами */


        /* создаем запись о новом коллеге в бд */
        $UserColleague = new UserColleagues();
        $UserColleague->collaboration_id = $this->UserCollaboration->collaboration_id;
        $UserColleague->user_id = $User_for_Colleague ? $User_for_Colleague->user_id : null;
        $UserColleague->colleague_status = UserColleagues::STATUS_INVITED;
        $UserColleague->colleague_permission = $this->access_type;
        $UserColleague->colleague_email = $this->colleague_email;
        if ($UserColleague->save()) {

            /* ставим отметку что файл учавствует в коллаборации */
            $this->CollaboratedFolder->is_collaborated  = UserFiles::FILE_COLLABORATED;
            $this->CollaboratedFolder->collaboration_id = $this->UserCollaboration->collaboration_id;
            $this->CollaboratedFolder->is_owner = UserFiles::IS_OWNER;

            if ($this->CollaboratedFolder->save()) {

                /* записываем информацию о папке-файле коллаборации в пустышку-инфо */
                $relativePath = UserFiles::getFullPath($this->CollaboratedFolder);
                $file_name = $this->UserOwner->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                if ($this->CollaboratedFolder->is_folder) {
                    $info_file = $file_name . DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
                } else {
                    $info_file = $file_name;
                }
                FileSys::touch($info_file, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
                UserFiles::createFileInfo($info_file, $this->CollaboratedFolder);


                /* Создаем нотификайшн о ПРИГЛАШЕНИИ (invite) ++создаем письмо */
                MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_CollaborationInvite, $UserColleague->colleague_email, [
                    'UserColleagueObject'      => $UserColleague,
                    'UserObject'               => $User_for_Colleague ? $User_for_Colleague : null,
                    'UserOwner_email'          => $this->UserOwner->user_email,
                    'UserOwner_name'           => $this->UserOwner->user_name,
                    'invite_colleague_message' => trim($this->colleague_message),
                ]);
                if ($User_for_Colleague) {
                    $notif = new Notifications();
                    $notif->user_id = $User_for_Colleague->user_id;
                    $notif->notif_isnew = Notifications::IS_NEW;
                    $notif->notif_type = Notifications::TYPE_COLLABORATION_INVITE;
                    $notif->notif_data = serialize([
                        'search' => [
                            '{folder_name}',
                            '{user_email}',
                            '{access_type}',
                            '{colleague_id}',
                            '{file_uuid}',
                            //'{accept_link}',
                        ],
                        'replace' => [
                            $this->CollaboratedFolder->file_name,
                            $this->UserOwner->user_email,
                            $this->access_type,
                            $UserColleague->colleague_id,
                            $this->CollaboratedFolder->file_uuid,
                            //Yii::$app->urlManager->createAbsoluteUrl(['user/accept-collaboration', 'colleague_id' => $UserColleague->colleague_id]),
                        ],
                        'links_data' => [
                            'accept_link' => ['user/accept-collaboration', 'colleague_id' => $UserColleague->colleague_id],
                        ],
                    ]);
                    $notif->save();
                }

                /* Чистка нулл-коллабораций у коллеги */
                self::clear_null_collaboration($this->UserCollaboration->user_id, $UserColleague->colleague_email);

                /* Заканчиваем транзакцию успешно и отправляем данные о новом колеге */
                $transaction->commit();
                $data_send = UserColleagues::prepareColleagueData($UserColleague);
                $data_send['collaboration_id'] = $UserColleague->collaboration_id;
                $data_send['file_uuid'] = $this->CollaboratedFolder->file_uuid;
                $data_send['file_name'] = $this->CollaboratedFolder->file_name;
                if (isset($license_restriction)) {
                    $data_send['license_restriction'] = $license_restriction;
                    $data_send['license_restriction_type'] = isset($license_restriction_type)
                        ? $license_restriction_type
                        : 'error';
                }
                return [
                    'status' => true,
                    'action' => $this->action,
                    'data'   => $data_send,
                    //'event_data' => $event_data,
                ];
            } else {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info'   => 'Cant save UserFIle info for collaboration',
                    'debug'  => $this->CollaboratedFolder->getErrors(),
                ];
            }
        } else {
            $transaction->rollBack();
            return [
                'status' => false,
                'info'   => "Database error: Can't add new colleague to collaboration.",
                'debug'  => $UserColleague->getErrors(),
            ];
        }
    }

    /**
     * Принятие коллегой приглашения в коллаборацию (пока не задействовано)
     * @return array
     */
    public function colleagueJoin()
    {
        /* collaboration_id - required */
        /* colleague_id - required */

        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();

        /* Находим нужную нам коллабоарацию или создаем ее если ее нет и это новая коллаборация */
        $initStatus = $this->collaborationInit();
        if (is_array($initStatus)) {
            $transaction->rollBack();
            return $initStatus;
        }

        /* находим запись о коллеге в бд */
        $UserColleague = UserColleagues::findOne([
            'colleague_id'  => $this->colleague_id,
            'collaboration_id' => $this->collaboration_id,
        ]);
        if (!$UserColleague) {
            $transaction->rollBack();
            return [
                'status' => false,
                'info' => "Database error: Can't find colleague with colleague_id={$this->colleague_id}.",
                //'debug' => $UserColleague->getErrors(),
            ];
        }

        /* Пробуем найти юзера который соответствует этому коллеге */
        if (!$UserColleague->user_id) {
            $User_for_Colleague = Users::findByEmail($UserColleague->colleague_email);
            $user_was_non_registered = true;
            if (!$User_for_Colleague) {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info' => "Database error: Can't JOIN colleague that not exist in tables Users.",
                    //'debug' => $User_for_Colleague->getErrors(),
                ];
            }
            $UserColleague->user_id = $User_for_Colleague->user_id;
        } else {
            $User_for_Colleague = Users::findIdentity($UserColleague->user_id);
        }
        if (!$User_for_Colleague) {
            $transaction->rollBack();
            return [
                'status' => false,
                'info' => "Database error: Can't find user_id for colleague_id={$this->colleague_id}.",
                //'debug' => $UserColleague->getErrors(),
            ];
        }


        /*  +++ проверки возможности коллаборации между юзерами */
        /* Если инвайт пришел от юзера TYPE_FREE_DEFAULT то несколько вариантов зависит от того какая у приглашенного лицензия */
        if ($this->UserOwner->license_type == Licenses::TYPE_FREE_DEFAULT) {
            /* любой кроме бизнес-админа - запрет */
            if ($User_for_Colleague->license_type != Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info'   => Yii::t('app/flash-messages', "license_restriction_any_try_join_from_free"),
                ];
            }
            /* если бизнес админ имеет свободные лицензии то списываем одну иначе запрет */
            $UserLicense = UserLicenses::getFreeLicense($User_for_Colleague->user_id);
            if ($UserLicense) {

                /* Проверка что FREE пользователь ранее не был удален из коллег и период блокировки не закончен */
                if ($this->UserOwner->previous_license_business_from == $User_for_Colleague->user_id) {
                    $InviteLockPeriod = Preferences::getValueByKey('InviteLockPeriod', 24, 'integer') * 3600;
                    $UserLastDate = strtotime($this->UserOwner->previous_license_business_finish);
                    if ($UserLastDate + $InviteLockPeriod >= time()) {
                        $transaction->rollBack();
                        return [
                            'status' => false,
                            'info' => Yii::t('app/flash-messages', "license_restriction_free_invites_businessAdmin_repeatedly"),
                        ];
                    }
                }

                $this->UserOwner->license_expire = $UserLicense->lic_end;
                $this->UserOwner->license_business_from = $User_for_Colleague->user_id;
                $this->UserOwner->license_type = Licenses::TYPE_PAYED_BUSINESS_USER;
                $this->UserOwner->upl_limit_nodes = $User_for_Colleague->upl_limit_nodes;
                $this->UserOwner->upl_shares_count_in24 = $User_for_Colleague->upl_shares_count_in24;
                $this->UserOwner->upl_max_shares_size = $User_for_Colleague->upl_max_shares_size;
                $this->UserOwner->upl_max_count_children_on_copy = $User_for_Colleague->upl_max_count_children_on_copy;
                $this->UserOwner->upl_block_server_nodes_above_bought = $User_for_Colleague->upl_block_server_nodes_above_bought;
                $this->UserOwner->save();

                $User_for_Colleague->license_count_available--;
                $User_for_Colleague->license_count_used++;
                $User_for_Colleague->save();

                //$UserLicense->lic_colleague_user_id = $this->UserOwner->user_id;
                //$UserLicense->lic_colleague_email = $User_for_Colleague->user_email;
                $UserLicense->lic_colleague_user_id = $this->UserOwner->user_id;
                $UserLicense->lic_colleague_email = $this->UserOwner->user_email;
                $UserLicense->save();

                $license_restriction = Yii::t('app/flash-messages', "license_minus_businessAdmin_invite_free_or_trial");
                $license_restriction_type = "success";

            } else {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info'   => Yii::t('app/flash-messages', "license_restriction_businessAdmin_with_0_available_licenses_try_join_from_free"),
                ];
            }
        }
        /* Если инвайт пришел от юзера TYPE_FREE_TRIAL к юзеру TYPE_PAYED_BUSINESS_ADMIN то нужно списать лицензию */
        if ($this->UserOwner->license_type == Licenses::TYPE_FREE_TRIAL && $User_for_Colleague->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {

            /* если бизнес админ имеет свободные лицензии то списываем одну иначе запрет */
            $UserLicense = UserLicenses::getFreeLicense($User_for_Colleague->user_id);
            if ($UserLicense) {

                /* Проверка что FREE пользователь ранее не был удален из коллег и период блокировки не закончен */
                if ($this->UserOwner->previous_license_business_from == $User_for_Colleague->user_id) {
                    $InviteLockPeriod = Preferences::getValueByKey('InviteLockPeriod', 24, 'integer') * 3600;
                    $UserLastDate = strtotime($this->UserOwner->previous_license_business_finish);
                    if ($UserLastDate + $InviteLockPeriod >= time()) {
                        $transaction->rollBack();
                        return [
                            'status' => false,
                            'info' => Yii::t('app/flash-messages', "license_restriction_free_invites_businessAdmin_repeatedly"),
                        ];
                    }
                }

                $this->UserOwner->license_expire = $UserLicense->lic_end;
                $this->UserOwner->license_business_from = $User_for_Colleague->user_id;
                $this->UserOwner->license_type = Licenses::TYPE_PAYED_BUSINESS_USER;
                $this->UserOwner->upl_limit_nodes = $User_for_Colleague->upl_limit_nodes;
                $this->UserOwner->upl_shares_count_in24 = $User_for_Colleague->upl_shares_count_in24;
                $this->UserOwner->upl_max_shares_size = $User_for_Colleague->upl_max_shares_size;
                $this->UserOwner->upl_max_count_children_on_copy = $User_for_Colleague->upl_max_count_children_on_copy;
                $this->UserOwner->upl_block_server_nodes_above_bought = $User_for_Colleague->upl_block_server_nodes_above_bought;
                $this->UserOwner->save();

                $User_for_Colleague->license_count_available--;
                $User_for_Colleague->license_count_used++;
                $User_for_Colleague->save();

                //$UserLicense->lic_colleague_user_id = $this->UserOwner->user_id;
                //$UserLicense->lic_colleague_email = $User_for_Colleague->user_email;
                $UserLicense->lic_colleague_user_id = $this->UserOwner->user_id;
                $UserLicense->lic_colleague_email = $this->UserOwner->user_email;
                $UserLicense->save();

                $license_restriction = Yii::t('app/flash-messages', "license_minus_businessAdmin_invite_free_or_trial");
                $license_restriction_type = "success";

            } else {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info'   => Yii::t('app/flash-messages', "license_restriction_businessAdmin_with_0_available_licenses_try_join_from_free"),
                ];
            }
        }
        /* если инвайт пришел от юзера TYPE_FREE_TRIAL к юзеру TYPE_FREE_DEFAULT то запретить */
        if ($this->UserOwner->license_type == Licenses::TYPE_FREE_TRIAL && $User_for_Colleague->license_type == Licenses::TYPE_FREE_DEFAULT) {
            $transaction->rollBack();
            return [
                'status' => false,
                'info'   => Yii::t('app/flash-messages', "license_restriction_free_try_join_from_trial"),
            ];
        }
        /* если инвайт пришел от юзера TYPE_PAYED_PROFESSIONAL к юзеру TYPE_FREE_DEFAULT то запретить */
        if ($this->UserOwner->license_type == Licenses::TYPE_PAYED_PROFESSIONAL && $User_for_Colleague->license_type == Licenses::TYPE_FREE_DEFAULT) {
            $transaction->rollBack();
            return [
                'status' => false,
                'info'   => Yii::t('app/flash-messages', "license_restriction_free_try_join_from_pro"),
            ];
        }
        /* если инвайт пришел от юзера TYPE_PAYED_BUSINESS_USER к юзеру TYPE_FREE_DEFAULT то запретить */
        if ($this->UserOwner->license_type == Licenses::TYPE_PAYED_BUSINESS_USER && $User_for_Colleague->license_type == Licenses::TYPE_FREE_DEFAULT) {
            $transaction->rollBack();
            return [
                'status' => false,
                'info'   => Yii::t('app/flash-messages', "license_restriction_free_try_join_from_businessUser"),
            ];
        }
        /* если инвайт пришел от TYPE_PAYED_BUSINESS_ADMIN к юзерам TYPE_FREE_DEFAULT или TYPE_FREE_TRIAL то они получают лицензию TYPE_PAYED_BUSINESS_USER */
        /* с TYPE_PAYED_BUSINESS_ADMIN она была списана (должна была быть списана) в момент отправки инвайта */
        if ($this->UserOwner->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN && in_array($User_for_Colleague->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])) {
            //$User_for_Colleague->license_expire = $UserLicense->lic_end;
            $User_for_Colleague->license_type = Licenses::TYPE_PAYED_BUSINESS_USER;
            $User_for_Colleague->license_business_from = $this->UserOwner->user_id;
            $User_for_Colleague->upl_limit_nodes = $this->UserOwner->upl_limit_nodes;
            $User_for_Colleague->upl_shares_count_in24 = $this->UserOwner->upl_shares_count_in24;
            $User_for_Colleague->upl_max_shares_size = $this->UserOwner->upl_max_shares_size;
            $User_for_Colleague->upl_max_count_children_on_copy = $this->UserOwner->upl_max_count_children_on_copy;
            $User_for_Colleague->upl_block_server_nodes_above_bought = $this->UserOwner->upl_block_server_nodes_above_bought;
            $User_for_Colleague->save();

            if (isset($user_was_non_registered)) {
                $UserLicense = UserLicenses::getFreeLicenseForNonRegistered($this->UserOwner->user_id, $User_for_Colleague->user_email);
                if ($UserLicense) {

                    $UserLicense->lic_colleague_user_id = $User_for_Colleague->user_id;
                    $UserLicense->lic_colleague_email = $User_for_Colleague->user_email;
                    $UserLicense->save();



                    if (strtotime($UserLicense->lic_end) < time()) {
                        return [
                            'status' => false,
                            'info'   => Yii::t('app/flash-messages', "license_restriction_free_try_join_from_business_but_license_is_expire"),
                        ];
                    }
                } else {
                    return [
                        'status' => false,
                        'info'   =>  Yii::t('app/flash-messages', "license_restriction_free_try_join_from_business_but_business_has_not_available_licenses"),
                    ];
                }

            }
        }
        /*  --- проверки возможности коллаборации между юзерами */


        /* Если успели удалить коллегу (в очереди на удаление) из коллаборации до того как он заджойнился */
        if ($UserColleague->colleague_status == UserColleagues::STATUS_QUEUED_DEL) {
            $transaction->rollBack();
            return [
                'status' => false,
                'info' => "Yor are excluded from this collaboration.",
            ];
        }

        /* Если коллега уже в очереди на добавление в коллаборацию и повторно жмет на джойн */
        if ($UserColleague->colleague_status == UserColleagues::STATUS_QUEUED_ADD) {
            $transaction->rollBack();
            return [
                'status' => false,
                'hidden_info' => true,
                'info' => "Yor are already in queue for add to this collaboration.",
            ];
        }

        /* Если коллега уже заджойнился ранее и пробует повторно */
        if ($UserColleague->colleague_status == UserColleagues::STATUS_JOINED) {
            $transaction->rollBack();
            return [
                'status' => false,
                'info' => "You are already joined to this collaboration.",
            ];
        }

        /* Обновляем статус коллеги на джойнед */
        $UserColleague->colleague_invite_date = date(SQL_DATE_FORMAT);
        $UserColleague->colleague_status = UserColleagues::STATUS_QUEUED_ADD;
        if ($UserColleague->save()) {

            /* ставим отметку что файл учавствует в коллаборации */
            $this->CollaboratedFolder->is_collaborated = UserFiles::FILE_COLLABORATED;
            $this->CollaboratedFolder->collaboration_id = $this->UserCollaboration->collaboration_id;
            $this->CollaboratedFolder->is_owner = UserFiles::IS_OWNER;

            if ($this->CollaboratedFolder->save()) {

                /* Заканчиваем транзакцию успешно и отправляем данные о новом колеге */
                $transaction->commit();

                /* через очередь или напрямую */
                if ($this->queue) {
                    /* выполняем остальную часть через очередь */
                    $event_uuid_from_node = md5($this->UserCollaboration->collaboration_id . microtime());
                    $job_id = $this->queue->push(new ColleagueAddJob([
                        'User_for_Colleague_user_id'         => $User_for_Colleague->user_id,
                        'CollaboratedFolder_file_id'         => $this->CollaboratedFolder->file_id,
                        'UserCollaboration_collaboration_id' => $this->UserCollaboration->collaboration_id,
                        'UserCollaboration_file_uuid'        => $this->UserCollaboration->file_uuid,
                        'UserCollaboration_user_id'          => $this->UserCollaboration->user_id,

                        'UserOwner_user_id'                  => $this->UserOwner->user_id,
                        'UserOwner_user_email'               => $this->UserOwner->user_email,
                        'UserOwner__full_path'               => $this->UserOwner->_full_path,

                        'UserColleague_colleague_email'      => $UserColleague->colleague_email,
                        'UserColleague_user_id'              => $UserColleague->user_id,
                        'UserColleague_colleague_id'         => $UserColleague->colleague_id,

                        'join_or_include'                    => self::IS_JOIN,
                        'event_uuid_from_node'               => $event_uuid_from_node,
                    ]));

                    /* Эта хрень нужна что бы запустить выполенение задание в псевдоочереди, при Unit-тестировании */
                    if (isset(Yii::$app->components['queue']['class'], Yii::$app->params['UnitTests']) &&
                        Yii::$app->components['queue']['class'] == 'yii\queue\sync\Queue') {
                        $this->queue->run(true);
                    }

                    $QueuedEvent = new QueuedEvents();
                    $QueuedEvent->event_uuid = $event_uuid_from_node;
                    $QueuedEvent->job_id     = (string) $job_id;
                    $QueuedEvent->user_id    = $this->UserOwner->user_id;
                    $QueuedEvent->node_id    = null;
                    $QueuedEvent->job_status = QueuedEvents::STATUS_WAITING;
                    $QueuedEvent->job_type   = QueuedEvents::TYPE_COLLEAGUE_ADD;
                    $QueuedEvent->queue_id   = 'queue';
                    $QueuedEvent->save();

                    $queue_status = 'queued';
                } else {
                    /* выполняем напрямую */
                    $job_id = null;
                    $queue_status = 'direct';
                    $ret = self::colleagueAdd_exec(
                        $this->redis,
                        $User_for_Colleague,
                        $this->CollaboratedFolder,
                        $this->UserCollaboration->collaboration_id,
                        $this->UserCollaboration->file_uuid,
                        $this->UserCollaboration->user_id,
                        $this->UserOwner->user_id,
                        $this->UserOwner->user_email,
                        $this->UserOwner->_full_path,
                        $UserColleague->colleague_email,
                        $UserColleague->user_id,
                        $UserColleague->colleague_id,
                        self::IS_JOIN,
                        ''
                    );
                }

                /* Ответ */
                $data_send = UserColleagues::prepareColleagueData($UserColleague);
                $data_send['collaboration_id'] = $UserColleague->collaboration_id;
                $data_send['file_uuid'] = $this->CollaboratedFolder->file_uuid;
                $data_send['file_name'] = $this->CollaboratedFolder->file_name;

                if (isset($license_restriction)) {
                    $data_send['license_restriction'] = $license_restriction;
                    $data_send['license_restriction_type'] = isset($license_restriction_type)
                        ? $license_restriction_type
                        : 'error';
                }

                return [
                    'status' => true,
                    'action' => $this->action,
                    'data' => $data_send,
                    //'event_data' => $event_data,
                ];
            } else {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info' => 'Cant save UserFIle info for collaboration',
                    'debug' => $this->CollaboratedFolder->getErrors(),
                ];
            }
        } else {
            $transaction->rollBack();
            return [
                'status' => false,
                'info' => "Database error: Can't add new colleague to collaboration.",
                'debug' => $UserColleague->getErrors(),
            ];
        }
    }

    /**
     * Добавление коллеги в коллаборацию
     * @return array
     */
    public function colleagueAdd()
    {
        /* colleague_email - required */

        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();

        /* Находим нужную нам коллабоарацию или создаем ее если ее нет и это новая коллаборация */
        $initStatus = $this->collaborationInit();
        if (is_array($initStatus)) {
            return $initStatus;
        }

        /* Проверим есть ли уже такой коллега в списке */
        $isColleagueExists = UserColleagues::findOne([
            'colleague_email'  => $this->colleague_email,
            'collaboration_id' => $this->UserCollaboration->collaboration_id,
        ]);
        if ($isColleagueExists) {
            $transaction->rollBack();
            if ($isColleagueExists->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                return [
                    'status' => false,
                    'info'   => "You try add self into collaboration.",
                ];
            } elseif ($isColleagueExists->colleague_status == UserColleagues::STATUS_QUEUED_ADD) {
                return [
                    'status' => false,
                    'info'   => "Colleague already set in queue fro add on this collaboration.",
                    'hidden_info' => true,
                ];
            } elseif ($isColleagueExists->colleague_status == UserColleagues::STATUS_QUEUED_DEL) {
                return [
                    'status' => false,
                    'info'   => "Colleague set in queue for delete from this collaboration. Wait while it will be deleted",
                    'hidden_info' => true,
                ];
            } else {
                return [
                    'status' => false,
                    'info'   => "Colleague already exist in this collaboration.",
                ];
            }
        }

        /* Пытаемся найти user_id (Users) по colleague_email */
        $User_for_Colleague = Users::findByEmail($this->colleague_email);
        if (!$User_for_Colleague) {
            $transaction->rollBack();
            return [
                'status' => false,
                'info'   => "Can't find user_id (Users) for colleague_email={$this->colleague_email}",
                'debug'  => $this->CollaboratedFolder->getErrors(),
            ];
        }


        /*  +++ проверки возможности коллаборации между юзерами */
        /* Если TYPE_FREE_DEFAULT пытается добавить ююзера любого типа лицензии минуя инвайт и джойн то запретить */
        if ($this->UserOwner->license_type == Licenses::TYPE_FREE_DEFAULT) {
            $transaction->rollBack();
            return [
                'status' => false,
                'info'   => Yii::t('app/flash-messages', "license_restriction_free_add_any"),
            ];
        }
        /* если TYPE_PAYED_PROFESSIONAL пытается добавить юзера с лицензией TYPE_FREE_DEFAULT минуя инвайт то запретить */
        if ($this->UserOwner->license_type == Licenses::TYPE_PAYED_PROFESSIONAL) {
            if ($User_for_Colleague->license_type == Licenses::TYPE_FREE_DEFAULT) {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info'   => Yii::t('app/flash-messages', "license_restriction_pro_add_free"),
                ];
            }
        }
        /*  --- проверки возможности коллаборации между юзерами */


        /* создаем запись о новом коллеге в бд */
        $UserColleague = new UserColleagues();
        $UserColleague->collaboration_id = $this->UserCollaboration->collaboration_id;
        $UserColleague->user_id = $User_for_Colleague->user_id;
        $UserColleague->colleague_status = UserColleagues::STATUS_QUEUED_ADD;
        $UserColleague->colleague_joined_date = date(SQL_DATE_FORMAT);
        $UserColleague->colleague_permission = $this->access_type;
        $UserColleague->colleague_email = $this->colleague_email;
        if ($UserColleague->save()) {

            /* ставим отметку что файл учавствует в коллаборации */
            $this->CollaboratedFolder->is_collaborated  = UserFiles::FILE_COLLABORATED;
            $this->CollaboratedFolder->collaboration_id = $this->UserCollaboration->collaboration_id;
            $this->CollaboratedFolder->is_owner         = UserFiles::IS_OWNER;

            /* Сохраняем */
            if ($this->CollaboratedFolder->save()) {

                /* Заканчиваем транзакцию успешно и отправляем данные о новом колеге */
                $transaction->commit();

                /* через очередь или напрямую */
                if ($this->queue) {
                    /* выполняем остальную часть через очередь */
                    $event_uuid_from_node = md5($this->UserCollaboration->collaboration_id . microtime());
                    $job_id = $this->queue->push(new ColleagueAddJob([
                        'User_for_Colleague_user_id'         => $User_for_Colleague->user_id,
                        'CollaboratedFolder_file_id'         => $this->CollaboratedFolder->file_id,
                        'UserCollaboration_collaboration_id' => $this->UserCollaboration->collaboration_id,
                        'UserCollaboration_file_uuid'        => $this->UserCollaboration->file_uuid,
                        'UserCollaboration_user_id'          => $this->UserCollaboration->user_id,

                        'UserOwner_user_id'                  => $this->UserOwner->user_id,
                        'UserOwner_user_email'               => $this->UserOwner->user_email,
                        'UserOwner__full_path'               => $this->UserOwner->_full_path,

                        'UserColleague_colleague_email'      => $UserColleague->colleague_email,
                        'UserColleague_user_id'              => $UserColleague->user_id,
                        'UserColleague_colleague_id'         => $UserColleague->colleague_id,

                        'join_or_include'                    => self::IS_INCLUDE,
                        'event_uuid_from_node'               => $event_uuid_from_node,
                    ]));

                    /* Эта хрень нужна что бы запустить выполенение задание в псевдоочереди, при Unit-тестировании */
                    if (isset(Yii::$app->components['queue']['class'], Yii::$app->params['UnitTests']) &&
                        Yii::$app->components['queue']['class'] == 'yii\queue\sync\Queue') {
                        $this->queue->run(true);
                    }

                    $QueuedEvent = new QueuedEvents();
                    $QueuedEvent->event_uuid = $event_uuid_from_node;
                    $QueuedEvent->job_id     = (string) $job_id;
                    $QueuedEvent->user_id    = $this->UserOwner->user_id;
                    $QueuedEvent->node_id    = null;
                    $QueuedEvent->job_status = QueuedEvents::STATUS_WAITING;
                    $QueuedEvent->job_type   = QueuedEvents::TYPE_COLLEAGUE_ADD;
                    $QueuedEvent->queue_id   = 'queue';
                    $QueuedEvent->save();

                    $queue_status = 'queued';
                } else {
                    /* выполняем напрямую */
                    $job_id = null;
                    $queue_status = 'direct';
                    $ret = self::colleagueAdd_exec(
                        $this->redis,
                        $User_for_Colleague,
                        $this->CollaboratedFolder,
                        $this->UserCollaboration->collaboration_id,
                        $this->UserCollaboration->file_uuid,
                        $this->UserCollaboration->user_id,
                        $this->UserOwner->user_id,
                        $this->UserOwner->user_email,
                        $this->UserOwner->_full_path,
                        $UserColleague->colleague_email,
                        $UserColleague->user_id,
                        $UserColleague->colleague_id,
                        self::IS_INCLUDE,
                        ''
                    );
                }

                /* Создаем нотификайшн о ИНКЛУДЕ (include) ++создаем письмо */
                MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_CollaborationInclude, $UserColleague->colleague_email, [
                    'UserColleagueObject'      => $UserColleague,
                    'UserObject'               => $User_for_Colleague ? $User_for_Colleague : null,
                    'UserOwner_email'          => $this->UserOwner->user_email,
                    'UserOwner_name'           => $this->UserOwner->user_name,
                    'invite_colleague_message' => trim($this->colleague_message),
                ]);
                if ($User_for_Colleague) {
                    /* создается в ехес функции
                    $notif = new Notifications();
                    $notif->user_id = $User_for_Colleague->user_id;
                    $notif->notif_isnew = Notifications::IS_NEW;
                    $notif->notif_type = Notifications::TYPE_COLLABORATION_INCLUDE;
                    $notif->notif_data = serialize([
                        'search' => [
                            '{folder_name}',
                            '{user_email}',
                            '{access_type}',
                            '{colleague_id}',
                            //'{include_link}',
                        ],
                        'replace' => [
                            $this->CollaboratedFolder->file_name,
                            $this->UserOwner->user_email,
                            $this->access_type,
                            $UserColleague->colleague_id,
                            //Yii::$app->urlManager->createAbsoluteUrl(['user/files']),
                        ],
                        'links_data' => [
                            'include_link' => ['user/files'],
                        ],
                    ]);
                    $notif->save();
                    */
                }

                /* Ответ */
                $data_send = UserColleagues::prepareColleagueData($UserColleague);
                $data_send['collaboration_id'] = $UserColleague->collaboration_id;
                $data_send['file_uuid'] = $this->CollaboratedFolder->file_uuid;
                $data_send['file_name'] = $this->CollaboratedFolder->file_name;
                $data_send['queue_status'] = $queue_status;
                $data_send['job_id'] = $job_id;
                return [
                    'status' => true,
                    'action' => $this->action,
                    'data'   => $data_send,
                    //'event_data' => $event_data,
                ];

            } else {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info'   => 'Cant save UserFIle info for collaboration',
                    'debug'  => $this->CollaboratedFolder->getErrors(),
                ];
            }
        } else {
            $transaction->rollBack();
            return [
                'status' => false,
                'info'   => "Database error: Can't add new colleague to collaboration.",
                'debug'  => $UserColleague->getErrors(),
            ];
        }
    }

    /**
     * Функция для постановки в очередь операции на добавление коллеги
     * (может быть выполнена и без очереди в зависимости от настроек)
     * @param \yii\redis\Connection $redis
     * @param \common\models\Users $User_for_Colleague
     * @param \common\models\UserFiles $CollaboratedFolder
     * @param integer $UserCollaboration_collaboration_id
     * @param string $UserCollaboration_file_uuid
     * @param integer $UserCollaboration_user_id
     * @param integer $UserOwner_user_id
     * @param string $UserOwner_user_email
     * @param string $UserOwner__full_path
     * @param string $UserColleague_colleague_email
     * @param integer $UserColleague_user_id
     * @param integer $UserColleague_colleague_id
     * @param string $join_or_include
     * @param string $event_uuid_from_node
     *
     * @return bool
     */
    public static function colleagueAdd_exec(
        &$redis,
        &$User_for_Colleague,
        &$CollaboratedFolder,

        $UserCollaboration_collaboration_id,
        $UserCollaboration_file_uuid,
        $UserCollaboration_user_id,

        $UserOwner_user_id,
        $UserOwner_user_email,
        $UserOwner__full_path,

        $UserColleague_colleague_email,
        $UserColleague_user_id,
        $UserColleague_colleague_id,

        $join_or_include,

        $event_uuid_from_node)
    {
        /** переменные которые нужно передать в джоб
        // это объекты которые придется в классе джоба искать по их ИД
        $User_for_Colleague;
        $this->CollaboratedFolder;

        // это простые переменные которые передаются стандартно (не будем передавать весь объект для уменьшения запросво в классе джоба)
        $this->UserCollaboration->collaboration_id;*
        $this->UserCollaboration->file_uuid;*
        $this->UserCollaboration->user_id;*

        $this->UserOwner->user_email;*
        $this->UserOwner->_full_path;*

        $UserColleague->colleague_email;*
        $UserColleague->user_id;*
        $UserColleague->colleague_id*
        * -- переменные которые нужно передать в джоб */


        /* До транзакции сделаем лок коллаборации если работа идет через очередь а если лок не удался
         * ставим задание в очередь снова через минуту
         * а текущее задание считаем отработанным */

        /** @var \yii\queue\file\Queue $queue */
        $queue = (isset(Yii::$app->queue) && method_exists(Yii::$app->queue, 'push')) ? Yii::$app->queue : false;

        /** @var \yii\mutex\FileMutex $mutex */
        $mutex = Yii::$app->mutex;
        $mutex_collaboration_name = 'collaboration_id_' . $UserCollaboration_collaboration_id;
        $mutex_collaboration_name_by_owner_user_id = 'collaboration_by_user_id' . $UserOwner_user_id;
        $mutex_collaboration_name_by_colleague_user_id = 'collaboration_by_user_id' . $UserColleague_user_id;

        if ($queue) {
            if (!$mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT)) {

                echo "collaboration_id = " . $UserCollaboration_collaboration_id . " is locked. Retry this job in 1 minutes.\n";
                $job_id = $queue->delay(60)->push(new ColleagueAddJob([

                    'UserCollaboration_collaboration_id' => $UserCollaboration_collaboration_id,
                    'UserCollaboration_file_uuid' => $UserCollaboration_file_uuid,
                    'User_for_Colleague_user_id' => $User_for_Colleague->user_id,

                    'UserOwner_user_id' => $UserOwner_user_id,
                    'UserOwner_user_email' => $UserOwner_user_email,
                    'UserOwner__full_path' => $UserOwner__full_path,

                    'UserColleague_colleague_email' => $UserColleague_colleague_email,
                    'UserColleague_user_id' => $UserColleague_user_id,
                    'UserColleague_colleague_id' => $UserColleague_colleague_id,

                    'CollaboratedFolder_file_id' => $CollaboratedFolder->file_id,
                    'UserCollaboration_user_id' => $UserCollaboration_user_id,

                    'join_or_include' => $join_or_include,
                    'event_uuid_from_node' => $event_uuid_from_node,
                ]));

                $QueuedEvent = new QueuedEvents();
                $QueuedEvent->event_uuid = $event_uuid_from_node;
                $QueuedEvent->job_id     = (string) $job_id;
                $QueuedEvent->user_id    = $UserOwner_user_id;
                $QueuedEvent->node_id    = null;
                $QueuedEvent->job_status = QueuedEvents::STATUS_WAITING;
                $QueuedEvent->job_type   = QueuedEvents::TYPE_COLLEAGUE_ADD;
                $QueuedEvent->queue_id   = 'queue';
                $QueuedEvent->save();

                return true;
            }

            if (!$mutex->acquire($mutex_collaboration_name_by_owner_user_id, MUTEX_WAIT_TIMEOUT)) {

                echo "collaboration_id = " . $UserCollaboration_collaboration_id . " is locked. Retry this job in 1 minutes.\n";
                $job_id = $queue->delay(60)->push(new ColleagueAddJob([

                    'UserCollaboration_collaboration_id' => $UserCollaboration_collaboration_id,
                    'UserCollaboration_file_uuid' => $UserCollaboration_file_uuid,
                    'User_for_Colleague_user_id' => $User_for_Colleague->user_id,

                    'UserOwner_user_id' => $UserOwner_user_id,
                    'UserOwner_user_email' => $UserOwner_user_email,
                    'UserOwner__full_path' => $UserOwner__full_path,

                    'UserColleague_colleague_email' => $UserColleague_colleague_email,
                    'UserColleague_user_id' => $UserColleague_user_id,
                    'UserColleague_colleague_id' => $UserColleague_colleague_id,

                    'CollaboratedFolder_file_id' => $CollaboratedFolder->file_id,
                    'UserCollaboration_user_id' => $UserCollaboration_user_id,

                    'join_or_include' => $join_or_include,
                    'event_uuid_from_node' => $event_uuid_from_node,
                ]));

                $QueuedEvent = new QueuedEvents();
                $QueuedEvent->event_uuid = $event_uuid_from_node;
                $QueuedEvent->job_id     = (string) $job_id;
                $QueuedEvent->user_id    = $UserOwner_user_id;
                $QueuedEvent->node_id    = null;
                $QueuedEvent->job_status = QueuedEvents::STATUS_WAITING;
                $QueuedEvent->job_type   = QueuedEvents::TYPE_COLLEAGUE_ADD;
                $QueuedEvent->queue_id   = 'queue';
                $QueuedEvent->save();

                return true;
            }

            if (!$mutex->acquire($mutex_collaboration_name_by_colleague_user_id, MUTEX_WAIT_TIMEOUT)) {

                echo "collaboration_id = " . $UserCollaboration_collaboration_id . " is locked. Retry this job in 1 minutes.\n";
                $job_id = $queue->delay(60)->push(new ColleagueAddJob([

                    'UserCollaboration_collaboration_id' => $UserCollaboration_collaboration_id,
                    'UserCollaboration_file_uuid' => $UserCollaboration_file_uuid,
                    'User_for_Colleague_user_id' => $User_for_Colleague->user_id,

                    'UserOwner_user_id' => $UserOwner_user_id,
                    'UserOwner_user_email' => $UserOwner_user_email,
                    'UserOwner__full_path' => $UserOwner__full_path,

                    'UserColleague_colleague_email' => $UserColleague_colleague_email,
                    'UserColleague_user_id' => $UserColleague_user_id,
                    'UserColleague_colleague_id' => $UserColleague_colleague_id,

                    'CollaboratedFolder_file_id' => $CollaboratedFolder->file_id,
                    'UserCollaboration_user_id' => $UserCollaboration_user_id,

                    'join_or_include' => $join_or_include,
                    'event_uuid_from_node' => $event_uuid_from_node,
                ]));

                $QueuedEvent = new QueuedEvents();
                $QueuedEvent->event_uuid = $event_uuid_from_node;
                $QueuedEvent->job_id     = (string) $job_id;
                $QueuedEvent->user_id    = $UserOwner_user_id;
                $QueuedEvent->node_id    = null;
                $QueuedEvent->job_status = QueuedEvents::STATUS_WAITING;
                $QueuedEvent->job_type   = QueuedEvents::TYPE_COLLEAGUE_ADD;
                $QueuedEvent->queue_id   = 'queue';
                $QueuedEvent->save();

                return true;
            }
        }

        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();

        /* Получаем ФМ ноду юзера */
        $ColleagueUserNode = NodeApi::registerNodeFM($User_for_Colleague);

        /* записываем информацию о папке-файле коллаборации в пустышку-инфо */
        $relativePath = UserFiles::getFullPath($CollaboratedFolder);
        $file_name = $UserOwner__full_path . DIRECTORY_SEPARATOR . $relativePath;
        if ($CollaboratedFolder->is_folder) {
            $info_file = $file_name . DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
        } else {
            $info_file = $file_name;
        }
        FileSys::touch($info_file, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
        UserFiles::createFileInfo($info_file, $CollaboratedFolder);

        /* тут рекурсивная ф-ия для установки collaboration_id для всех дочерних элементов */
        /* а так же для создания копию папки коллаборации для нового коллеги и рекурсивного создания в ней всех файлов */
        $event_data = [];

        /* Попробуем найти и удалить такую же папку коллаборации, если пользователь был ранее приглашен а затем удален из нее */
        $query = "DELETE FROM {{%user_files}}
                  WHERE (file_uuid = :file_uuid)
                  AND (user_id = :colleague_user_id)
                  AND (is_deleted = :FILE_DELETED)";
        $res = Yii::$app->db->createCommand($query, [
            'file_uuid'         => $CollaboratedFolder->file_uuid,
            'colleague_user_id' => $User_for_Colleague->user_id,
            'FILE_DELETED'      => UserFiles::FILE_DELETED,
        ])->query()->getRowCount();

        /* Создание копии папки и всех ее чилдренов для коллеги */
        $res_cc = UserFiles::changeCollaboration(
            $CollaboratedFolder,
            $UserOwner__full_path,
            $UserCollaboration_collaboration_id,
            $ColleagueUserNode,
            $event_data,
            $redis
        );
        $event_group_id   = isset($res_cc['event_group_id'])   ? $res_cc['event_group_id']   : null;
        $root_folder_name = isset($res_cc['root_folder_name']) ? $res_cc['root_folder_name'] : null;;

        /*
         * если что то пошло не так в функции UserFiles::changeCollaboration и она не вернула ид группы евентов
         * тогда откат транзакции и фалс
         */
        if (!$event_group_id) {
            $transaction->rollBack();
            return false;
        }

        /* Обновим статус коллеги с queued на joined */
        UserColleagues::updateAll([
            'colleague_status' => UserColleagues::STATUS_JOINED,
            'colleague_joined_date' => date(SQL_DATE_FORMAT),
        ], [
            'colleague_id' => $UserColleague_colleague_id,
        ]);

        /* Чистка нулл-коллабораций у коллеги */
        self::clear_null_collaboration($UserCollaboration_user_id, $UserColleague_colleague_email);

        /* Заканчиваем транзакцию успешно и отправляем данные о новом колеге */
        $transaction->commit();

        /* Создадим нотфикайшен о том что джойн или инклуд прошел успешно */
        $UserColleague = UserColleagues::findOne(['colleague_id' => $UserColleague_colleague_id]);
        if ($UserColleague) {

            /* нотиф об успешном инклуде или джойне коллеги для АДМИНА */
            $notif = new Notifications();
            $notif->user_id = $UserOwner_user_id;
            $notif->notif_isnew = Notifications::IS_NEW;
            $notif->notif_type = Notifications::TYPE_COLLABORATION_ABOUT_JOIN_FOR_ADMIN;
            $notif->notif_data = serialize([
                'search' => [
                    '{folder_name}',
                    '{user_email}',
                    '{access_type}',
                    '{colleague_id}',
                    '{file_uuid}',
                    //'{include_link}',
                ],
                'replace' => [
                    $CollaboratedFolder->file_name,
                    $UserColleague->colleague_email,
                    $UserColleague->colleague_permission,
                    $UserColleague->user_id,
                    $CollaboratedFolder->file_uuid,
                    //Yii::$app->urlManager->createAbsoluteUrl(['user/files']),
                ],
                'links_data' => [
                    'include_link' => ['user/files'],
                ],
            ]);
            $notif->save();


            if ($join_or_include == self::IS_INCLUDE) {

                /* нотиф об успешном инклуде для коллеги */
                $notif = new Notifications();
                $notif->user_id = $User_for_Colleague->user_id;
                $notif->notif_isnew = Notifications::IS_NEW;
                $notif->notif_type = Notifications::TYPE_COLLABORATION_INCLUDE;
                $notif->notif_data = serialize([
                    'search' => [
                        '{folder_name}',
                        '{user_email}',
                        '{access_type}',
                        '{colleague_id}',
                        '{file_uuid}',
                        //'{include_link}',
                    ],
                    'replace' => [
                        (isset($root_folder_name)) ? $root_folder_name : $CollaboratedFolder->file_name,
                        $UserOwner_user_email,
                        $UserColleague->colleague_permission,
                        $UserColleague_user_id,
                        $CollaboratedFolder->file_uuid,
                        //Yii::$app->urlManager->createAbsoluteUrl(['user/files']),
                    ],
                    'links_data' => [
                        'include_link' => ['user/files'],
                    ],
                ]);
                $notif->save();

            } else {

                /* ноитф об успешном джойне для коллеги */
                $notif = new Notifications();
                $notif->user_id = $UserColleague_user_id;
                $notif->notif_isnew = Notifications::IS_NEW;
                $notif->notif_type = Notifications::TYPE_COLLABORATION_JOIN;
                $notif->notif_data = serialize([
                    'search' => [
                        '{folder_name}',
                        '{user_email}',
                        '{access_type}',
                        '{colleague_id}',
                        '{file_uuid}',
                        //'{joined_link}',
                    ],
                    'replace' => [
                        (isset($root_folder_name)) ? $root_folder_name : $CollaboratedFolder->file_name,
                        $UserOwner_user_email,
                        $UserColleague->colleague_permission,
                        $UserColleague_user_id,
                        $CollaboratedFolder->file_uuid,
                        //Yii::$app->urlManager->createAbsoluteUrl(['user/files']),
                    ],
                    'links_data' => [
                        'joined_link' => ['user/files'],
                    ],
                ]);
                $notif->save();
            }
        }

        /*
         * Если UserFiles::changeCollaboration вернула ид группы евентов тогда
         * создадим для них папки и файлы в фс ФМа а так же отправим данные на редис
         */
        $User = Users::getPathNodeFS($ColleagueUserNode->user_id);
        $min_file_id = 0;
        do {
            $query = "SELECT DISTINCT ON (e1.file_id)
                                          e1.event_id,
                                          e1.event_uuid,
                                          e1.last_event_id,
                                          e1.event_type,
                                          e1.event_timestamp,
                                          (CASE WHEN (e1.file_hash IS NOT NULL) THEN e1.file_hash ELSE f1.file_md5 END) as file_hash,
                                          e1.file_hash_before_event,
                                          e1.diff_file_uuid,
                                          e1.diff_file_size,
                                          e1.rev_diff_file_uuid,
                                          e1.rev_diff_file_size,
                                          f1.is_folder,
                                          f1.file_uuid,

                                          f1.is_owner,
                                          f1.is_updated,
                                          f1.is_shared,
                                          f1.is_deleted,
                                          f1.is_collaborated,
                                          f1.folder_children_count,
                                          f1.collaboration_id,
                                          f1.share_hash,
                                          f1.share_lifetime,
                                          f1.share_ttl_info,
                                          f1.share_password,
                                          f1.file_lastatime,
                                          f1.file_lastmtime,

                                          e1.file_id,
                                          e1.parent_after_event as file_parent_id,
                                          e1.file_name_after_event as file_name,
                                          e1.file_size_after_event as file_size,
                                          e1.erase_nested,
                                          coalesce(f2.file_uuid, null) as parent_folder_uuid,
                                          get_full_path(e1.file_id, :DIRECTORY_SEPARATOR) as file_path
                                      FROM dl_user_file_events as e1
                                      INNER JOIN dl_user_files as f1 ON f1.file_id = e1.file_id
                                      LEFT JOIN dl_user_files as f2 ON e1.parent_after_event = f2.file_id
                                      WHERE e1.event_group_id = :event_group_id
                                      AND e1.file_id > :min_file_id
                                      ORDER BY e1.file_id ASC
                                      LIMIT 100";
            $res4 = Yii::$app->db->createCommand($query, [
                'event_group_id'      => $event_group_id,
                'min_file_id'         => $min_file_id,
                'DIRECTORY_SEPARATOR' => DIRECTORY_SEPARATOR,
            ])->queryAll();

            $event_data = [];
            if (sizeof($res4)) {
                foreach ($res4 as $data) {
                    $min_file_id = $data['file_id'];


                    /* создаем файлы и папки */
                    $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $data['file_path'];
                    if ($data['is_folder']) {
                        $file_name .= DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
                    }
                    //var_dump($file_name); exit;
                    $dir_name = dirname($file_name);
                    if (!file_exists($dir_name)) {
                        FileSys::mkdir($dir_name, UserFiles::CHMOD_DIR, true);
                    }
                    UserFiles::createFileInfoRaw($file_name, $data);


                    /* Собираем набор евентов */
                    $event_data[] = [
                        'operation' => "file_event",
                        'data' => [
                            'event_id'               => $data['event_id'],
                            'event_uuid'             => $data['event_uuid'],
                            'erase_nested'           => ($data['erase_nested'] == UserFileEvents::ERASE_NESTED_TRUE),
                            'last_event_id'          => $data['last_event_id'],
                            'event_type'             => UserFileEvents::getType($data['event_type']),
                            'event_type_int'         => $data['event_type'],
                            'timestamp'              => $data['event_timestamp'],
                            'hash'                   => $data['file_hash'],
                            'file_hash_before_event' => $data['file_hash_before_event'],
                            'file_hash_after_event'  => $data['file_hash'],
                            'file_hash'              => $data['file_hash'],
                            'diff_file_uuid'         => $data['diff_file_uuid'],
                            'diff_file_size'         => $data['diff_file_size'],
                            'rev_diff_file_uuid'     => $data['rev_diff_file_uuid'],
                            'rev_diff_file_size'     => $data['rev_diff_file_size'],
                            'is_folder'              => ($data['is_folder'] == UserFiles::TYPE_FOLDER),
                            'uuid'                   => $data['file_uuid'],
                            'file_id'                => $data['file_id'],
                            'file_parent_id'         => $data['file_parent_id'],
                            'file_name'              => $data['file_name'],
                            'file_size'              => $data['file_size'],
                            'user_id'                => $ColleagueUserNode->user_id,
                            'node_id'                => $ColleagueUserNode->node_id,
                            'parent_folder_uuid'     => $data['parent_folder_uuid'],
                        ],
                    ];
                }

                /* Отправка пачки евентов на редис (того кто создал данное событие) */
                if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
                    try {
                        $redis->publish("user:{$ColleagueUserNode->user_id}:fs_events", Json::encode($event_data));
                        $redis->save();
                        unset($event_data);
                        $event_data = [];
                    } catch (\Exception $e) {
                        RedisSafe::createNewRecord(
                            RedisSafe::TYPE_FS_EVENTS,
                            $ColleagueUserNode->user_id,
                            $ColleagueUserNode->node_id,
                            Json::encode([
                                'action' => 'fs_events',
                                'chanel' => "user:{$ColleagueUserNode->user_id}:fs_events",
                                'user_id' => $ColleagueUserNode->user_id,
                                'noe_id' => $ColleagueUserNode->node_id,
                            ])
                        );
                    }
                }

            }


        } while (sizeof($res4) > 0);

        /* Отправляем в редис данные по коллаборации*/
        try {
            $redis->publish("collaboration:{$UserCollaboration_collaboration_id}:useradd", $UserColleague_user_id);
            $redis->sadd("collaboration:{$UserColleague_user_id}:folders", $UserCollaboration_file_uuid);
            $redis->sadd("collaboration:{$UserCollaboration_collaboration_id}:users", $UserColleague_user_id);
            $redis->sadd("user:{$UserColleague_user_id}:collaborations", $UserCollaboration_collaboration_id);
            $redis->save();
        } catch (\Exception $e) {
            if ($UserColleague_user_id) {
                RedisSafe::createNewRecord(
                    RedisSafe::TYPE_COLLABORATION_CHANGES,
                    $UserColleague_user_id,
                    null,
                    Json::encode([
                        'collaboration_id' => $UserCollaboration_collaboration_id,
                        'action' => 'useradd',
                        'chanel' => "collaboration:{$UserCollaboration_collaboration_id}:useradd",
                        'user_id' => $UserColleague_user_id,
                    ])
                );
            }
        }

        /* Освобождаем от блокировки по мутексу */
        $mutex->release($mutex_collaboration_name);
        $mutex->release($mutex_collaboration_name_by_owner_user_id);
        $mutex->release($mutex_collaboration_name_by_colleague_user_id);

        return true;
    }

    /**
     * Удаление коллеги из коллаборации
     * @param bool $needTransaction
     * @return array
     * @throws \yii\db\Exception
     */
    public function colleagueDelete($needTransaction=true)
    {
        /* colleague_id | colleague_user_id is required */

        /* Начинаем транзакцию */
        if ($needTransaction) { $transaction = Yii::$app->db->beginTransaction(); }

        /* Находим нужную нам коллабоарацию или создаем ее если ее нет и это новая коллаборация */
        $initStatus = $this->collaborationInit();
        if (is_array($initStatus)) {
            return $initStatus;
        }

        /* Если не передан colleague_id но передан colleague_user_id то пытаемся найти нужный colleague_id*/
        if (!$this->colleague_id && $this->colleague_user_id) {
            if (!$this->initColleagueIDByUserID()) {
                if (isset($transaction)) { $transaction->rollBack(); }
                return [
                    'status' => false,
                    'info'   => 'colleague_id not found for this colleague_user_id.',
                ];
            }
        }

        /* Если нет colleague_id то дальше не возмжна работа */
        if (!$this->colleague_id) {
            if (isset($transaction)) { $transaction->rollBack(); }
            return [
                'status' => false,
                'info'   => "The colleague_id isn't given.",
            ];
        }

        /* Если не нашли коллегу с таим ИД - ошибка */
        $UserColleague = UserColleagues::findOne(['colleague_id' => $this->colleague_id]);
        if (!$UserColleague) {
            if (isset($transaction)) { $transaction->rollBack(); }
            return [
                'status' => false,
                'info'   => 'Colleague not found.',
            ];
        }

        /* Если коллега не из текущей коллаборации */
        if ($UserColleague->collaboration_id != $this->UserCollaboration->collaboration_id) {
            if (isset($transaction)) { $transaction->rollBack(); }
            return [
                'status' => false,
                'info'   => 'This colleague not found in this collaboration.',
            ];
        }

        /* нельзя удалить владельца коллаборации */
        if ($UserColleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
            if (isset($transaction)) { $transaction->rollBack(); }
            return [
                'status' => false,
                'info'   => "Can't delete owner of collaboration.",
            ];
        }

        /* Если коллега в очереди на добавление */
        if ($UserColleague->colleague_status == UserColleagues::STATUS_QUEUED_ADD) {
            if (isset($transaction)) { $transaction->rollBack(); }
            return [
                'status' => false,
                'info'   => "Colleague set in queue for add on this collaboration. Wait while it will be added",
                'hidden_info' => true,
            ];
        }

        /* Если коллега в очереди на удаление */
        if ($UserColleague->colleague_status == UserColleagues::STATUS_QUEUED_DEL) {
            if (isset($transaction)) { $transaction->rollBack(); }
            return [
                'status' => false,
                'info'   => "Colleague already set in queue for delete from this collaboration.",
                'hidden_info' => true,
            ];
        }

        /* Если коллега имеет лицензию TYPE_PAYED_BUSINESS_USER и его удаляет из коллаборации Бизнес Админ от которого прилетела лицензия,
           тогда бизнес-админ получает назад свою лицензию а юзеру (бывшему коллеге) присваивается лицензия TYPE_FREE_DEFAULT */
        $User_for_Colleague = Users::findIdentity($UserColleague->user_id);
        /*
        if (!$User_for_Colleague) {
            $transaction->rollBack();
            return [
                'status' => false,
                'info'   => "Can't find User with user_id={$UserColleague->user_id} for colleague_id={$UserColleague->colleague_id}.",
            ];
        }
        */
        if ($User_for_Colleague) {
            if (($User_for_Colleague->license_type == Licenses::TYPE_PAYED_BUSINESS_USER) && ($User_for_Colleague->license_business_from == $this->UserOwner->user_id)) {
                $query = "SELECT
                        t2.collaboration_id
                      FROM {{%user_colleagues}} as t1
                      INNER JOIN {{%user_collaborations}} as t2 ON t1.collaboration_id = t2.collaboration_id
                      WHERE t1.user_id = :colleague_user_id
                      AND t2.user_id = :owner_user_id
                      AND t2.collaboration_id != :collaboration_id
                      LIMIT 1";
                $test_res = Yii::$app->db->createCommand($query, [
                    'colleague_user_id' => $User_for_Colleague->user_id,
                    'owner_user_id' => $this->UserOwner->user_id,
                    'collaboration_id' => $this->UserCollaboration->collaboration_id,
                ])->queryOne();
                //var_dump($test_res); exit;
                if (!$test_res) {
                    $this->UserOwner->license_count_available++;
                    $this->UserOwner->license_count_used--;
                    $this->UserOwner->save();

                    $User_for_Colleague->previous_license_business_from = $this->UserOwner->user_id;
                    $User_for_Colleague->previous_license_business_finish = date(SQL_DATE_FORMAT);
                    $User_for_Colleague->license_type = Licenses::TYPE_FREE_DEFAULT;
                    $User_for_Colleague->license_business_from = null;
                    $User_for_Colleague->upl_limit_nodes = null;
                    $User_for_Colleague->upl_shares_count_in24 = null;
                    $User_for_Colleague->upl_max_shares_size = null;
                    $User_for_Colleague->upl_max_count_children_on_copy = null;
                    $User_for_Colleague->upl_block_server_nodes_above_bought = null;
                    $User_for_Colleague->license_expire = null;
                    $User_for_Colleague->_is_colleague_self_leave = $this->is_colleague_self_leave;
                    $User_for_Colleague->save();

                    $UserLicense = UserLicenses::getLicenseUsedBy($this->UserOwner->user_id, $User_for_Colleague->user_id);
                    if ($UserLicense) {
                        $UserLicense->lic_colleague_user_id = null;
                        $UserLicense->lic_colleague_email = null;
                        $UserLicense->save();
                    }
                }
            }
        } else {
            $query = "SELECT
                        t2.collaboration_id
                      FROM {{%user_colleagues}} as t1
                      INNER JOIN {{%user_collaborations}} as t2 ON t1.collaboration_id = t2.collaboration_id
                      WHERE t1.colleague_email = :colleague_email
                      AND t2.user_id = :owner_user_id
                      AND t2.collaboration_id != :collaboration_id
                      LIMIT 1";
            $test_res = Yii::$app->db->createCommand($query, [
                'colleague_email' => $UserColleague->colleague_email,
                'owner_user_id' => $this->UserOwner->user_id,
                'collaboration_id' => $this->UserCollaboration->collaboration_id,
            ])->queryOne();
            if (!$test_res) {
                $this->UserOwner->license_count_available++;
                $this->UserOwner->license_count_used--;
                $this->UserOwner->save();

                $UserLicense = UserLicenses::getLicenseUsedBy($this->UserOwner->user_id, null, $UserColleague->colleague_email);
                if ($UserLicense) {
                    $UserLicense->lic_colleague_user_id = null;
                    $UserLicense->lic_colleague_email = null;
                    $UserLicense->save();
                }
            }
        }

        /* Ставим статус коллеге о том что он в очереди на удаление */
        $UserColleague->colleague_status = UserColleagues::STATUS_QUEUED_DEL;
        if ($UserColleague->save()) {

            /* Успешное завершение транзакции */
            if (isset($transaction)) { $transaction->commit(); }

            /* через очередь или напрямую */
            if ($this->queue) {
                /* выполняем остальную часть через очередь */
                $event_uuid_from_node = md5($this->UserCollaboration->collaboration_id . microtime());
                $job_id = $this->queue->push(new ColleagueDeleteJob([
                    'CollaboratedFolder_file_id'         => $this->CollaboratedFolder->file_id,

                    'UserCollaboration_collaboration_id' => $this->UserCollaboration->collaboration_id,
                    'UserCollaboration_file_uuid'        => $this->UserCollaboration->file_uuid,
                    'UserCollaboration_user_id'          => $this->UserCollaboration->user_id,

                    'UserOwner_user_id'                  => $this->UserOwner->user_id,
                    'UserOwner_user_email'               => $this->UserOwner->user_email,
                    'UserOwner__full_path'               => $this->UserOwner->_full_path,

                    'UserColleague_user_id'              => $UserColleague->user_id,
                    'UserColleague_colleague_id'         => $UserColleague->colleague_id,
                    'is_colleague_self_leave'            => $this->is_colleague_self_leave,

                    'event_uuid_from_node'               => $event_uuid_from_node,
                ]));

                /* Эта хрень нужна что бы запустить выполенение задание в псевдоочереди, при Unit-тестировании */
                if (isset(Yii::$app->components['queue']['class'], Yii::$app->params['UnitTests']) &&
                    Yii::$app->components['queue']['class'] == 'yii\queue\sync\Queue') {
                    $this->queue->run(true);
                }

                $QueuedEvent = new QueuedEvents();
                $QueuedEvent->event_uuid = $event_uuid_from_node;
                $QueuedEvent->job_id     = (string) $job_id;
                $QueuedEvent->user_id    = $this->UserOwner->user_id;
                $QueuedEvent->node_id    = null;
                $QueuedEvent->job_status = QueuedEvents::STATUS_WAITING;
                $QueuedEvent->job_type   = QueuedEvents::TYPE_COLLEAGUE_DEL;
                $QueuedEvent->queue_id   = 'queue';
                $QueuedEvent->save();

                $queue_status = 'queued';
            } else {
                /* выполняем напрямую */
                $job_id = null;
                $queue_status = 'direct';
                $ret = self::colleagueDelete_exec(
                    $this->redis,
                    $this->CollaboratedFolder,

                    $this->UserCollaboration->collaboration_id,
                    $this->UserCollaboration->file_uuid,
                    $this->UserCollaboration->user_id,

                    $this->UserOwner->user_id,
                    $this->UserOwner->user_email,
                    $this->UserOwner->_full_path,

                    $UserColleague->user_id,
                    $UserColleague->colleague_id,
                    $this->is_colleague_self_leave,

                    ''
                );

                if (is_array($ret)) {
                    return $ret;
                }
            }

            /* Ответ */
            $data_send = UserColleagues::prepareColleagueData($UserColleague);
            if (!$job_id) {
                $data_send['status'] = 'deleted';
            }
            $data_send['collaboration_id'] = $UserColleague->collaboration_id;
            $data_send['file_uuid'] = $this->CollaboratedFolder->file_uuid;
            $data_send['file_name'] = $this->CollaboratedFolder->file_name;
            $data_send['is_colleague_self_leave'] = $this->is_colleague_self_leave;
            return [
                'status'     => true,
                'action'     => $this->action,
                'data'       => $data_send,
                //'event_data' => $event_data,
                'event_delete_answer' => isset($answer) ? $answer : false,
            ];
        } else {
            return [
                'status' => false,
                'info'   => "Database error: Can't save colleague to collaboration.",
                'debug'  => $UserColleague->getErrors(),
            ];

        }
    }

    /**
     * Функция для постановки в очередь операции на удаление коллеги
     * (может быть выполнена и без очереди в зависимости от настроек)
     * @param \yii\redis\Connection $redis
     * @param \common\models\UserFiles $CollaboratedFolder
     * @param integer $UserCollaboration_collaboration_id
     * @param string $UserCollaboration_file_uuid
     * @param integer $UserCollaboration_user_id
     * @param integer $UserOwner_user_id
     * @param string $UserOwner_user_email
     * @param string $UserOwner__full_path
     * @param integer $UserColleague_user_id
     * @param integer $UserColleague_colleague_id
     * @param bool $is_colleague_self_leave
     * @param string $event_uuid_from_node
     * @return array
     */
    public static function colleagueDelete_exec(
        &$redis,
        &$CollaboratedFolder,

        $UserCollaboration_collaboration_id,
        $UserCollaboration_file_uuid,
        $UserCollaboration_user_id,

        $UserOwner_user_id,
        $UserOwner_user_email,
        $UserOwner__full_path,

        $UserColleague_user_id,
        $UserColleague_colleague_id,
        $is_colleague_self_leave,

        $event_uuid_from_node)
    {

        /** переменные которые нужно передать в джоб

        // это объекты которые придется в классе джоба искать по их ИД
        $CollaboratedFolder;

        // это простые переменные которые передаются стандартно (не будем передавать весь объект для уменьшения запросов в классе джоба)
        $UserCollaboration->collaboration_id;
        $UserCollaboration->file_uuid;
        $UserCollaboration->user_id;

        $UserOwner->user_id;
        $UserOwner->user_email
        $UserOwner->_full_path;

        $UserColleague->user_id;
        $UserColleague->colleague_id;
         */

        /* До транзакции сделаем лок коллаборации если работа идет через очередь а если лок не удался
         * ставим задание в очередь снова через минуту
         * а текущее задание считаем отработанным */
        /** @var \yii\queue\file\Queue $queue */
        $queue = (isset(Yii::$app->queue) && method_exists(Yii::$app->queue, 'push')) ? Yii::$app->queue : false;

        /** @var \yii\mutex\FileMutex $mutex */
        $mutex = Yii::$app->mutex;
        $mutex_collaboration_name = 'collaboration_id_' . $UserCollaboration_collaboration_id;
        $mutex_collaboration_name_by_owner_user_id = 'collaboration_by_user_id' . $UserOwner_user_id;
        $mutex_collaboration_name_by_colleague_user_id = 'collaboration_by_user_id' . $UserColleague_user_id;

        if ($queue) {

            if (!$mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT)) {

                echo "collaboration_id = " . $UserCollaboration_collaboration_id . " is locked. Retry this job in 1 minutes.\n";

                $job_id = $queue->delay(60)->push(new ColleagueDeleteJob([
                    'CollaboratedFolder_file_id'         => $CollaboratedFolder->file_id,

                    'UserCollaboration_collaboration_id' => $UserCollaboration_collaboration_id,
                    'UserCollaboration_file_uuid'        => $UserCollaboration_file_uuid,
                    'UserCollaboration_user_id'          => $UserCollaboration_user_id,

                    'UserOwner_user_id'                  => $UserOwner_user_id,
                    'UserOwner_user_email'               => $UserOwner_user_email,
                    'UserOwner__full_path'               => $UserOwner__full_path,

                    'UserColleague_user_id'              => $UserColleague_user_id,
                    'UserColleague_colleague_id'         => $UserColleague_colleague_id,
                    'is_colleague_self_leave'            => $is_colleague_self_leave,

                    'event_uuid_from_node'               => $event_uuid_from_node,
                ]));

                $QueuedEvent = new QueuedEvents();
                $QueuedEvent->event_uuid = $event_uuid_from_node;
                $QueuedEvent->job_id     = (string) $job_id;
                $QueuedEvent->user_id    = $UserOwner_user_id;
                $QueuedEvent->node_id    = null;
                $QueuedEvent->job_status = QueuedEvents::STATUS_WAITING;
                $QueuedEvent->job_type   = QueuedEvents::TYPE_COLLEAGUE_DEL;
                $QueuedEvent->queue_id   = 'queue';
                $QueuedEvent->save();

                return true;
            }

            if (!$mutex->acquire($mutex_collaboration_name_by_owner_user_id, MUTEX_WAIT_TIMEOUT)) {

                echo "collaboration_id = " . $UserCollaboration_collaboration_id . " is locked. Retry this job in 1 minutes.\n";

                $job_id = $queue->delay(60)->push(new ColleagueDeleteJob([
                    'CollaboratedFolder_file_id'         => $CollaboratedFolder->file_id,

                    'UserCollaboration_collaboration_id' => $UserCollaboration_collaboration_id,
                    'UserCollaboration_file_uuid'        => $UserCollaboration_file_uuid,
                    'UserCollaboration_user_id'          => $UserCollaboration_user_id,

                    'UserOwner_user_id'                  => $UserOwner_user_id,
                    'UserOwner_user_email'               => $UserOwner_user_email,
                    'UserOwner__full_path'               => $UserOwner__full_path,

                    'UserColleague_user_id'              => $UserColleague_user_id,
                    'UserColleague_colleague_id'         => $UserColleague_colleague_id,
                    'is_colleague_self_leave'            => $is_colleague_self_leave,

                    'event_uuid_from_node'               => $event_uuid_from_node,
                ]));

                $QueuedEvent = new QueuedEvents();
                $QueuedEvent->event_uuid = $event_uuid_from_node;
                $QueuedEvent->job_id     = (string) $job_id;
                $QueuedEvent->user_id    = $UserOwner_user_id;
                $QueuedEvent->node_id    = null;
                $QueuedEvent->job_status = QueuedEvents::STATUS_WAITING;
                $QueuedEvent->job_type   = QueuedEvents::TYPE_COLLEAGUE_DEL;
                $QueuedEvent->queue_id   = 'queue';
                $QueuedEvent->save();

                return true;
            }

            if (!$mutex->acquire($mutex_collaboration_name_by_colleague_user_id, MUTEX_WAIT_TIMEOUT)) {

                echo "collaboration_id = " . $UserCollaboration_collaboration_id . " is locked. Retry this job in 1 minutes.\n";

                $job_id = $queue->delay(60)->push(new ColleagueDeleteJob([
                    'CollaboratedFolder_file_id'         => $CollaboratedFolder->file_id,

                    'UserCollaboration_collaboration_id' => $UserCollaboration_collaboration_id,
                    'UserCollaboration_file_uuid'        => $UserCollaboration_file_uuid,
                    'UserCollaboration_user_id'          => $UserCollaboration_user_id,

                    'UserOwner_user_id'                  => $UserOwner_user_id,
                    'UserOwner_user_email'               => $UserOwner_user_email,
                    'UserOwner__full_path'               => $UserOwner__full_path,

                    'UserColleague_user_id'              => $UserColleague_user_id,
                    'UserColleague_colleague_id'         => $UserColleague_colleague_id,
                    'is_colleague_self_leave'            => $is_colleague_self_leave,

                    'event_uuid_from_node'               => $event_uuid_from_node,
                ]));

                $QueuedEvent = new QueuedEvents();
                $QueuedEvent->event_uuid = $event_uuid_from_node;
                $QueuedEvent->job_id     = (string) $job_id;
                $QueuedEvent->user_id    = $UserOwner_user_id;
                $QueuedEvent->node_id    = null;
                $QueuedEvent->job_status = QueuedEvents::STATUS_WAITING;
                $QueuedEvent->job_type   = QueuedEvents::TYPE_COLLEAGUE_DEL;
                $QueuedEvent->queue_id   = 'queue';
                $QueuedEvent->save();

                return true;
            }
        }

        $transaction = Yii::$app->db->beginTransaction();

        $ObjectUserColleague = Users::findIdentity($UserColleague_user_id);

        /*Удаляем все копии файлов и папок которые принадлежат удаляемому из коллаборации коллеге */
        if ($UserColleague_user_id) {
            $DeleteColleagueCopyUserFile = UserFiles::findOne([
                'user_id'          => $UserColleague_user_id,
                'collaboration_id' => $UserCollaboration_collaboration_id,
                'is_folder'        => UserFiles::TYPE_FOLDER,
                'is_collaborated'  => UserFiles::FILE_COLLABORATED,
                'is_deleted'       => UserFiles::FILE_UNDELETED,
                'file_uuid'        => $CollaboratedFolder->file_uuid,
                //'file_parent_id'   => UserFiles::ROOT_PARENT_ID,
            ]);

            if ($DeleteColleagueCopyUserFile) {

                /* Создаем евент удаления папки коллеги */
                $last_event_id = UserFileEvents::find()
                    ->andWhere(['file_id' => $DeleteColleagueCopyUserFile->file_id])
                    ->max('event_id');

                $data['folder_uuid'] = $DeleteColleagueCopyUserFile->file_uuid;
                $data['last_event_id'] = $last_event_id;
                $data['is_from_colleagueDelete'] = true;
                //$data['is_permanent'] =

                $model = new NodeApi(['folder_uuid', 'last_event_id']);
                $model->load(['NodeApi' => $data]);
                $model->validate();
                $ColleagueUserNode = NodeApi::registerNodeFM($ObjectUserColleague);
                $answer = $model->folder_event_delete(
                    $ColleagueUserNode,
                    true,
                    false,
                    false
                );
                //var_dump($answer); exit;
                /*
                $relativePath = UserFiles::getFullPath($DeleteColleagueCopyUserFile);
                $UserColleagueFullPath = Users::getPathNodeFS($UserColleague_user_id);
                $folder_name = $UserColleagueFullPath->_full_path . '/' . $relativePath;
                FileSys::rmdir($folder_name, true);
                UserFiles::deleteAll([
                    'user_id' => $UserColleague_user_id,
                    'collaboration_id' => $UserCollaboration_collaboration_id,
                ]);
                */

                /* Удаляем всех чилдренов этой папки из базы данных начисто */
                $query =
                    "with recursive obj_tree as (
                    SELECT
                        file_id,
                        file_parent_id,
                        file_uuid,
                        file_name,
                        file_size,
                        is_folder,
                        is_updated,
                        is_deleted,
                        collaboration_id,
                        is_collaborated,
                        is_owner,
                        is_shared,
                        share_hash,
                        share_lifetime,
                        share_ttl_info,
                        share_password,
                        text(file_name) AS file_path
                    FROM {{%user_files}}
                    WHERE file_id = :file_id
                      UNION ALL
                    SELECT
                        t.file_id,
                        t.file_parent_id,
                        t.file_uuid,
                        t.file_name,
                        t.file_size,
                        t.is_folder,
                        t.is_updated,
                        t.is_deleted,
                        t.collaboration_id,
                        t.is_collaborated,
                        t.is_owner,
                        t.is_shared,
                        t.share_hash,
                        t.share_lifetime,
                        t.share_ttl_info,
                        t.share_password,
                        concat_ws(:separator, ff.file_path, t.file_name)
                    FROM {{%user_files}} AS t
                    JOIN obj_tree ff on ff.file_id = t.file_parent_id
                )
                DELETE FROM {{%user_files}} WHERE file_id IN (SELECT file_id FROM obj_tree WHERE file_id != :file_id);";
                $res = Yii::$app->db->createCommand($query, [
                    'file_id'   => $DeleteColleagueCopyUserFile->file_id,
                    'separator' => DIRECTORY_SEPARATOR,
                ])->query()->getRowCount();

                /* Удаляем все евенты для удаленной рут-папки коллаборации коллеги (сейчас --не ++уже нужно этого делать) */
                $query = "DELETE FROM {{%user_file_events}}
                          WHERE (file_id = :file_id)
                          AND (event_type != :TYPE_DELETE)";
                $res = Yii::$app->db->createCommand($query, [
                    'file_id'     => $DeleteColleagueCopyUserFile->file_id,
                    'TYPE_DELETE' => UserFileEvents::TYPE_DELETE,
                ])->query()->getRowCount();

            }
        } else {
            $DeleteColleagueCopyUserFile = null;
        }

        /* Удаляем коллегу из коллаборации */
        $countDel = UserColleagues::deleteAll(['colleague_id' => $UserColleague_colleague_id]);

        if ($countDel) {

            /* Отправляем в редис */
            try {
                $redis->publish("collaboration:{$UserCollaboration_collaboration_id}:userdel", $UserColleague_user_id);
                $redis->srem("collaboration:{$UserColleague_user_id}:folders", $UserCollaboration_file_uuid);
                $redis->srem("collaboration:{$UserCollaboration_collaboration_id}:users", $UserColleague_user_id);
                $redis->srem("user:{$UserColleague_user_id}:collaborations", $UserCollaboration_collaboration_id);
                $redis->save();
            } catch (\Exception $e) {
                if ($UserColleague_user_id) {
                    RedisSafe::createNewRecord(
                        RedisSafe::TYPE_COLLABORATION_CHANGES,
                        $UserColleague_user_id,
                        null,
                        Json::encode([
                            'collaboration_id' => $UserCollaboration_collaboration_id,
                            'action' => 'userdel',
                            'chanel' => "collaboration:{$UserCollaboration_collaboration_id}:userdel",
                            'user_id' => $UserColleague_user_id,
                        ])
                    );
                }
            }

            /* Ищем есть ли в этой коллаборации еще участники, если нет, то коллаборация автоматически удаляется */
            $count = UserColleagues::find()
                ->where(['collaboration_id' => $UserCollaboration_collaboration_id])
                ->andWhere('(user_id!=:user_id) OR (user_id IS NULL)', [':user_id' => $UserOwner_user_id])
                ->count('*');

            if ($count > 0) {
                $CollaboratedFolder->collaboration_id = $UserCollaboration_collaboration_id;
                $CollaboratedFolder->is_collaborated = UserFiles::FILE_COLLABORATED;
            } else {
                /* создаем репорт для овнера запись о законченной коллаборации */
                $ColleagueOwner = UserColleagues::findOne([
                    'collaboration_id' => $UserCollaboration_collaboration_id,
                    'user_id'          => $UserOwner_user_id,
                ]);
                if ($ColleagueOwner) {
                    $Report = ColleaguesReports::createNewReport(
                        [
                            'data' => [
                                'event_type_int'                  => ColleaguesReports::EXT_RPT_TYPE_COLLABORATION_DELETED,
                                'event_id'                        => 0,
                                'file_id'                         => $CollaboratedFolder->file_id,
                                'file_parent_id'                  => $CollaboratedFolder->file_parent_id,
                                'file_parent_id_before_event'     => $CollaboratedFolder->file_parent_id,
                                'file_name'                       => $CollaboratedFolder->file_name,
                                'file_name_before_event'          => $CollaboratedFolder->file_name,
                                'parent_folder_name'              => '',
                                'parent_folder_name_before_event' => '',
                                'is_folder'                       => $CollaboratedFolder->is_folder,
                            ]
                        ],
                        $ColleagueOwner,
                        NodeApi::registerNodeFM(Users::findIdentity($UserOwner_user_id))
                    );
                }

                /* Удаляем всех коллег и саму коллаборацию, если нет больше в ней коллег */
                UserColleagues::deleteAll(['collaboration_id' => $UserCollaboration_collaboration_id]);
                UserCollaborations::deleteAll(['collaboration_id' => $UserCollaboration_collaboration_id]);

                /* Отправляем в редис */
                try {
                    $redis->publish("collaboration:{$UserCollaboration_collaboration_id}:userdel", $UserOwner_user_id);
                    $redis->publish("collaboration:{$UserCollaboration_collaboration_id}:delete", "delete");
                    $redis->srem("collaboration:{$UserCollaboration_user_id}:folders", $UserCollaboration_file_uuid);
                    $redis->srem("user:{$UserOwner_user_id}:collaborations", $UserCollaboration_collaboration_id);
                    $redis->save();
                } catch (\Exception $e) {
                    RedisSafe::createNewRecord(
                        RedisSafe::TYPE_COLLABORATION_CHANGES,
                        $UserOwner_user_id,
                        null,
                        Json::encode([
                            'collaboration_id' => $UserCollaboration_collaboration_id,
                            'action'           => 'userdel',
                            'chanel'           => "collaboration:{$UserCollaboration_collaboration_id}:userdel",
                            'user_id'          => $UserOwner_user_id,
                        ])
                    );
                }

                /* обнуляем ИД коллаборации */
                $CollaboratedFolder->collaboration_id = null;
                $CollaboratedFolder->is_collaborated = UserFiles::FILE_UNCOLLABORATED;
            }

            /* ставим отметку что файл учавствует или не учавствует в коллаборации и сохраняем о нем данные */
            if ($CollaboratedFolder->save()) {

                /* записываем информацию о папке-файле коллаборации в пустышку-инфо */
                $relativePath = UserFiles::getFullPath($CollaboratedFolder);
                $folder_name = $UserOwner__full_path . '/' . $relativePath;
                if (!file_exists($folder_name)) {
                    FileSys::mkdir($folder_name, UserFiles::CHMOD_DIR, true);
                }
                $folder_info_file = $folder_name . '/' . UserFiles::DIR_INFO_FILE;
                FileSys::touch($folder_info_file, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
                UserFiles::createFileInfo($folder_info_file, $CollaboratedFolder);

                $event_data = [];
                /* это вызовется только в том случае если больше не осталось коллег в коллаборации */
                if (!$CollaboratedFolder->collaboration_id) {

                    /* запрос для выборки всех чилдренов (папок и файлов) овнера колллаборации */
                    $query =
                        "with recursive obj_tree as (
                            SELECT
                                file_id,
                                file_parent_id,
                                file_uuid,
                                last_event_uuid,
                                file_name,
                                file_size,
                                file_lastatime,
                                file_lastmtime,
                                folder_children_count,
                                is_folder,
                                is_updated,
                                is_deleted,
                                collaboration_id,
                                is_collaborated,
                                is_owner,
                                is_shared,
                                share_hash,
                                share_lifetime,
                                share_ttl_info,
                                share_password,
                                text(file_name) AS file_path
                            FROM {{%user_files}}
                            WHERE file_id = :file_id
                              UNION ALL
                            SELECT
                                t.file_id,
                                t.file_parent_id,
                                t.file_uuid,
                                t.last_event_uuid,
                                t.file_name,
                                t.file_size,
                                t.file_lastatime,
                                t.file_lastmtime,
                                t.folder_children_count,
                                t.is_folder,
                                t.is_updated,
                                t.is_deleted,
                                t.collaboration_id,
                                t.is_collaborated,
                                t.is_owner,
                                t.is_shared,
                                t.share_hash,
                                t.share_lifetime,
                                t.share_ttl_info,
                                t.share_password,
                                concat_ws(:separator, ff.file_path, t.file_name)
                            FROM {{%user_files}} AS t
                            JOIN obj_tree ff on ff.file_id = t.file_parent_id
                        )
                        SELECT * FROM obj_tree WHERE file_id != :file_id;";
                    $res = Yii::$app->db->createCommand($query, [
                        'file_id'   => $CollaboratedFolder->file_id,
                        'separator' => DIRECTORY_SEPARATOR,
                    ])->queryAll();

                    /* по полученным данным записываем (обновляем) информацию о файлах */
                    if (sizeof($res)) {
                        $upd_ids = [];
                        //var_dump($res);
                        foreach ($res as $UserFileOrigChild) {
                            //var_dump($UserFileOrigChild); exit;
                            $UserFileOrigChild['collaboration_id'] = null;
                            $upd_ids[] = $UserFileOrigChild['file_id'];

                            $file_name = $UserOwner__full_path . DIRECTORY_SEPARATOR . $UserFileOrigChild['file_path'];
                            if ($UserFileOrigChild['is_folder']) {
                                $file_name .= DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
                            }
                            //var_dump($file_name); exit;
                            $dir_name = dirname($file_name);
                            if (!file_exists($dir_name)) {
                                FileSys::mkdir($dir_name, UserFiles::CHMOD_DIR, true);
                            }
                            UserFiles::createFileInfoRaw($file_name, $UserFileOrigChild);
                        }
                        UserFiles::updateAll([
                            'collaboration_id' => null,
                        ], ['file_id' => $upd_ids]);
                    }

                }

                if (isset($answer['event_data'])) {
                    foreach ($answer['event_data'] as $k => $v) {
                        $event_data[] = $v;
                    }
                }

                //var_dump($event_data); exit;
                if (sizeof($event_data)) {
                    /*
                    try {
                        $this->redis->publish("collaboration:{$this->UserCollaboration->collaboration_id}:fsevent", Json::encode($event_data));
                        $this->redis->save();
                    } catch (\Exception $e) {}
                    */
                }

                /* находим реальное имя папки коллаборации для пользователя (оно может быть другим при создании) */
                /** @var \common\models\UserFiles $DeleteColleagueCopyUserFile */
                $root_folder_name_for_user = (isset($DeleteColleagueCopyUserFile))
                    ? $DeleteColleagueCopyUserFile->file_name
                    : $CollaboratedFolder->file_name;

                /* Создаем нотификайшен об ИСКЛЮЧЕНИИ для пользователя */
                if ($UserColleague_user_id) {
                    $notif = new Notifications();
                    $notif->user_id = $UserColleague_user_id;
                    $notif->notif_isnew = Notifications::IS_NEW;
                    if ($is_colleague_self_leave) {
                        $notif->notif_type = Notifications::TYPE_COLLABORATION_SELF_EXCLUDE;
                    } else {
                        $notif->notif_type = Notifications::TYPE_COLLABORATION_EXCLUDE;
                    }
                    $notif->notif_data = serialize([
                        'search' => [
                            '{folder_name}',
                            '{user_email}',
                            '{colleague_id}',
                            '{file_uuid}',
                        ],
                        'replace' => [
                            $root_folder_name_for_user,
                            $UserOwner_user_email,
                            $UserColleague_colleague_id,
                            $CollaboratedFolder->file_uuid,
                        ],
                    ]);
                    $notif->save();
                }
                /* Если пользователь сам покинул коллаборацию, создатим нотиф об этом для овнера */
                if ($is_colleague_self_leave) {
                    $notif = new Notifications();
                    $notif->user_id = $UserOwner_user_id;
                    $notif->notif_isnew = Notifications::IS_NEW;
                    $notif->notif_type = Notifications::TYPE_FOR_OWNER_COLLEAGUE_SELF_EXCLUDE;
                    $notif->notif_data = serialize([
                        'search' => [
                            '{folder_name}',
                            '{user_email}',
                            '{colleague_id}',
                            '{file_uuid}',
                        ],
                        'replace' => [
                            $CollaboratedFolder->file_name,
                            $ObjectUserColleague->user_email,
                            $UserColleague_colleague_id,
                            $CollaboratedFolder->file_uuid,
                        ],
                    ]);
                    $notif->save();
                }

                /* Заканчиваем транзакцию успешно и отправляем данные об удаленном колеге */
                $transaction->commit();
            } else {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info'   => 'Cant save UserFIle info for collaboration',
                    'debug'  => $CollaboratedFolder->getErrors(),
                ];
            }

        } else {
            $transaction->rollBack();
            return [
                'status' => false,
                'info'   => "Database error: Can't delete colleague.",
                'debug'  => "DEL COUNT=" . $countDel,
            ];
        }

        /* Освобождаем от блокировки по мутексу */
        $mutex->release($mutex_collaboration_name);
        $mutex->release($mutex_collaboration_name_by_owner_user_id);
        $mutex->release($mutex_collaboration_name_by_colleague_user_id);

        return true;
    }

    /**
     * Изменение прав для коллеги на папку коллаборации
     * @return array
     */
    public function colleagueEdit()
    {
        /* colleague_id - required */

        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();

        /* Находим нужную нам коллабоарацию или создаем ее если ее нет и это новая коллаборация */
        $initStatus = $this->collaborationInit();
        if (is_array($initStatus)) {
            return $initStatus;
        }

        /* Если не передан colleague_id но передан colleague_user_id то пытаемся найти нужный colleague_id*/
        if (!$this->colleague_id && $this->colleague_user_id) {
            if (!$this->initColleagueIDByUserID()) {
                return [
                    'status' => false,
                    'info'   => 'colleague_id not found for this colleague_user_id.',
                ];
            }
        }

        /* Если нет colleague_id то дальше не возмжна работа */
        if (!$this->colleague_id) {
            return [
                'status' => false,
                'info'   => "The colleague_id isn't given.",
            ];
        }

        /* Если не нашли коллегу с таим ИД - ошибка */
        $UserColleague = UserColleagues::findOne(['colleague_id' => $this->colleague_id]);
        if (!$UserColleague) {
            return [
                'status' => false,
                'info'   => 'Colleague not found.',
            ];
        }

        /* Устанавливаем новый тип прав коллеге и сохраняем */
        $UserColleague->colleague_permission = $this->access_type;
        if ($UserColleague->save()) {

            /* Создаем нотификайшен об ИЗМЕНЕНИИ ПРАВ для пользователя */
            if ($UserColleague->user_id) {

                /* Находим имя для папки коллаборации как оно у коллеги (может отличаться при создании) */
                $root_folder_for_colleague = UserFiles::findOne([
                    'user_id'          => $UserColleague->user_id,
                    'collaboration_id' => $this->CollaboratedFolder->collaboration_id,
                    'is_folder'        => UserFiles::TYPE_FOLDER,
                    'is_collaborated'  => UserFiles::FILE_COLLABORATED,
                    'is_deleted'       => UserFiles::FILE_UNDELETED,
                    'file_uuid'        => $this->CollaboratedFolder->file_uuid,
                    //'file_parent_id'   => UserFiles::ROOT_PARENT_ID,
                ]);
                $root_folder_name_for_colleague = ($root_folder_for_colleague)
                    ? $root_folder_for_colleague->file_name
                    : $this->CollaboratedFolder->file_name;

                /**/
                $notif = new Notifications();
                $notif->user_id = $UserColleague->user_id;
                $notif->notif_isnew = Notifications::IS_NEW;
                $notif->notif_type = Notifications::TYPE_COLLABORATION_CHANGE_ACCESS;
                $notif->notif_data = serialize([
                    'search' => [
                        '{folder_name}',
                        '{user_email}',
                        '{access_type}',
                        '{colleague_id}',
                        '{file_uuid}',
                    ],
                    'replace' => [
                        $root_folder_name_for_colleague,
                        $this->UserOwner->user_email,
                        $this->access_type,
                        $UserColleague->colleague_id,
                        $this->CollaboratedFolder->file_uuid,
                    ],
                ]);
                $notif->save();
            }

            /* Заканчиваем транзакцию успешно и отправляем данные */
            $transaction->commit();
            $data_send = UserColleagues::prepareColleagueData($UserColleague);
            $data_send['collaboration_id'] = $UserColleague->collaboration_id;
            $data_send['file_uuid'] = $this->CollaboratedFolder->file_uuid;
            $data_send['file_name'] = $this->CollaboratedFolder->file_name;
            return [
                'status' => true,
                'action' => $this->action,
                'data'   => $data_send,
            ];
        } else {
            $transaction->rollBack();
            return [
                'status' => false,
                'info'   => "Database error: Can't change permission for colleague.",
                'debug'  => $UserColleague->getErrors(),
            ];
        }
    }
    /************************ --- COLLABORATION  --- ***********************/
}
