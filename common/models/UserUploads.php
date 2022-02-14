<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%user_uploads}}".
 *
 * @property string $upload_id
 * @property string $upload_md5
 * @property string $upload_path
 * @property string $upload_size
 * @property integer $file_parent_id
 * @property string $user_id
 * @property string $node_id
 * @property string $upload_saved_name
 *
 * @property Users $user
 */
class UserUploads extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_uploads}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['upload_size', 'user_id', 'node_id'], 'integer'],
            [['upload_size'], 'default', 'value' => 0],
            [['upload_md5'], 'string', 'max' => 32],
            [['upload_path'], 'safe'],
            [['upload_saved_name'], 'string', 'max' => 255],
            [['upload_path', 'user_id'], 'unique', 'targetAttribute' => ['upload_path', 'user_id'], 'message' => 'The combination of File Name and Id has already been taken.'],
            [['file_parent_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'upload_id' => 'Id',
            'upload_md5' => 'MD5 File',
            'upload_path' => 'File Name',
            'upload_size' => 'Upload Size',
            'file_parent_id' => 'Parent ID',
            'user_id' => 'UserID',
            'node_id' => 'NodeID',
            'upload_saved_name' => 'file name as it is saved'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

    /**
     * Метод приведения пути к нормальному виду
     *
     * @param string $pathToNodeFs
     * @param string $fullPath
     * @return string
     */
    public static function normalizePath($pathToNodeFs, $fullPath)
    {
        $pathToNodeFs = str_replace("\\", "/", realpath($pathToNodeFs)). "/";
        $fullPath = str_replace("\\", "/", realpath($fullPath));
        $path = str_replace($pathToNodeFs, '', $fullPath);
        return $path;
    }

    /**
     * @param array|string $condition
     * @param array|null $params
     * @return int|null
     */
    public static function deleteRecords($condition, $params=null)
    {
        //var_dump($condition);
        //var_dump($params);
        if (!$params) { $params = []; }
        $where = Yii::$app->db->queryBuilder->buildWhere($condition, $params);
        //var_dump($where);
        //var_dump($params);
        $query = "DELETE FROM {{%user_uploads}} "
            . $where
            . " RETURNING user_id, node_id, upload_id, upload_saved_name";
        $res = Yii::$app->db
            ->createCommand($query, $params)
            ->queryAll();
        //var_dump($res); exit;
        if (is_array($res)) {
            /** @var \yii\redis\Connection $redis */
            $redis = Yii::$app->redis;
            foreach ($res as $v) {

                try {
                    $redis->publish("user:" . $v['user_id'] . ":upload_cancel", Json::encode($v['upload_id']));
                    $redis->save();
                } catch (Exception $e) {
                    RedisSafe::createNewRecord(
                        RedisSafe::TYPE_UPLOAD_EVENTS,
                        $v['user_id'],
                        null,
                        Json::encode([
                            'action'           => 'upload_cancel',
                            'chanel'           => "user:" . $v['user_id'] . ":upload_cancel",
                            'user_id'          => $v['user_id'],
                            'upload_id'        => $v['upload_id'],
                        ])
                    );
                }

                @unlink(Yii::$app->params['userUploadsDir'] . DIRECTORY_SEPARATOR . $v['upload_saved_name']);
            }
            return sizeof($res);
        }
        return null;
    }
}
