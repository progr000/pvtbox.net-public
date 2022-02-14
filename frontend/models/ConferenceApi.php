<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\Json;
use common\models\Licenses;
use common\models\Users;
use common\models\UserConferences;
use common\models\ConferenceParticipants;
use common\models\Notifications;
use common\models\MailTemplatesStatic;
use frontend\models\forms\ShareElementForm;

/**
 * CollaborationApi
 *
 * @property integer $conference_id
 * @property string $conference_name
 * @property array $participants
 * @property integer $conference_status
 * @property integer $participant_id
 * @property string $room_uuid
 * @property string $conference_guest_hash
 *
 * @property string $signal_passphrase
 * @property string $user_hash
 * @property string $node_hash
 *
 * @property \yii\redis\Connection $redis
 * @property \yii\queue\file\Queue $queue
 * @property \yii\mutex\FileMutex $mutex
 *
 * @property \common\models\Users $UserOwner
 *
 */
class ConferenceApi extends Model
{
    public $node_hash, $user_hash;

    public $conference_id;
    public $conference_name;
    public $participants;
    public $conference_status;
    public $participant_id;
    public $room_uuid;
    public $conference_guest_hash;

    public $signal_passphrase;
    public $dynamic_rules = null;

    protected $redis;
    protected $mutex;
    protected $queue;

    /**************************** +++ GLOBAL +++ ***************************/
    /**
     * NodeApi constructor.
     * @param array $required_fields Поля которые будут проверяться на наличие в джсоне
     */
    public function __construct(array $required_fields = []/*, UserFiles $CollaboratedFolder*/)
    {
        if (is_array($required_fields) && sizeof($required_fields)) {
            $this->dynamic_rules = [[$required_fields, 'required', 'message' => 'Fields ' . implode(', ', $required_fields) . ' are required.']];
        }
        $this->redis = Yii::$app->redis;
        $this->mutex = Yii::$app->mutex;
        $this->queue = (isset(Yii::$app->queue) && method_exists(Yii::$app->queue, 'push')) ? Yii::$app->queue : false;

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
            [['participant_id', 'conference_id', 'conference_status'], 'integer'],
            [['conference_name'], 'string', 'max' => 50],
            [['conference_status'], 'in', 'range' => [UserConferences::STATUS_IDLE, UserConferences::STATUS_LIVE]],
            [['participants'], 'checkIsArray'],
            [['conference_guest_hash'], 'string', 'length' => 32],
            [['room_uuid'], 'string', 'length' => 32],
            [['signal_passphrase'], 'string', 'length' => 128],
        ];
        if (is_array($this->dynamic_rules)) {
            return array_merge($this->dynamic_rules, $rules);
        } else {
            return $rules;
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function checkIsArray($attribute, $params)
    {
        if (!is_array($this->$attribute)){
            $this->addError($attribute, "$attribute must be an array");
        }

        $arr = $this->$attribute;

        foreach ($arr as $v) {
            if (!isset($v['participant_email'])) {
                $this->addError($attribute, "$attribute is not a valid array");
                return;
            }
        }
    }
    /**************************** --- GLOBAL --- ***************************/


    /**
     * @param \common\models\Users $UserOwner
     * @return array
     */
    public function getListConferences($UserOwner)
    {
        $data = UserConferences::find()
            ->select("t1.*, t2.participant_status, t2.participant_email, t2.user_id as your_user_id")
            ->alias('t1')
            ->innerJoin('{{%conference_participants}} as t2', 't1.conference_id=t2.conference_id')
            ->where([
                't2.user_id' => $UserOwner->user_id,
                't2.participant_status' => [
                    ConferenceParticipants::STATUS_OWNER,
                    ConferenceParticipants::STATUS_JOINED,
                    //ConferenceParticipants::STATUS_INVITED,
                ],
            ])
            ->orderBy(['t1.conference_name' => SORT_ASC])
            ->asArray()
            ->all();
        foreach ($data as $k => $v) {
            $data[$k]['conference_guest_link'] = UserConferences::getConferenceGuestLinkBy($data[$k]['conference_guest_hash']);
        }
        return [
            'status' => true,
            'data'   => $data,
        ];
    }

    /**
     * @param \common\models\Users $UserOwner
     * @return array
     */
    public function getListAvailableParticipants($UserOwner)
    {
        return [
            'status' => true,
            'data'   => Yii::$app->db->createCommand("SELECT * FROM get_participants_for_conference(:user_id, :conference_id)", [
                'user_id'       => $UserOwner->user_id,
                'conference_id' => $this->conference_id,
            ])->queryAll()
        ];
    }

    /**
     * @param \common\models\Users $UserOwner
     * @return array
     */
    public function getListParticipants($UserOwner)
    {
        if (!ConferenceParticipants::findOne([
            'conference_id'      => $this->conference_id,
            'user_id'            => $UserOwner->user_id,
            'participant_status' => [
                ConferenceParticipants::STATUS_OWNER,
                ConferenceParticipants::STATUS_JOINED,
            ],
        ])) {
            return [
                'status' => false,
                'info'   => 'Conference with this conference_id not found',
            ];
        }

        $query = "SELECT
                    (CASE WHEN (t1.participant_status = :STATUS_OWNER) THEN 0 ELSE 100 END)::SMALLINT AS first_order,
                    (CASE WHEN (t1.participant_status = :STATUS_JOINED) THEN 1 ELSE 100 END)::SMALLINT AS second_order,
                    t1.participant_id     as participant_id,
                    t1.participant_email  as participant_email,
                    t1.participant_email  as participant_name,
                    t1.conference_id      as conference_id,
                    t1.user_id            as  user_id,
                    t1.participant_status as participant_status,
                    t1.participant_last_activity as participant_last_activity
                  FROM dl_conference_participants AS t1
                  WHERE (t1.conference_id = :conference_id)
                  ORDER BY first_order ASC,
                  second_order ASC,
                  --user_id ASC NULLS LAST,
                  participant_email ASC";
        $res = Yii::$app->db->createCommand($query, [
            'STATUS_OWNER' => ConferenceParticipants::STATUS_OWNER,
            'STATUS_JOINED' => ConferenceParticipants::STATUS_JOINED,
            'conference_id' => $this->conference_id,
        ])->queryAll();

        if (is_array($res)) {
            foreach ($res as $k => $v) {
                unset($res[$k]['first_order'], $res[$k]['second_order']);
                $res[$k]['participant_status'] = ConferenceParticipants::getStatus($res[$k]['participant_status']);
                $res[$k]['last_activity_timestamp'] = strtotime($res[$k]['participant_last_activity']);
            }
        } else {
            $res = [];
        }

        return [
            'status' => true,
            'data' => $res,
        ];
    }

    /**
     * @param \common\models\Users $UserOwner
     * @return array
     */
    public function setListParticipants($UserOwner)
    {
        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();

        $is_new_conference = false;

        //var_dump($this->conference_id);
        /* если это не новая конференция которая была создана ранее - значит просто меняются в ней участники */
        if ($this->conference_id > 0) {
            $Conference = UserConferences::findIdentity($this->conference_id);
            /* если такая конференция существует и принадлежит этому юзеру */
            if ($Conference && $Conference->user_id == $UserOwner->user_id) {
                /* если есть список участников $this->participants то нужно
                 * сначала удалить из бызы тех, кого нет в списке,
                 * затем добавить тех кого не хватает
                 * (их можно определить по параметру user_enabled
                 * если он = 0 значит этого пользователя еще
                 * нет в базе и нужно его добавить и
                 * выполнить инвайты в случае бизнес акка)*/
                if ($this->participants) {

                    /* соберем два списка емейлов (один поможет в удалении не нужных, второй в добавлении нехватающих)*/
                    $all_emails = [];
                    foreach ($this->participants as $participant) {
                        $all_emails[] = $participant['participant_email'];
                    }

                    /* сначала удаляем уже ненужных участников */
                    if (sizeof($all_emails)) {
                        $queryBuild = new Query();
                        $query_delete = $queryBuild->from('{{%conference_participants}}') ->where([
                            'AND',
                            'conference_id = :conference_id',
                            //'participant_status != :STATUS_OWNER',
                            ['NOT IN', 'participant_email', $all_emails],
                        ])->createCommand()->getRawSql();

                        $query_delete = str_replace(['select', 'SELECT', '*'], '', $query_delete);
                        $query_delete = 'DELETE ' . $query_delete . ' RETURNING *';
                    }

                /* если же $this->participants пустой (null) то нужно удалить всех кто есть в базе для этой конфы */
                } else {
                    $query_delete = "DELETE FROM {{%conference_participants}}
                                     WHERE conference_id = :conference_id
                                     --AND participant_status != :STATUS_OWNER
                                     RETURNING *";
                }
                /* удаление */
                if (isset($query_delete)) {
                    $res_delete = Yii::$app->db->createCommand($query_delete, [
                        'conference_id' => $this->conference_id,
                        //'STATUS_OWNER'  => ConferenceParticipants::STATUS_OWNER,
                    ])->queryAll();
                    if (sizeof($res_delete)) {
                        foreach ($res_delete as $v) {
                            if ($v['user_id'] && $v['participant_status'] !== ConferenceParticipants::STATUS_OWNER) {
                                $notif = new Notifications();
                                $notif->user_id = $v['user_id'];
                                $notif->notif_isnew = Notifications::IS_NEW;
                                $notif->notif_type = Notifications::TYPE_CONFERENCE_EXCLUDE;
                                $notif->notif_data = serialize([
                                    'search' => [
                                        '{conference_name}',
                                        '{user_email}',
                                    ],
                                    'replace' => [
                                        $Conference->conference_name,
                                        $UserOwner->user_email,
                                    ],
                                ]);
                                $notif->save();

                                // publish в редис "conferences:rooms:<room_uuid>:kick" опубликовав user_id
                                if ($Conference->room_uuid) {
                                    try {
                                        $this->redis->publish(
                                            "conferences:rooms:{$Conference->room_uuid}:kick",
                                            $v['user_id']
                                        );
                                        $this->redis->save();
                                    } catch (\Exception $e) {}
                                }
                            }
                        }
                    }
                }

            /* если конференция не найдена или ваделец не совпадает */
            } else {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info'   => 'Conference with this conference_id not found',
                ];
            }
        /* Если же это новая конференция, нужно ее создать
         * и затем доавить всех участников из $this->participants
         * и в случае бизнес акка выполнить инваты */
        } else {
            if (UserConferences::findOne([
                'conference_name' => $this->conference_name,
                'user_id'         => $UserOwner->user_id,
            ])) {
                return [
                    'status'  => false,
                    'errcode' => 'CONFERENCE_ALREADY_EXIST',
                    'info'    => "Conference with name '{$this->conference_name}' already exist",
                ];
            }

            $is_new_conference = true;
            $Conference = new UserConferences();
            $Conference->conference_status = UserConferences::STATUS_IDLE;
            $Conference->room_uuid = null;
            $Conference->conference_name = $this->conference_name;
            $Conference->user_id = $UserOwner->user_id;
            $Conference->conference_participants = 'set it field after add all participants';
            $Conference->generateGuestHash();
            if (!$Conference->save()) {
                return [
                    'status' => false,
                    'info'   => 'Some errors during work with DB',
                    'debug'  => $Conference->getErrors(),
                ];
            }
        }

        /* теперь добавляем тех кого не хватает и в случае бизнес ака сделать инвайты в колабы для снятия лицензий */
        if ($Conference) {

            $this->conference_id = $Conference->conference_id;

            /* добавление партиципанта-овнера если это новая конфа */
            //if ($is_new_conference) {
                $newParticipant = new ConferenceParticipants();
                $newParticipant->participant_status = ConferenceParticipants::STATUS_OWNER;
                $newParticipant->participant_invite_date = $Conference->conference_created;
                $newParticipant->participant_joined_date = $Conference->conference_created;
                $newParticipant->participant_last_activity = date(SQL_DATE_FORMAT);
                $newParticipant->participant_email = $UserOwner->user_email;
                $newParticipant->conference_id = $Conference->conference_id;
                $newParticipant->user_id = $UserOwner->user_id;
                //$newParticipant->save();
                if (!$newParticipant->save()) {
                    $transaction->rollBack();
                    return [
                        'status' => false,
                        'info'   => 'Some errors during work with DB',
                        'debug'  => $newParticipant->getErrors(),
                    ];
                }
            //}

            /* добавление списка выбранных партиципантов */
            if (is_array($this->participants)) {
                foreach ($this->participants as $participant) {
                    if (intval($participant['user_enabled']) == 0) {

                        if ($UserOwner->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                            $model = new ShareElementForm(['colleague_email']);
                            $model->colleague_email = $participant['participant_email'];
                            $ret = $model->adminPanelCreateNullCollaboration($UserOwner);

                            /* если какая либо ошибка тут, то транзакция отменяется */
                            if (!$ret['status']) {
                                $transaction->rollBack();
                                return $ret;
                            }
                        }

                        $User = Users::findByEmail($participant['participant_email']);
                        $newParticipant = new ConferenceParticipants();
                        $newParticipant->participant_status = ConferenceParticipants::STATUS_INVITED;
                        $newParticipant->participant_email = $participant['participant_email'];
                        $newParticipant->conference_id = $Conference->conference_id;
                        $newParticipant->participant_last_activity = $User ? $User->user_updated : null;
                        $newParticipant->user_id = $User ? $User->user_id : null;
                        if (!$newParticipant->save()) {
                            $transaction->rollBack();
                            return [
                                'status' => false,
                                'info'   => 'Some errors during work with DB',
                                'debug'  => $newParticipant->getErrors(),
                            ];
                        }

                        /* Создаем нотификайшн о ПРИГЛАШЕНИИ (invite) ++создаем письмо */
                        MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_ConferenceInvite, $newParticipant->participant_email, [
                            'UserObject'                  => $User ? $User : null,
                            'UserOwner_email'             => $UserOwner->user_email,
                            'UserOwner_name'              => $UserOwner->user_name,
                            'conference_name'             => $Conference->conference_name,
                            'conference_id'               => $Conference->conference_id,
                            'participant_id'              => $newParticipant->participant_id,
                            'conference_invite_link'      => Yii::$app->urlManager->createAbsoluteUrl(['conferences/accept-invitation', 'participant_id' => $newParticipant->participant_id]),
                        ]);
                        if ($User) {
                            $notif = new Notifications();
                            $notif->user_id = $User->user_id;
                            $notif->notif_isnew = Notifications::IS_NEW;
                            $notif->notif_type = Notifications::TYPE_CONFERENCE_INVITE;
                            $notif->notif_data = serialize([
                                'search' => [
                                    '{conference_name}',
                                    '{user_email}',
                                    '{participant_id}',
                                ],
                                'replace' => [
                                    $Conference->conference_name,
                                    $UserOwner->user_email,
                                    $newParticipant->participant_id
                                ],
                                'links_data' => [
                                    'accept_link' => ['conferences/accept-invitation', 'participant_id' => $newParticipant->participant_id],
                                ],
                            ]);
                            $notif->save();
                        }

                    }
                }
            }

            /* теперь нужно обновить данные об участниках в этом поле на основе данных из таблиц
             * participants и users ( вторая таблица для получения данных об онлайн )
             * сделать выборку из этих таблиц и сформировать удобный джсон который записать в это поле
             * а также вернуть это поле в ретурне для обработки на стороне клиента яваскриптом */
            //ConferenceParticipants
            $transaction->commit();

            $participants = $this->getListParticipants($UserOwner);

            if (!$Conference->conference_guest_hash) { $Conference->generateGuestHash(); }
            $Conference->conference_participants = Json::encode($participants['data']);
            $Conference->save();

            return [
                'status' => true,
                'data'   => [
                    'is_new_conference'     => $is_new_conference,
                    'conference_id'         => $Conference->conference_id,
                    'conference_name'       => $Conference->conference_name,
                    'conference_guest_hash' => $Conference->conference_guest_hash,
                    'conference_guest_link' => $Conference->conference_guest_link,
                    'participants'          => $participants['data'],
                ],
            ];

        } else {
            $transaction->rollBack();
            return [
                'status' => false,
                'info'   => 'Some errors during work with DB',
            ];
        }
    }

    /**
     * @param \common\models\Users $UserOwner
     * @return array
     */
    public function generateGuestLink($UserOwner)
    {
        /**/
        $Conference = UserConferences::findIdentity($this->conference_id);
        if (!$Conference) {
            return [
                'status' => false,
                'info' => 'Conference with this conference_id not found',
            ];
        }

        /**/
        if ($Conference->user_id != $UserOwner->user_id) {
            return [
                'status'   => false,
                'info' => 'Conference with this conference_id not found',
            ];
        }

        /**/
        if ($Conference) {
            $Conference->generateGuestHash();
        }

        /**/
        if (!$Conference->save()) {
            return [
                'status'   => false,
                'info'     => 'Some errors during work with DB',
                'debug'    => $Conference->getErrors(),
                'redirect' => ['user/conferences'],
            ];
        }

        /* возврат успеха */
        return [
            'status' => true,
            'data'   => [
                'conference_id'         => $Conference->conference_id,
                'conference_name'       => $Conference->conference_name,
                'conference_guest_hash' => $Conference->conference_guest_hash,
                'conference_guest_link' => $Conference->conference_guest_link,
            ],
        ];
    }

    /**
     * @param \common\models\Users|null $invitedUser
     * @return array
     */
    public function acceptInvitation($invitedUser)
    {
        Yii::$app->session->remove('is_from_guest_sign');
        Yii::$app->session->remove('is_from_guest_login');
        Yii::$app->session->remove('is_from_guest_participant_id');

        /* Ищем коллегу по его ИД */
        $ConferenceParticipant = ConferenceParticipants::findOne(['participant_id' => $this->participant_id]);
        if (!$ConferenceParticipant) {
            return [
                'status'   => false,
                'redirect' => $invitedUser ? ['/'] : ['/'],
                'info'     => "You now are not participant of this conference. You can't join it.",
            ];
        }

        /* если уже заджойнился ранее */
        if ($ConferenceParticipant->participant_status == ConferenceParticipants::STATUS_JOINED) {
            $ConferenceParticipant->participant_last_activity = date(SQL_DATE_FORMAT);
            $ConferenceParticipant->save();
            return [
                'status'   => true,
                'info'     => "You are already joined to this conference.",
                'type'     => "danger",
                'data'   => [
                    'conference_id'   => $ConferenceParticipant->conference_id,
                ],
            ];
        }

        /* Если не залогинен посетитель */
        if (!$invitedUser) {
            if ($ConferenceParticipant->user_id) {
                $User = Users::findIdentity($ConferenceParticipant->user_id);
            } else {
                $User = Users::findOne(['user_email' => $ConferenceParticipant->participant_email]);
            }

            Yii::$app->session->set('is_from_guest_participant_id', $ConferenceParticipant->participant_id);
            if ($User) {
                Yii::$app->session->set('is_from_guest_login', $User->user_email);
                return [
                    'status'   => false,
                    'redirect' => ['/site/login'],
                    'info'     => "You must be logged for join this conference.",
                ];
            } else {
                Yii::$app->session->set('is_from_guest_sign', $ConferenceParticipant->participant_email);
                return [
                    'status'   => false,
                    'redirect' => ['/site/signup', 'participant_id' => $this->participant_id],
                    'info'     => "Signup for free with email &lt;<b>{$ConferenceParticipant->participant_email}</b>&gt; for join this conference.",
                ];
            }
            /* Если залогинен */
        } else {

            /* Если не заполнено поле user_id в записи о коллеге */
            if (!$ConferenceParticipant->user_id) {
                if ($ConferenceParticipant->participant_email !== $invitedUser->user_email) {
                    return [
                        'status' => false,
                        'info' => "Wrong link (user_email mismatch). Access denied.",
                    ];
                } else {
                    $ConferenceParticipant->user_id = $invitedUser->user_id;
                    if (!$ConferenceParticipant->save()) {
                        return [
                            'status' => false,
                            'info'   => "Can't change user_id from null to id of User",
                            'debug'   => $ConferenceParticipant->getErrors(),
                        ];
                    }
                }
            } else if ($ConferenceParticipant->user_id !== $invitedUser->user_id) {
                return [
                    'status' => false,
                    'info' => "Wrong link (user_id mismatch). Access denied.",
                ];
            }

            /* Ищем коллаборацию для найденного коллеги */
            $UserConference = UserConferences::findOne(['conference_id' => $ConferenceParticipant->conference_id]);
            if (!$UserConference) {
                return [
                    'status' => false,
                    'info' => "Can't find conference for participant_id={$this->participant_id}",
                ];
            }

            /* Создаем нотиф для овнера конференции о том что юзер заджонился */
            $notif = new Notifications();
            $notif->user_id = $UserConference->user_id;
            $notif->notif_isnew = Notifications::IS_NEW;
            $notif->notif_type = Notifications::TYPE_CONFERENCE_ABOUT_JOIN_FOR_ADMIN;
            $notif->notif_data = serialize([
                'search' => [
                    '{conference_id}',
                    '{conference_name}',
                    '{user_email}',
                ],
                'replace' => [
                    $UserConference->conference_id,
                    $UserConference->conference_name,
                    $ConferenceParticipant->participant_email,
                ],
            ]);
            $notif->save();

            /* начинаем транзакцию */
            $transaction = Yii::$app->db->beginTransaction();

            /* Джойним юзера в эту конференцию */
            $ConferenceParticipant->participant_status = ConferenceParticipants::STATUS_JOINED;
            $ConferenceParticipant->participant_last_activity = date(SQL_DATE_FORMAT);
            $ConferenceParticipant->participant_joined_date = date(SQL_DATE_FORMAT);
            if (!$ConferenceParticipant->save()) {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info'   => 'Some errors during work with DB',
                    'debug'  => $ConferenceParticipant->getErrors(),
                ];
            }

            /* перезаписываем данные об участниках в поле со списком участников в таблице конференций */
            $this->conference_id = $UserConference->conference_id;
            $participants = $this->getListParticipants(Users::findIdentity($UserConference->user_id));
            $UserConference->conference_participants = Json::encode($participants['data']);
            if (!$UserConference->save()) {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info'   => 'Some errors during work with DB',
                    'debug'  => $UserConference->getErrors(),
                ];
            }

            /* плдтверждаем транзакцию */
            $transaction->commit();

            /* возврат успеха */
            return [
                'status' => true,
                'info'   => "You are joined to conference {$UserConference->conference_name}",
                'data'   => [
                    'conference_id'   => $UserConference->conference_id,
                    'conference_name' => $UserConference->conference_name,
                ],
            ];
        }
    }

    /**
     * @param \common\models\Users $currentUser
     * @return array
     */
    public function cancelConference($currentUser)
    {
        $Conference = UserConferences::findIdentity($this->conference_id);

        /* если конференции с таким ид нет */
        if (!$Conference) {
            return [
                'status' => false,
                'info' => 'Conference with this conference_id not found',
            ];
        }

        /* начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();

        $room_uuid = $Conference->room_uuid;

        /* если владелец удаляет колабу */
        if ($Conference->user_id == $currentUser->user_id) {
            $owner_cancel = true;

            /* создаем нотисы для участников */
            $Participants = ConferenceParticipants::findAll([
                'conference_id' => $this->conference_id,
                'participant_status' => [
                    ConferenceParticipants::STATUS_JOINED,
                    ConferenceParticipants::STATUS_INVITED,
                ],
            ]);
            foreach ($Participants as $participant) {
                if ($participant->user_id) {
                    $notif = new Notifications();
                    $notif->user_id = $participant->user_id;
                    $notif->notif_isnew = Notifications::IS_NEW;
                    $notif->notif_type = Notifications::TYPE_CONFERENCE_EXCLUDE;
                    $notif->notif_data = serialize([
                        'search' => [
                            '{conference_name}',
                            '{user_email}',
                        ],
                        'replace' => [
                            $Conference->conference_name,
                            $currentUser->user_email,
                        ],
                    ]);
                    $notif->save();
                }
            }

            /* Удаляем запись о конференции */
            if (!$Conference->delete()) {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info' => 'Some errors during work with DB',
                    'debug' => $Conference->getErrors(),
                ];
            }

        /* если участник инициировал свой выход */
        } else {
            $owner_cancel = false;

            /* создаем нотис для овнера */
            $Participant = ConferenceParticipants::findOne([
                'user_id'       => $currentUser->user_id,
                'conference_id' => $Conference->conference_id,
            ]);
            if ($Participant) {
                $notif = new Notifications();
                $notif->user_id = $Conference->user_id;
                $notif->notif_isnew = Notifications::IS_NEW;
                $notif->notif_type = Notifications::TYPE_CONFERENCE_LEAVE;
                $notif->notif_data = serialize([
                    'search' => [
                        '{conference_id}',
                        '{conference_name}',
                        '{user_email}',
                    ],
                    'replace' => [
                        $Conference->conference_id,
                        $Conference->conference_name,
                        $currentUser->user_email,
                    ],
                ]);
                $notif->save();

                /* Удаляем запись об участнике */
                if (!$Participant->delete()) {
                    $transaction->rollBack();
                    return [
                        'status' => false,
                        'info' => 'Some errors during work with DB',
                        'debug' => $Participant->getErrors(),
                    ];
                }

                /* перезаписываем данные об участниках в поле со списком участников в таблице конференций */
                $participants = $this->getListParticipants(Users::findIdentity($Conference->user_id));
                $Conference->conference_participants = Json::encode($participants['data']);
                if (!$Conference->save()) {
                    $transaction->rollBack();
                    return [
                        'status' => false,
                        'info'   => 'Some errors during work with DB',
                        'debug'  => $Conference->getErrors(),
                    ];
                }
            }
        }

        /* подтверждаем транзакцию */
        $transaction->commit();

        /* работа с редис в случае успешной транзакции */
        if ($room_uuid) {
            if ($owner_cancel) {
                // publish в редис "conferences:rooms:<room_uuid>:closed"
                try {
                    $this->redis->publish(
                        "conferences:rooms:{$room_uuid}:closed",
                        uniqid() // вместо пустого сообщения
                    );
                    $this->redis->save();
                } catch (\Exception $e) {}
            } else {
                // publish в редис "conferences:rooms:<room_uuid>:kick" опубликовав user_id исключаемого участника
                try {
                    $this->redis->publish(
                        "conferences:rooms:{$room_uuid}:kick",
                        $currentUser->user_id
                    );
                    $this->redis->save();
                } catch (\Exception $e) {}
            }
        }

        /* возврат успеха */
        return [
            'status' => true,
        ];
    }

    /**
     * @param \common\models\Users|null $currentUser
     * @return array
     */
    public function openConference($currentUser)
    {
        /*
        При Join room:
        Ничео не делать кроме как открыть страницу комнаты

        При Open room:

        сгенерировать уникальный идентификатор комнаты room_uuid
        записать идентификатор комнаты в базу данных
        сделать publish в редис conferences:rooms:<room_uuid>:opened

        сделать publish в редис conferences:rooms:<room_uuid>:call
        опубликовав json {conference_name: <conference_name>, users: [<user_id>]}
        где users - список всех user_id пользователей входящих в конференцию,
        кроме текущего пользователя нажавшего Open room.

        Открыть страницу комнаты
        */

        /**/
        if ($currentUser) {
            $fail_redirect = ['user/conferences'];
        } else {
            $fail_redirect = ['/'];
        }

        /* начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();

        /**/
        $Conference = null;
        $Participant = null;

        /**/
        if ($currentUser) {
            $Participant = ConferenceParticipants::findOne([
                'conference_id' => $this->conference_id,
                'user_id' => $currentUser->user_id
            ]);
            $Conference = UserConferences::findIdentity($this->conference_id);
        } elseif ($this->conference_guest_hash) {
            $Participant = 'guest';
            $Conference = UserConferences::findByGuestHash($this->conference_guest_hash);
        }

        /* если конференции с таким ид нет */
        if (!$Participant || !$Conference) {
            return [
                'status' => false,
                'info' => 'Conference not found',
                'redirect' => $fail_redirect,
            ];
        }

        if ($Participant == 'guest' && $Conference->conference_status != UserConferences::STATUS_LIVE) {
            return [
                'status' => false,
                'info' => 'Conference not opened yet. Try later.',
                'redirect' => $fail_redirect,
            ];
        }

        /**/
        if ($Conference->room_uuid) {
            // это джйон в существующую
            $Conference->conference_status = UserConferences::STATUS_LIVE;
        } else {
            // это открытие новой
            $Conference->room_uuid = md5(uniqid());
            $Conference->conference_status = UserConferences::STATUS_LIVE;
        }

        /**/
        if (!$Conference->save()) {
            $transaction->rollBack();
            return [
                'status'   => false,
                'info'     => 'Some errors during work with DB',
                'debug'    => $Conference->getErrors(),
                'redirect' => $fail_redirect,
            ];
        }

        /* подготовка массива ид юзеров для редис */
        $tmp = json_decode($Conference->conference_participants, true);
        $users_ids = [];
        foreach ($tmp as $itemParticipant) {
            if (!empty($itemParticipant['user_id'])) {
                //if ($currentUser && $itemParticipant['user_id'] != $currentUser->user_id) {
                    $users_ids[] = $itemParticipant['user_id'];
                //}
            }
        }

        /* урл для открытия комнаты через ноды */
        if ($this->user_hash && $this->node_hash) {
            $page_access_token = md5(uniqid());
            if ($currentUser) {
                Yii::$app->cache->set($page_access_token, $currentUser->user_id);
            }
            $open_join_url = Yii::$app->urlManager->createAbsoluteUrl([
                'conferences/open-conference',
                'conference_id' => $Conference->conference_id,
                'header-free' => 1,
                'access_token' => $page_access_token
            ]);
        } else {
            $page_access_token = null;
            $open_join_url     = null;
        }

        /* отправка в редис */
        try {
            $this->redis->publish(
                "conferences:rooms:{$Conference->room_uuid}:call",
                Json::encode([
                    'conference_id'     => $Conference->conference_id,
                    'conference_name'   => $Conference->conference_name,
                    'caller_user_id'    => $currentUser ? $currentUser->user_id : null,
                    'users'             => $users_ids,
                    'page_access_token' => $page_access_token,
                    'room_url'          => $open_join_url,
                ])
            );
            if (!$this->redis->save()) {
                $transaction->rollBack();
                return [
                    'status'   => false,
                    'info'     => 'Redis save data failed',
                    'redirect' => $fail_redirect,
                ];
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'status'   => false,
                'info'     => 'Redis connection failed',
                'debug'    => $e,
                'redirect' => $fail_redirect,
            ];
        }

        /* возврат успеха */
        return [
            'status' => true,
            'data'   => [
                'conference_id'           => $Conference->conference_id,
                'conference_name'         => $Conference->conference_name,
                'room_uuid'               => $Conference->room_uuid,
                'conference_guest_link'   => $Conference->conference_guest_link,
                'conference_guest_hash'   => $Conference->conference_guest_hash,
                'conference_participants' => json_decode($Conference->conference_participants),
                'conference_status'       => $Conference->conference_status,
                'users_ids'               => isset($users_ids) ? $users_ids : [],
                'user_hash'               => $currentUser ? $currentUser->user_remote_hash : null,
                'page_access_token'       => $page_access_token,
                'open_join_url'           => $open_join_url,
            ],
        ];
    }

    /**
     * Метод для сигнального - который закрывает комнату (обнуляет room_uuid)
     * @return array
     */
    public function closeRoom()
    {
        $Conference = UserConferences::findByRoom($this->room_uuid);

        if (!$Conference) {
            return [
                'result'  => "error",
                'errcode' => "CONFERENCE_NOT_FOUND",
                'info'    => "Conference with room_uuid='{$this->room_uuid}' not found",
            ];
        }

        $Conference->room_uuid = null;
        $Conference->conference_status = UserConferences::STATUS_IDLE;
        if (!$Conference->save()) {
            return [
                'result'  => "error",
                'errcode' => "DATABASE_FAILURE",
                'info'    => "An internal server error occurred.",
                'debug'   => $Conference->getErrors(),
            ];
        }

        return [
            'result'  => "success",
        ];
    }

    /**
     * Метод для сигнального - который проверяет можно л и этому юзеру в эту конфу
     * @return array
     */
    public function checkParticipantAuth()
    {
        $Conference = UserConferences::findByRoom($this->room_uuid);
        if (!$Conference) {
            return [
                'result'  => "error",
                'errcode' => "CONFERENCE_NOT_FOUND",
                'info'    => "Conference with room_uuid='{$this->room_uuid}' not found",
            ];
        }

        $User = Users::findByUserRemoteHash($this->user_hash);
        if (!$User) {
            return [
                'result'  => "error",
                'errcode' => "USER_NOT_FOUND",
                'info'    => "User with user_hash='{$this->user_hash}' not found",
            ];
        }

        $Participant = ConferenceParticipants::findOne([
            'conference_id' => $Conference->conference_id,
            'user_id'       => $User->user_id,
        ]);
        if (!$Participant) {
            return [
                'result'  => "error",
                'errcode' => "ACCESS_DENIED",
                'info'    => "This user are not participant for this conference",
            ];
        }

        return [
            'result'  => "success",
            'data' => [
                'user_id'    => $User->user_id,
                'user_email' => $User->user_email,
                'user_name'  => $User->user_name,
            ],
        ];
    }
}
