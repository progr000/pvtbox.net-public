<?php

namespace frontend\models\forms;

use Yii;
use yii\base\Model;
//use yii\web\UploadedFile;
use common\helpers\FileSys;

/**
 * UploadFilesForm is the model behind the contact form.
 *
 * @property file $uploadedFile
 * @property integer $target_folder_id
 */
class UploadLogsForm extends Model
{
    public $uploadedFile;
    private $res_file_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        //'checkExtensionByMimeType' => false,
        return [
            [['uploadedFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'txt, log, zip, rar, gzip, tar, 7z, tgz, gz'],
        ];
    }

    /**
     * @return bool
     */
    public function upload()
    {
        if ($this->validate()) {
            if (file_exists($this->uploadedFile->tempName)) {
                $this->res_file_name = md5_file($this->uploadedFile->tempName) . "." . $this->uploadedFile->extension;
                if (!file_exists(Yii::$app->params['logUploadsDir'])) {
                    FileSys::mkdir(Yii::$app->params['logUploadsDir']);
                }
                if ($this->uploadedFile->saveAs(Yii::$app->params['logUploadsDir'] . DIRECTORY_SEPARATOR . $this->res_file_name)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getResFileName()
    {
        return $this->res_file_name;
    }
}
