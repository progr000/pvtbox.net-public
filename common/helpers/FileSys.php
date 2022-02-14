<?php

namespace common\helpers;

use Yii;

/**
 * This is the static Functions.
 *
 */
class FileSys
{
    protected static $mimetypes = array(
        // applications
        'ai'    => 'application/postscript',
        'eps'   => 'application/postscript',
        'exe'   => 'application/x-executable',
        'doc'   => 'application/msword',
        'dot'   => 'application/msword',
        'xls'   => 'application/vnd.ms-excel',
        'xlt'   => 'application/vnd.ms-excel',
        'xla'   => 'application/vnd.ms-excel',
        'ppt'   => 'application/vnd.ms-powerpoint',
        'pps'   => 'application/vnd.ms-powerpoint',
        'pdf'   => 'application/pdf',
        'xml'   => 'application/xml',
        'swf'   => 'application/x-shockwave-flash',
        'torrent' => 'application/x-bittorrent',
        'jar'   => 'application/x-jar',
        // open office (finfo detect as application/zip)
        'odt'   => 'application/vnd.oasis.opendocument.text',
        'ott'   => 'application/vnd.oasis.opendocument.text-template',
        'oth'   => 'application/vnd.oasis.opendocument.text-web',
        'odm'   => 'application/vnd.oasis.opendocument.text-master',
        'odg'   => 'application/vnd.oasis.opendocument.graphics',
        'otg'   => 'application/vnd.oasis.opendocument.graphics-template',
        'odp'   => 'application/vnd.oasis.opendocument.presentation',
        'otp'   => 'application/vnd.oasis.opendocument.presentation-template',
        'ods'   => 'application/vnd.oasis.opendocument.spreadsheet',
        'ots'   => 'application/vnd.oasis.opendocument.spreadsheet-template',
        'odc'   => 'application/vnd.oasis.opendocument.chart',
        'odf'   => 'application/vnd.oasis.opendocument.formula',
        'odb'   => 'application/vnd.oasis.opendocument.database',
        'odi'   => 'application/vnd.oasis.opendocument.image',
        'oxt'   => 'application/vnd.openofficeorg.extension',
        // MS office 2007 (finfo detect as application/zip)
        'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'docm'  => 'application/vnd.ms-word.document.macroEnabled.12',
        'dotx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'dotm'  => 'application/vnd.ms-word.template.macroEnabled.12',
        'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xlsm'  => 'application/vnd.ms-excel.sheet.macroEnabled.12',
        'xltx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'xltm'  => 'application/vnd.ms-excel.template.macroEnabled.12',
        'xlsb'  => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
        'xlam'  => 'application/vnd.ms-excel.addin.macroEnabled.12',
        'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'pptm'  => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
        'ppsx'  => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'ppsm'  => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
        'potx'  => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'potm'  => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
        'ppam'  => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
        'sldx'  => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
        'sldm'  => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
        // archives
        'gz'    => 'application/x-gzip',
        'tgz'   => 'application/x-gzip',
        'bz'    => 'application/x-bzip2',
        'bz2'   => 'application/x-bzip2',
        'tbz'   => 'application/x-bzip2',
        'xz'    => 'application/x-xz',
        'zip'   => 'application/zip',
        'rar'   => 'application/x-rar',
        'tar'   => 'application/x-tar',
        '7z'    => 'application/x-7z-compressed',
        // texts
        'txt'   => 'text/plain',
        'php'   => 'text/x-php',
        'html'  => 'text/html',
        'htm'   => 'text/html',
        'js'    => 'text/javascript',
        'css'   => 'text/css',
        'rtf'   => 'text/rtf',
        'rtfd'  => 'text/rtfd',
        'py'    => 'text/x-python',
        'java'  => 'text/x-java-source',
        'rb'    => 'text/x-ruby',
        'sh'    => 'text/x-shellscript',
        'pl'    => 'text/x-perl',
        //'xml'   => 'text/xml',
        'sql'   => 'text/x-sql',
        'c'     => 'text/x-csrc',
        'h'     => 'text/x-chdr',
        'cpp'   => 'text/x-c++src',
        'hh'    => 'text/x-c++hdr',
        'log'   => 'text/plain',
        'csv'   => 'text/csv',
        'md'    => 'text/x-markdown',
        'markdown' => 'text/x-markdown',
        // images
        'bmp'   => 'image/x-ms-bmp',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'png'   => 'image/png',
        'tif'   => 'image/tiff',
        'tiff'  => 'image/tiff',
        'tga'   => 'image/x-targa',
        'psd'   => 'image/vnd.adobe.photoshop',
        //'ai'    => 'image/vnd.adobe.photoshop',
        'xbm'   => 'image/xbm',
        'pxm'   => 'image/pxm',
        //audio
        'mp3'   => 'audio/mpeg',
        'mid'   => 'audio/midi',
        'ogg'   => 'audio/ogg',
        'oga'   => 'audio/ogg',
        'm4a'   => 'audio/mp4',
        'wav'   => 'audio/wav',
        'wma'   => 'audio/x-ms-wma',
        // video
        'avi'   => 'video/x-msvideo',
        'dv'    => 'video/x-dv',
        'mp4'   => 'video/mp4',
        'mpeg'  => 'video/mpeg',
        'mpg'   => 'video/mpeg',
        'mov'   => 'video/quicktime',
        'wm'    => 'video/x-ms-wmv',
        'flv'   => 'video/x-flv',
        'mkv'   => 'video/x-matroska',
        'webm'  => 'video/webm',
        'ogv'   => 'video/ogg',
        'ogm'   => 'video/ogg'
    );

    const CHMOD_DIR  = 0775;
    const CHMOD_FILE = 0664;
    const MAX_FILENAME_LENGTH = 50;

    /**
     * @param string $file_name
     * @return string
     */
    public static function file_mime($file_name)
    {
        $file_name = mb_strtolower($file_name);
        $ext = (false === $pos = strrpos($file_name, '.')) ? '' : substr($file_name, $pos + 1);
        if ($ext) {
            if (isset(self::$mimetypes[$ext])) return self::$mimetypes[$ext];
        }
        return "application/octet-stream";
    }

    /**
     * @param string $filename
     * @param int|null $max_len
     * @param int|null $delta
     * @return string
     */
    public static function formatFileName($filename, $max_len=null, $delta=null)
    {
        if (!$max_len) {
            $max_len = self::MAX_FILENAME_LENGTH;
        }
        if (!$delta) {
            $delta = 15;
        }
        $cur_len = mb_strlen($filename);
        if ($cur_len > $max_len) {
            $filename = mb_substr($filename, 0, $max_len - $delta) . " ... " . mb_substr($filename, $cur_len - $delta);
        }
        return $filename;

    }

    /**
     * @param string $dir
     * @param integer $mode
     * @param bool $recursive
     * @return bool
     */
    public static function mkdir($dir, $mode=self::CHMOD_DIR, $recursive=true)
    {
        return @mkdir($dir, $mode, $recursive);
    }

    /**
     * @param string $file
     * @param integer $dmode
     * @param integer $fmode
     * @return bool
     */
    public static function touch($file, $dmode=self::CHMOD_DIR, $fmode=self::CHMOD_FILE)
    {
        $dir = dirname($file);
        if (!file_exists($dir)) {
            self::mkdir($dir, $dmode, true);
        }
        return (touch($file) && chmod($file, $fmode));
    }

    /**
     * @param string $file
     * @param string $data
     * @param int $fmode
     * @param string $mode
     * @return bool
     */
    public static function fwrite($file, $data, $fmode=self::CHMOD_FILE, $mode='w')
    {
        $f = fopen($file, $mode);
        fwrite($f, $data);
        fflush($f);
        fclose($f);

        return chmod($file, $fmode);
    }

    /**
     * @param string $path
     * @param bool $recursive
     * @return bool
     */
    public static function remove($path, $recursive=false)
    {
        if (is_dir($path) && !is_link($path)) {
            return self::rmdir($path, $recursive);
        } else {
            return @unlink($path);
        }
    }

    /**
     * @param string $src
     * @param string $dst
     * @return bool
     */
    public static function move($src, $dst)
    {
        return rename($src, $dst);
    }

    /**
     * Позволяет удалить не пустую дирректорию рекурсивно
     *
     * @param string $dir
     * @param bool $recursive
     * @return bool
     */
    public static function rmdir($dir, $recursive=false)
    {
        if ($recursive) {
            //@chmod($dir, self::CHMOD_DIR);
            if ($h = @opendir($dir)) {
                while (false !== ($f = readdir($h))) {

                    if (($f === '.') || ($f === '..'))
                        continue;

                    $path = $dir.'/'.$f;
                    if (is_dir($path) && !is_link($path)) {
                        self::rmdir($path, true);
                    } else {
                        //@chmod($path, self::CHMOD_FILE);
                        @unlink($path);
                    }
                }
                closedir($h);
            }
            return @rmdir($dir);
        } else {
            return @rmdir($dir);
        }
    }

    /**
     * Сканирует дирректорию и создает массив элементов
     * (директорий, файлов, и рекурсивно субдиректорий если нужно)
     * Результат заисит от параметра $param
     * $param = 0 - возврат списка файла в указанной директории
     * $param = 1 - список файлов и субдиректорий в указанной директории без рекурсии
     * $param = 2 - список файлов и субдиректорий в указанной директории рекурсивно
     * $param = 3 - список только субдиректорий в укзанной директории без рекурсии
     * $param = 4 - список только субдиректорий в укзанной директории рекурсивно
     * формат возвращаемого массива
     * []['name'=>'Имя директории или файла', 'path'=>'полный путь к этому элементу','is_dir'=>bool, subdir=>Array(RECURSION)|false];
     *
     * @param string $dir
     * @param integer $param
     * @return array|null
     */
    public static function getFileList($dir, $param=0)
    {
        $dpath = [];
        $fpath = [];
        if ($h = @opendir($dir)) {
            while (false !== ($f = readdir($h))) {

                if (($f === '.') || ($f === '..'))
                    continue;

                $path = $dir.'/'.$f;
                $name = $f;
                if (!is_dir($path)) {
                    if ($param < 3)
                        $fpath[] = ['name'=>$name, 'size'=>filesize($path), 'path'=>$path, 'ctime'=>filectime($path), 'is_dir'=>false, 'subdir'=>false];
                } elseif ($param > 0) {
                    if (in_array($param, [1,3]))
                        $dpath[] = ['name'=>$name, 'size'=>0, 'path'=>$path, 'ctime'=>filectime($path), 'is_dir'=>true,  'subdir'=>false];
                    elseif (in_array($param, [2,4]))
                        $dpath[] = ['name'=>$name, 'size'=>0, 'path'=>$path, 'ctime'=>filectime($path), 'is_dir'=>true,  'subdir'=>self::getFileList($path, $param)];
                }
            }
            closedir($h);
            return array_merge($dpath, $fpath);
        } else { return null; }
    }

    /**
     * @param string $dir
     * @return array
     */
    public static function recalcDir($dir)
    {
        $totalcntf = 0;
        $totalcntd = 0;
        $cntf = 0;
        $cntd = 0;
        $sizd = 0;
        if ($h = @opendir($dir)) {
            while (false !== ($f = readdir($h))) {

                if (($f === '.') || ($f === '..') || ($f === '.dirinfo'))
                    continue;

                $path = $dir.'/'.$f;
                if (is_dir($path)) {
                    $cntd++;
                    $totalcntd++;
                    $res = self::recalcDir($path);
                    $totalcntd = $totalcntd + $res['totalcntd'];
                    $totalcntf = $totalcntf + $res['totalcntf'];
                    $sizd = $sizd + $res['sizd'];
                } else {
                    $cntf++;
                    $totalcntf++;
                    $finfo = self::getVirtualFileInfo($path);
                    $sizd = $sizd + $finfo['size'];
                }

            }
            self::fwrite($dir. '/.dirinfo', serialize([
                'total_count_dirs' => $totalcntd,
                'total_count_files' => $totalcntf,
                'count_dirs' => $cntd,
                'count_files' => $cntf,
                'size' => $sizd
            ]) ,0666);
            closedir($h);
        }
        return [
            'totalcntd' => $totalcntd,
            'totalcntf' => $totalcntf,
            'cntf' => $cntf,
            'cntd' => $cntd,
            'sizd' => $sizd
        ];
    }

    /**
     * @param string $fpath
     * @return array
     */
    public static function getVirtualFileInfo($fpath)
    {
        /*
        $fname = basename($fpath);
        $tmp = explode("_", $fname);
        $size  = isset($tmp[0]) ? intval($tmp[0]) : 0;
        $atime = isset($tmp[1]) ? intval($tmp[1]) : 0;
        unset($tmp[0], $tmp[1]);
        $name  = isset($tmp[2]) ? implode('_', $tmp) : $fname;

        return ['name' => $name, 'size' => $size, 'atime' => $atime];
        */
        $file = @unserialize(file_get_contents($fpath));
        if (is_array($file)) {
            return $file;
        } else {
            return ['name' => self::basename($fpath), 'size' => 0, 'atime' => 0, 'is_real' => true];
        }
    }

    /**
     * @param array $filedata
     * @return string
     */
    public static function setRealFileName(array &$filedata)
    {
        if (isset($filedata['name'], $filedata['mime'])) {
            if ($filedata['mime'] !== 'directory') {
                $tmp = self::getVirtualFileInfo($filedata['name']);
                $filedata['name'] = $tmp['name'];
                //return $tmp['name'];
            }
        }
        //return false;
    }

    /**
     * @param string $path
     * @param string $separator
     * @return mixed
     */
    public static function basename($path, $separator=DIRECTORY_SEPARATOR)
    {
        $arr = explode($separator, $path);
        return array_pop($arr);
    }

    /**
     * @param string $file_path
     * @return array
     */
    public static function pathinfo($file_path)
    {
        setlocale(LC_ALL, 'en_US.UTF-8');
        //rawurldecode(dirname(rawurlencode($file_path)));
        $ret['basename'] = basename($file_path);
        $ret['dirname']  = dirname($file_path);
        //var_dump($ret); exit;

        $pos = mb_strrpos($ret['basename'], '.');
        if ($pos !== false) {
            $ret['filename']  = mb_substr($ret['basename'], 0, $pos);
            $ret['extension'] = mb_substr($ret['basename'], $pos + 1);
        } else {
            $ret['filename'] = $ret['basename'];
        }

        return $ret;
    }
}