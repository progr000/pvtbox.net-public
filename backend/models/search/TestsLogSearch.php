<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use common\helpers\FileSys;
use common\models\QueuedEvents;
use backend\models\Jobs\StartTestsJob;

/**
 * PagesSearch represents the model behind the search form about common\models\Pages.
 *
 * @property \yii\queue\file\Queue $queue2
 *
 */
class TestsLogSearch extends Model
{
    protected $queue2;

    private $out_dir = 'tests' . DIRECTORY_SEPARATOR . '_output' . DIRECTORY_SEPARATOR;
    private $screen_dir = 'tests' . DIRECTORY_SEPARATOR . '_reports' . DIRECTORY_SEPARATOR;
    private $report_dir = 'tests' . DIRECTORY_SEPARATOR. '_reports' . DIRECTORY_SEPARATOR;
    private $report_start_script_yii = 'tests' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'pvtbox-yii-tests.sh';
    private $report_start_script_db = 'tests' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'pvtbox-db-tests.sh';
    private $report_lock_file = 'tests' . DIRECTORY_SEPARATOR . '_output' . DIRECTORY_SEPARATOR . 'tests_exec.lock';

    public $image;
    public $image_dir;
    public $report;

    public function init()
    {
        parent::init();
        $basePath = Yii::$app->getBasePath() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        $this->report_dir = realpath($basePath . $this->report_dir);
        $this->screen_dir = realpath($basePath . $this->screen_dir);
        $this->report_start_script_yii = realpath($basePath . $this->report_start_script_yii);
        $this->report_start_script_db  = realpath($basePath . $this->report_start_script_db);
        $this->report_lock_file = realpath($basePath . $this->report_lock_file);
        $this->queue2 = (isset(Yii::$app->queue2) && method_exists(Yii::$app->queue2, 'push')) ? Yii::$app->queue2 : false;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['image'], 'match', 'pattern' => "/^screen\-[a-z0-9\-]{2,50}\-screen$/i"],
            [['image_dir'], 'match', 'pattern' => "/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/"],
            [['report'], 'match', 'pattern' => "/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}\_[0-9]{6}[\_a-zA-Z]{0,20}$/i"],
        ];
    }

    /**
     * @return bool|array
     */
    public function ExecManually()
    {
        if (!$this->CheckTestInProgress()) {

            if ($this->queue2) {
                Yii::$app->cache->set('TestExecutionInProgress', true, 6000);
                /* выполняем остальную часть через очередь */
                $unique_id = md5(uniqid(rand(), true));
                $job_id = $this->queue2->push(new StartTestsJob([
                    'report_start_script_yii' => $this->report_start_script_yii,
                    'report_start_script_db'  => $this->report_start_script_db,
                    'report_lock_file'        => $this->report_lock_file,
                    'unique_id'               => $unique_id,
                ]));

                $QueuedEvent = new QueuedEvents();
                $QueuedEvent->event_uuid = $unique_id;
                $QueuedEvent->job_id     = (string) $job_id;
                //$QueuedEvent->user_id    = null;
                //$QueuedEvent->node_id    = null;
                $QueuedEvent->job_status = QueuedEvents::STATUS_WAITING;
                $QueuedEvent->job_type   = QueuedEvents::TYPE_TESTS_EXECUTION;
                $QueuedEvent->queue_id   = 'queue2';
                $QueuedEvent->save();
                return [
                    'status' => true,
                    'result' => "queued",
                    'info' => "StartTestsJob stored in queue successfully",
                    'data' => [
                        'job_id'     => $QueuedEvent->job_id,
                    ],
                ];
            } else {
                /* выполняем напрямую */
                Yii::$app->cache->set('TestExecutionInProgress', true, 6000);
                $out = shell_exec($this->report_start_script_yii);
                sleep(5);
                $out = shell_exec($this->report_start_script_db);
                Yii::$app->cache->delete('TestExecutionInProgress');
                //var_dump($out); exit;
                return [
                    'status' => true,
                    'result' => 'ok',
                    'output' => $out,
                ];
            }

        } else {
            return [
                'status' => false,
                'info'   => 'Tests are already in progress. Wait until its finished',
            ];
        }
    }

    /**
     * @return bool
     */
    public function CheckTestInProgress()
    {
        $TestExecutionInProgress = Yii::$app->cache->get('TestExecutionInProgress');
        if ($TestExecutionInProgress) {
            return true;
        }

        if (file_exists($this->report_lock_file)) {
            return true;
        }

        return false;
    }

    /**
     * @return ArrayDataProvider
     */
    public function listReports()
    {
        $res = FileSys::getFileList($this->report_dir, 0);
        if (is_array($res)) {
            foreach ($res as $k => $v) {
                if (mb_strrpos($v['name'], '.html') === false || $v['size'] == 0) {
                    unset($res[$k]);
                } else {
                    $res[$k]['type'] = 'yii';
                    if (mb_strrpos($v['name'], '_db') !== false) {
                        $res[$k]['type'] = 'db';
                    }
                    $res[$k]['idx'] = str_replace('.html', '', $v['name']);
                    $res[$k]['date'] = date(SQL_DATE_FORMAT, $v['ctime']);
                }
            }
        }
        //var_dump($res); exit;

        $dataProvider = new ArrayDataProvider([
            //'key' => 'id',
            'allModels' => $res,

            'sort' => [
                'attributes' => [
                    'ctime',
                    'date',
                    'idx',
                    'type',
                ],
                'defaultOrder' => [
                    'ctime' => SORT_DESC
                ]
            ],

            'pagination' => [
                'pageSize' => 10,
                //'route'=>'tikets/index',
            ],
        ]);

        return $dataProvider;
    }

    /**
     * @return string
     */
    public function getReport()
    {
        $report_path = $this->report_dir . DIRECTORY_SEPARATOR . $this->report . ".html";
        if (file_exists($report_path)) {
            $content = file_get_contents($report_path);
            //preg_match_all("/screen\-[a-z0-9]*\-screen/siU", $content, $ma);
            //var_dump($ma); exit;
            $content = preg_replace("/\.\.\/\.\.\/\.\.\/\.\.\/tests\/\_reports\/([0-9]{4}\-[0-9]{2}\-[0-9]{2})\/(screen\-[a-z0-9\-]*\-screen)/siU", "<a href=\"/site/view-tests-image?image=$2&image_dir=$1\" target=\"_blank\">$2</a>", $content);
            $content = trim(str_replace("<!DOCTYPE html>", "", $content));

            return [
                'status'  => true,
                'content' => $content,
            ];
        } else {
            return [
                'status'  => false,
                'content' => 'File not found',
            ];
        }
    }

    /**
     * @return string
     */
    public function getImage()
    {
        $image_path = $this->screen_dir . DIRECTORY_SEPARATOR . $this->image_dir . DIRECTORY_SEPARATOR . $this->image . ".png";
        //var_dump($image_path);exit;
        if (file_exists($image_path)) {
            return [
                'status' => true,
                'path'   => $image_path,
            ];
        } else {
            return [
                'status' => false,
                'info'   => "Image not found",
            ];
        }
    }
}
