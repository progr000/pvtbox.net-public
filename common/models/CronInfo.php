<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%cron_info}}".
 *
 * @property int $task_id Id
 * @property string $task_name Имя задачи
 * @property string $task_schedule Предпочтительное расписание запуска задачи
 * @property string $task_last_start Время последнего запуска задачи
 * @property string $task_last_finish Время последнего завершения задачи
 * @property string $task_log Лог задачи после последнего выполнения
 */
class CronInfo extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cron_info}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_name'], 'required'],
            [['task_last_start', 'task_last_finish'], 'safe'],
            [['task_log'], 'string'],
            [['task_name'], 'string', 'max' => 100],
            [['task_schedule'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'task_id' => 'Id',
            'task_name' => 'Task name',
            'task_schedule' => 'Preferred task schedule',
            'task_last_start' => 'Last time of task start',
            'task_last_finish' => 'Last time of task finish',
            'task_log' => 'Log after last task execute',
        ];
    }

    /**
     * @param string $task_name
     * @param string $task_last_start
     * @param string $task_last_finish
     * @param string|null $task_schedule
     * @param string|null $task_log
     */
    public static function setInfoForCronTask($task_name, $task_last_start, $task_last_finish, $task_schedule=null, $task_log=null)
    {
        $task = self::findOne(['task_name' => $task_name]);
        if (!$task) {
            $task = new CronInfo();
        }
        $task->task_name        = $task_name;
        $task->task_last_start  = $task_last_start;
        $task->task_last_finish = $task_last_finish;
        if (isset($task_schedule)) { $task->task_schedule = $task_schedule; }
        if (isset($task_log))      { $task->task_log = $task_log; }
        $task->save();
    }

    /**
     * Creates data provider instance with search query applied
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = self::find()->where("1=1");

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['task_id'=>SORT_DESC],
            ],
            'pagination' => false,
        ]);

        return $dataProvider;
    }

}
