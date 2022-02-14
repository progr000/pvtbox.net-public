<?php

namespace frontend\models\forms;

use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Json;
use common\models\UserFiles;
use common\models\Users;
use common\models\UserUploads;
use common\helpers\FileSys;
use common\models\RedisSafe;

/**
 * UploadFilesForm is the model behind the contact form.
 *
 * @property file $uploadedFile
 * @property integer $target_folder_id
 */
class UploadFilesForm extends Model
{
    public $uploadedFile;
    public $target_folder_id;

    protected $redis;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['uploadedFile', 'required'],
            ['uploadedFile', 'file'],
            ['target_folder_id', 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uploadedFile' => 'File',
        ];
    }


    public function processUpload()
    {
        $this->redis = Yii::$app->redis;
        $user_id = Yii::$app->user->identity->getId();

        $relativePath = "";
        if ($this->target_folder_id > 0) {
            $folder_id = $this->target_folder_id;
        } else {
            $folder_id = Yii::$app->session->get('current_id_dir', null);
        }
        if ($folder_id) {
            $UserFile = UserFiles::findOne(['file_id' => $folder_id, 'user_id' => $user_id]);
            if ($UserFile) {
                $relativePath = UserFiles::getFullPath($UserFile) . DIRECTORY_SEPARATOR;
            }
        }

        //$relativePath = UserFiles::getFullPath($UserFile);
        $User = Users::getPathNodeFS($user_id);
        $folderPath = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
        $ext = ($this->uploadedFile->extension) ? '.' . $this->uploadedFile->extension : '';
        $fileName = $this->uploadedFile->baseName . $ext;
        $filePath = $folderPath . $fileName;

        /* Проверка на зарезервированные имена файлов */
        $ret = UserFiles::checkSystemReservedFilename($fileName);
        if (isset($ret['error'])) {
            return [
                'files'  => [[
                    'name' => $fileName,
                    'size' => $this->uploadedFile->size,
                ]],
                'status' => false,
                'info'   => $ret['error'][0],
            ];
        }

        /* Проверка что такого файла нет в текущем каталоге */
        if (file_exists($filePath)) {
            $i = 1;
            do {
                $fileName = $this->uploadedFile->baseName . "($i)" . $ext;
                $filePath = $folderPath . $fileName;
                $i++;
            } while (file_exists($filePath));
        }
        /* Проверка что такого файла нет в текущем каталоге */
        /*
        if (file_exists($filePath)) {
            return [
                'files'  => [[
                    'name' => $fileName,
                    'size' => $this->uploadedFile->size,
                ]],
                'status' => false,
                'info'   => "File already exist.",
            ];
        }
        */

        /* Проверка что папка владелец существует и синхронизирована */
        $parent_dirInfoFile = dirname($filePath) . '/' . UserFiles::DIR_INFO_FILE;
        if (!file_exists($parent_dirInfoFile)) {
            return [
                'files'  => [[
                    'name' => $fileName,
                    'size' => $this->uploadedFile->size,
                ]],
                'status' => false,
                'info'   => "Sync file error. parent_folder_uuid does not exist.",
            ];
        }

        /* Проверка что папка владелец существует и синхронизирована */
        try {
            if (file_exists($parent_dirInfoFile)) {
                $tmp1 = @unserialize(file_get_contents($parent_dirInfoFile));
                if (!isset($tmp1['file_uuid'])) {
                    return [
                        'files' => [[
                            'name' => $fileName,
                            'size' => $this->uploadedFile->size,
                        ]],
                        'status' => false,
                        'info' => "Sync file error. parent_folder_uuid does not exist.",
                    ];
                }
            }
        } catch (\Exception $e) {
            return [
                'files' => [[
                    'name' => $fileName,
                    'size' => $this->uploadedFile->size,
                ]],
                'status' => false,
                'info' => "Sync file error. parent_folder_uuid does not exist.",
            ];
        }

        /* Проверка что папка не была удалена */
        if (isset($tmp1['file_deleted']) && $tmp1['file_deleted']) {
            return [
                'files' => [[
                    'name' => $fileName,
                    'size' => $this->uploadedFile->size,
                ]],
                'status' => false,
                'info' => "Sync file error. Parent folder is deleted.",
            ];
        }

        /* Если все прошло успешно то генерируем уникальное имя
         * для этого файла который будет храниться оригинал этого файла
         * а файл пустышка будет создан в каталоге ФМ */
        $upload_saved_name = md5($filePath);

        /* Если нет папки для аплоада пытаемся ее создать */
        if (!file_exists(Yii::$app->params['userUploadsDir'])) {
            FileSys::mkdir(Yii::$app->params['userUploadsDir'], UserFiles::CHMOD_DIR);
        }

        /* Записываем файл */
        $saved_file = Yii::$app->params['userUploadsDir'] . DIRECTORY_SEPARATOR . $upload_saved_name;
        if ($this->uploadedFile->saveAs($saved_file)) {

            FileSys::touch($filePath, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);

            /* Проверка что файл не был загружен ранее */
            $upload_path = UserUploads::normalizePath($User->_full_path, $filePath);
            if (UserUploads::findOne(['user_id' => $user_id, 'upload_path' => $upload_path])) {
                @unlink($saved_file);
                return [
                    'files'  => [[
                        'name' => $fileName,
                        'size' => $this->uploadedFile->size,
                    ]],
                    'status' => false,
                    'info'   => "File already uploaded and wait for sync. Can't upload file with same name.",
                ];
            }

            $UserUploads = new UserUploads();
            $UserUploads->user_id = $user_id;
            $UserUploads->upload_path = $upload_path;
            $UserUploads->upload_saved_name = $upload_saved_name;
            $UserUploads->upload_size = $this->uploadedFile->size;
            $UserUploads->upload_md5 = md5_file($saved_file);
            $UserUploads->file_parent_id = (isset($tmp1['file_id'])) ? $tmp1['file_id'] : UserFiles::ROOT_PARENT_ID;
            $UserUploads->node_id = 0;
            if ($UserUploads->save()) {
                $event_data[] = [
                    'operation' => "upload_add",
                    'data'      => [
                        //'user_id'       => $UserUploads->user_id,
                        'upload_id'     => $UserUploads->upload_id,
                        'upload_md5'    => $UserUploads->upload_md5,
                        'upload_size'   => $UserUploads->upload_size,
                        'upload_path'   => $UserUploads->upload_path,
                        'upload_name'   => $fileName,
                        'folder_uuid'   => (isset($tmp1['file_id'], $tmp1['file_uuid'])) ? $tmp1['file_uuid'] : null,
                    ]
                ];

                //FileSys::touch($filePath, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
                FileSys::fwrite($filePath, serialize([
                    'uploaded' => $event_data[0]['data'],
                ]), UserFiles::CHMOD_FILE);

                /* Отправка евента на редис */
                try {
                    $this->redis->publish("user:{$user_id}:uploads", Json::encode($event_data));
                    $this->redis->save();
                } catch (\Exception $e) {
                    RedisSafe::createNewRecord(
                        RedisSafe::TYPE_UPLOAD_EVENTS,
                        $user_id,
                        null,
                        Json::encode([
                            'action'           => 'uploads',
                            'chanel'           => "user:{$user_id}:uploads",
                            'user_id'          => $user_id,
                            'upload_id'        => $UserUploads->upload_id,
                        ])
                    );
                }


                return [
                    'files' => [[
                        'name' => $fileName,
                        'size' => $this->uploadedFile->size,
                        //"url" => $path,
                        //"thumbnailUrl" => $path,
                        //"deleteUrl" => 'delete-file?node_id=' . $node_id . '&name=' . $UserNode->_relative_path . '/' . $fileName,
                        //"deleteType" => "POST"
                    ]],
                    'status' => true,
                    'info'   => 'Success.',
                    //'event_data' => $event_data,
                ];
            } else {
                @unlink($filePath);
                return [
                    'files'  => [[
                        'name' => $fileName,
                        'size' => $this->uploadedFile->size,
                    ]],
                    'status' => false,
                    'info'   => $UserUploads->getErrors(),
                ];
            }
        }

        return [
            'files'  => [[
                'name' => $fileName,
                'size' => $this->uploadedFile->size,
            ]],
            'status' => false,
            'info'   => "Save file error.",
        ];
    }
}
