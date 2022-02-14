<?php

namespace common\helpers;

use Yii;
//use yii\helpers\ArrayHelper;
//use common\helpers\JSMin;

/**
 * This is the static Functions.
 *
 */
class Functions
{
    const IS_DEBUG = true;
    /**
     * @param $str
     */
    public static function debugEcho($str)
    {
        if (self::IS_DEBUG) {
            echo $str;
        }
    }

    private static $start_time;
    /**
     * @return float
     */
    private static function get_microtime()
    {
        list($usec, $sec) = explode(" ",microtime());
        return (doubleval($usec) + doubleval($sec));
    }

    public static function start_timer()
    {
        self::$start_time = self::get_microtime();
    }

    /**
     * @return float
     */
    public static function get_execute_time()
    {
        return (self::get_microtime() - self::$start_time);
    }

    /**
     * prepared queryString
     * @param string $simbol - first simbol before queryString  & | ?
     * @param array $exclude - name of params of queryString wich were excluded from her
     * @return string - queryString
     */
    public static function prepareQS($exclude = [], $simbol = '&')
    {
        if (!in_array($simbol, ['&', '?']))
            $simbol = '&';

        $parsms = Yii::$app->request->queryParams;
        foreach ($exclude as $v)
            unset($parsms[$v]);

        if (sizeof($parsms) > 0)
            return $simbol.http_build_query($parsms);
        else
            return "";
    }

    /**
     * @param array $color_name_array  it's array like this Users::statusParams(); [1=>['color'=>'any_color', 'name'=>'any_name'], 2=>[]...]
     * @return string
     */
    public static function getLegend($color_name_array)
    {
        $str = "<b>Interpretation of statuses:&nbsp;</b>";
        foreach ($color_name_array as $k=>$v) {
            $str .= '<span class="badge" style="background-color: '.$v['color'].'">&nbsp;</span> '.$v['name'].';&nbsp;&nbsp;';
        }
        return $str;
    }

    /**
     * Function returns array of dates for search
     *
     * @return array
     */
    public static function dateInfo()
    {
        $d['today_begin'] = date('Y-m-d', time())." 00:00:00";
        $d['today_end']   = date('Y-m-d', time())." 23:59:59";

        $day_week = intval(date('N', time())); // 1-пн, 2-вт ... 7-вс
        $time_first_day_week = ($day_week>1) ? time() - ($day_week - 1)*86400 : time();
        $d['week_begin']  = date('Y-m-d', $time_first_day_week) ." 00:00:00";
        $d['week_end']    = date('Y-m-d', time())." 23:59:59";

        $day_month = intval(date('j', time()));
        $time_first_day_mnth = ($day_month>1) ? time() - ($day_month - 1)*86400 : time();
        $d['mnth_begin']  = date('Y-m-d', $time_first_day_mnth) ." 00:00:00";
        $d['mnth_end']    = date('Y-m-d', time())." 23:59:59";

        return $d;
    }

    /**
     * @param $code_period
     * @return string
     */
    public static function forPeriod($code_period)
    {
        switch ($code_period) {
            case 'day':
                return "Per day: ";
                break;
            case 'week':
                return "Per week: ";
                break;
            case 'mnth':
                return "Per month: ";
                break;
            case 'now':
                return "Now: ";
                break;
            case 'for24hours':
                return "Per 24 hours: ";
                break;
            default:
                return $code_period;
                break;
        }
    }

    public static function HttpGet($url, $user=null, $passwd=null, $timeout=30, $headers=[])
    {
        if (sizeof($headers) == 0) { $headers = ["Accept-Language: en"]; }
        $ch = curl_init();    // initialize curl handle
        curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // times out after 40s
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch, CURLOPT_HEADER, true);
        //curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $str); // add POST fields
        if ($user && $passwd) {
            curl_setopt($ch, CURLOPT_USERPWD, "{$user}:{$passwd}");
        }
        $answer = curl_exec($ch);// run the whole process
        curl_close($ch);

        return $answer;
    }

    public static function getBrowserByUserAgent($ua)
    {
        $ua = mb_strtolower($ua);
        if (strrpos($ua, 'firefox')) {
            return 'firefox';
        }
        if (strrpos($ua, 'opera')) {
            return 'opera';
        }
        if (strrpos($ua, 'netscape')) {
            return 'netscape';
        }
        if (strrpos($ua, 'chrome')) {
            return 'chrome';
        }
        if (strrpos($ua, 'safari')) {
            return 'safari';
        }
        if (strrpos($ua, 'msie') || strrpos($ua, "trident") || strrpos($ua, "rv:11")) {
            return 'msie';
        }
        return null;
    }

    public static function getOsTypeByUserAgent($ua)
    {
        $ua = mb_strtolower($ua);
        if (strrpos($ua, 'linux')) {
            return 'Linux';
        }
        if (strrpos($ua, 'android')) {
            return 'Android';
        }
        if (strrpos($ua, 'ios') || strrpos($ua, 'mac')) {
            return 'MacOS';
        }
        if (strrpos($ua, 'windows') || strrpos($ua, 'msie') || strrpos($ua, "trident") || strrpos($ua, "rv:11")) {
            return 'Windows';
        }
        return 'unknown';
    }

    /**
     * @param integer $bytes
     * @param integer $decimal_digits
     * @param string $force  ('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb')
     * @param string $space_between
     * @param bool $no_power
     * @return string
     */
    //public static function file_size_format($bytes, $format = '', $force = '')
    public static function file_size_format($bytes, $decimal_digits = 2, $force = '', $space_between = ' ', $no_power=false)
    {
        $defaultFormat = '%s%s';
        //if (strlen($format) == 0) { $format = $defaultFormat; }
        $bytes = max(0, round($bytes));
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $power = array_search($force, $units);
        if ($power === false) { $power = ($bytes > 0) ? floor(log($bytes, 1024)) : 0; }
        //return sprintf($format, $bytes / pow(1024, $power), $units[$power]);
        //return sprintf($format, round($bytes / pow(1024, $power), 2), $units[$power]);
        if ($no_power) {
            return '' . number_format(round($bytes / pow(1024, $power), 2), $decimal_digits, '.', '');
        } else {
            return '' . number_format(round($bytes / pow(1024, $power), 2), $decimal_digits, '.', '') . $space_between . $units[$power];
        }
    }

    /**
     * return bool
     */
    public static function isIE()
    {
        $ua = \Yii::$app->request->useragent;
        if (strrpos($ua, 'msie') || strrpos($ua, "trident") || strrpos($ua, "rv:11")) {
            return true;
        }
        return false;
    }

    public static function getOsExtendedByUserAgent($ua)
    {
        $ua = mb_strtolower($ua);
        //var_dump(preg_match("/(Linux|X11)/i", $ua)); exit;
        if (preg_match("/Windows/i", $ua)) {
            if (preg_match("/(Windows 10.0|Windows NT 10.0)/i", $ua)) {
                return 'Windows 10';
            }
            if (preg_match("/(Windows 8.1|Windows NT 6.3)/i", $ua)) {
                return 'Windows 8.1';
            }
            if (preg_match("/(Windows 8|Windows NT 6.2)/i", $ua)) {
                return 'Windows 8';
            }
            if (preg_match("/(Windows 7|Windows NT 6.1)/i", $ua)) {
                return 'Windows 7';
            }
            if (preg_match("/Windows NT 6.0/i", $ua)) {
                return 'Windows Vista';
            }
            if (preg_match("/Windows NT 5.2/i", $ua)) {
                return 'Windows Server 2003';
            }
            if (preg_match("/(Windows NT 5.1|Windows XP)/i", $ua)) {
                return 'Windows XP';
            }
            if (preg_match("/(Windows NT 5.0|Windows 2000)/i", $ua)) {
                return 'Windows 2000';
            }
            if (preg_match("/(Win 9x 4.90|Windows ME)/i", $ua)) {
                return 'Windows ME';
            }
            if (preg_match("/(Windows 98|Win98)/i", $ua)) {
                return 'Windows 98';
            }
            if (preg_match("/(Windows 95|Win95|Windows_95)/i", $ua)) {
                return 'Windows 95';
            }
            if (preg_match("/(Windows NT 4.0|WinNT4.0|WinNT|Windows NT)/i", $ua)) {
                return 'Windows NT 4.0';
            }
            if (preg_match("/Windows CE/i", $ua)) {
                return 'Windows CE';
            }
            if (preg_match("/Win16/i", $ua)) {
                return 'Windows 3.11';
            }
            return 'Windows';
        }
        if (preg_match("/Android/i", $ua)) {
            return 'Android';
        }
        if (preg_match("/OpenBSD/i", $ua)) {
            return 'Open BSD';
        }
        if (preg_match("/SunOS/i", $ua)) {
            return 'Sun OS';
        }
        if (preg_match("/(Linux|X11)/i", $ua)) {
            if (preg_match("/Kubuntu/i", $ua)) {
                return 'Kubuntu';
            }
            if (preg_match("/Ubuntu/i", $ua)) {
                return 'Ubuntu';
            }
            if (preg_match("/Debian/i", $ua)) {
                return 'Debian';
            }
            if (preg_match("/Red/i", $ua)) {
                return 'Red Hat';
            }
            if (preg_match("/Cent/i", $ua)) {
                return 'Linux CentOS';
            }
            if (preg_match("/Mint/i", $ua)) {
                return 'Linux Mint';
            }
            if (preg_match("/SUSE/i", $ua)) {
                return 'openSUSE';
            }
            if (preg_match("/Fedora/i", $ua)) {
                return 'Fedora';
            }
            return 'Linux';
        }
        if (preg_match("/(iPhone|iPad|iPod)/i", $ua)) {
            return 'iOS';
        }
        if (preg_match("/Mac OS X/i", $ua)) {
            return 'Mac OS X';
        }
        if (preg_match("/(MacPPC|MacIntel|Mac_PowerPC|Macintosh)/i", $ua)) {
            return 'Mac OS';
        }
        if (preg_match("/QNX/i", $ua)) {
            return 'QNX';
        }
        if (preg_match("/UNIX/i", $ua)) {
            return 'UNIX';
        }
        if (preg_match("/BeOS/i", $ua)) {
            return 'BeOS';
        }
        if (preg_match("/OS\/2/i", $ua)) {
            return 'OS/2';
        }
        if (preg_match("/(nuhk|Googlebot|Yammybot|Openbot|Slurp|MSNBot|Ask Jeeves\/Teoma|ia_archiver)/i", $ua)) {
            return 'Search Bot\'';
        }
        return 'unknown';
    }

    /**
     * @param string $nAgt
     * @return array
     */
    public static function clientDetection($nAgt)
    {
        $unknown = '';
        $nAgt = mb_strtolower($nAgt);

        /* init */
        $browser = $unknown;
        $browserVersion = $unknown;
        $browserMajorVersion = $unknown;
        $mobile = false;
        $os = $unknown;
        $osVersion = $unknown;


        /* detect is mobile or not */
        $mobile = preg_match("/mobile|mini|fennec|android|ipad|ipod|iphone/", $nAgt);


        /* detect browser name and version */
        // Opera
        if (($verOffset = mb_strpos($nAgt, 'opera')) !== false) {
            $browser = 'Opera';
            $browserVersion = mb_substr($nAgt, $verOffset + 6);
            if (($verOffset = mb_strpos($nAgt, 'version')) !== false) {
                $browserVersion = mb_substr($nAgt, $verOffset + 8);
            }
        }
        // MSIE
        elseif (($verOffset = mb_strpos($nAgt, 'msie')) !== false) {
            $browser = 'Microsoft Internet Explorer';
            $browserVersion = mb_substr($nAgt, $verOffset + 5);
        }
        // Chrome
        elseif (($verOffset = mb_strpos($nAgt, 'chrome')) !== false) {
            $browser = 'Chrome';
            $browserVersion = mb_substr($nAgt, $verOffset + 7);
        }
        // Safari
        elseif (($verOffset = mb_strpos($nAgt, 'safari')) !== false) {
            $browser = 'Safari';
            $browserVersion = mb_substr($nAgt, $verOffset + 7);
            if (($verOffset = mb_strpos($nAgt, 'version')) !== false) {
                $browserVersion = mb_substr($nAgt, $verOffset + 8);
            }
        }
        // Firefox
        elseif (($verOffset = mb_strpos($nAgt, 'firefox')) !== false) {
            $browser = 'Firefox';
            $browserVersion = mb_substr($nAgt, $verOffset + 8);
        }
        // MSIE 11+
        elseif (($verOffset = mb_strpos($nAgt, 'trident/')) !== false) {
            $browser = 'Microsoft Internet Explorer';
            $browserVersion = mb_substr($nAgt, mb_strrpos($nAgt, 'rv:') + 3);
        }
        // Other browsers
        elseif (($nameOffset = mb_strrpos($nAgt, ' ') + 1) < ($verOffset = mb_strrpos($nAgt, '/'))) {
            $len = mb_strpos($nAgt, '/', $nameOffset) - $nameOffset;
            $browser = mb_substr($nAgt, $nameOffset, $len);
            $browserVersion = mb_substr($nAgt, $verOffset + 1);
        }

        // trim the version string
        if (($ix = mb_strpos($browserVersion, ';')) !== false) $browserVersion = mb_substr($browserVersion, 0, $ix);
        if (($ix = mb_strpos($browserVersion, ' ')) !== false) $browserVersion = mb_substr($browserVersion, 0, $ix);
        if (($ix = mb_strpos($browserVersion, ')')) !== false) $browserVersion = mb_substr($browserVersion, 0, $ix);

        $browserMajorVersion = intval($browserVersion, 10);


        /* detect os and it version */

        $clientStrings = [
            ['s' => 'Windows 10', 'r' => "/(Windows 10.0|Windows NT 10.0)/"],
            ['s' => 'Windows 8.1', 'r' => "/(Windows 8.1|Windows NT 6.3)/"],
            ['s' => 'Windows 8', 'r' => "/(Windows 8|Windows NT 6.2)/"],
            ['s' => 'Windows 7', 'r' => "/(Windows 7|Windows NT 6.1)/"],
            ['s' => 'Windows Vista', 'r' => "/Windows NT 6.0/"],
            ['s' => 'Windows Server 2003', 'r' => "/Windows NT 5.2/"],
            ['s' => 'Windows XP', 'r' => "/(Windows NT 5.1|Windows XP)/"],
            ['s' => 'Windows 2000', 'r' => "/(Windows NT 5.0|Windows 2000)/"],
            ['s' => 'Windows ME', 'r' => "/(Win 9x 4.90|Windows ME)/"],
            ['s' => 'Windows 98', 'r' => "/(Windows 98|Win98)/"],
            ['s' => 'Windows 95', 'r' => "/(Windows 95|Win95|Windows_95)/"],
            ['s' => 'Windows NT 4.0', 'r' => "/(Windows NT 4.0|WinNT4.0|WinNT|Windows NT)/"],
            ['s' => 'Windows CE', 'r' => "/Windows CE/"],
            ['s' => 'Windows 3.11', 'r' => "/Win16/"],
            ['s' => 'Android', 'r' => "/Android/"],
            ['s' => 'Open BSD', 'r' => "/OpenBSD/"],
            ['s' => 'Sun OS', 'r' => "/SunOS/"],
            ['s' => 'Linux', 'r' => "/(Linux|X11)/"],
            ['s' => 'iOS', 'r' => "/(iPhone|iPad|iPod)/"],
            ['s' => 'Mac OS X', 'r' => "/Mac OS X/"],
            ['s' => 'Mac OS', 'r' => "/(MacPPC|MacIntel|Mac_PowerPC|Macintosh)/"],
            ['s' => 'QNX', 'r' => "/QNX/"],
            ['s' => 'UNIX', 'r' => "/UNIX/"],
            ['s' => 'BeOS', 'r' => "/BeOS/"],
            ['s' => 'OS/2', 'r' => "/OS\/2/"],
            ['s' => 'Search Bot', 'r' => "/(nuhk|Googlebot|Yammybot|Openbot|Slurp|MSNBot|Ask Jeeves\/Teoma|ia_archiver)/"],
        ];
        foreach ($clientStrings as $cs) {
            $cs['r'] = mb_strtolower($cs['r']);
            if (preg_match($cs['r']."i", $nAgt)) {
                $os = $cs['s'];
                break;
            }
        }


        if (preg_match("/Windows/i", $os)) {
            $osVersion = str_replace("Windows ", "", $os);
            $os = 'Windows';
        }

        //$test = preg_match("/Mac OS X ([\.\_\d]+)/i", $nAgt, $matches);
        //var_dump($matches);
        //exit;

        switch ($os) {
            case 'Mac OS X':
                $test = preg_match("/Mac OS X ([\.\_\d]+)/i", $nAgt, $matches);
                if (isset($matches[1])) $osVersion = str_replace('_', '.', $matches[1]);
                break;
            case 'Android':
                $test = preg_match("/Android ([\.\_\d]+)/i", $nAgt, $matches);
                if (isset($matches[1])) $osVersion = str_replace('_', '.', $matches[1]);
                break;
            case 'iOS':
                $test = preg_match("/OS ((\d+)\_(\d+)\_?(\d+)?)/i", $nAgt, $matches);
                if (isset($matches[1])) $osVersion = str_replace('_', '.', $matches[1]);
                break;
            case 'Linux' :
                if     (strpos($nAgt, 'kubuntu') !== false) $osVersion = 'Kubuntu';
                elseif (strpos($nAgt, 'ubuntu')  !== false) $osVersion = 'Ubuntu';
                elseif (strpos($nAgt, 'debian')  !== false) $osVersion = 'Debian';
                elseif (strpos($nAgt, 'red')     !== false) $osVersion = 'Red Hat';
                elseif (strpos($nAgt, 'cent')    !== false) $osVersion = 'CentOS';
                elseif (strpos($nAgt, 'mint')    !== false) $osVersion = 'Mint';
                elseif (strpos($nAgt, 'suse')    !== false) $osVersion = 'openSUSE';
                elseif (strpos($nAgt, 'fedora')  !== false) $osVersion = 'Fedora';
                break;
        }

        $osMajorVersion = intval($osVersion, 10);

        return [
            'mobile' => boolval($mobile),
            'browser' => [
                'name'         => $browser,
                'version'      => $browserVersion,
                'majorVersion' => $browserMajorVersion,
            ],
            'os' => [
                'name'         => $os,
                'version'      => $osVersion,
                'majorVersion' => $osMajorVersion,
            ],
        ];
    }

    /**
     * @param string $str
     * @param integer $length
     * @return string
     */
    public static function cutUtf8StrToLengthBites($str, $length)
    {
        while (mb_strlen($str, '8bit') > $length) {
            $str = mb_substr($str, 1);
        }
        return $str;
    }

    /**
     * @param string $format
     * @param string $pg_date
     * @return string
     */
    public static function formatPostgresDate($format, $pg_date)
    {
        return date($format, strtotime($pg_date));
    }

    /**
     * @param integer $unix_timestamp
     * @return int
     */
    public static function getTimestampEndOfDayByTimestamp($unix_timestamp)
    {
        return  gmmktime(date(23,  $unix_timestamp),
                         date(59,  $unix_timestamp),
                         date(59,  $unix_timestamp),
                         date("n", $unix_timestamp),
                         date("j", $unix_timestamp),
                         date("Y", $unix_timestamp));
    }

    /**
     * @param integer $unix_timestamp
     * @return int
     */
    public static function getTimestampBeginOfDayByTimestamp($unix_timestamp)
    {
        return  gmmktime(date(0,   $unix_timestamp),
                         date(0,   $unix_timestamp),
                         date(0,   $unix_timestamp),
                         date("n", $unix_timestamp),
                         date("j", $unix_timestamp),
                         date("Y", $unix_timestamp));
    }

    /**
     * @param string $email
     * @return string
     */
    public static function getNameFromEmail($email)
    {
        return mb_strtoupper(mb_substr($email, 0, 1)) . mb_substr($email, 1, mb_strrpos($email, '@') - 1);
    }

    /**
     * Валидация даты для поля типа 'timestamp without time zone' в постгре
     * @param $date
     * @return bool
     */
    public static function checkDateIsValidForDB($date)
    {
        $date = str_replace([',', ';'], " ", $date);
        $date = trim($date);

        /** проверка даты */

        // Если дата в формате yyyy-m-d или yyyy.m.d
        if (preg_match("/^([\d]{4})[\-\.]([\d]{1,2})[\-\.]([\d]{1,2})(\s|,|;|$)/i", $date, $ma)) {
            //var_dump($ma);
            $y = intval($ma[1]);
            $m = intval($ma[2]);
            $d = intval($ma[3]);
            if ($ma[4] === "") { $eol = true; }
        }
        // Если дата в формате d-m-y или d.m.y
        if (preg_match("/^([\d]{1,2})[\-\.]([\d]{1,2})[\-\.]([\d]{1,2})(\s|,|;|$)/i", $date, $ma)) {
            //var_dump($ma);
            $y = intval($ma[3]);
            $m = intval($ma[2]);
            $d = intval($ma[1]);
            if ($ma[4] === "") { $eol = true; }
            if ($y<70) {
                $y = ($y > 9) ?  "20" . $y :  "200" . $y;
            } else {
                $y = "19" . $y;
            }
        }

        // если нет числа, месяца или года, то ошибочная false
        if (!isset($y, $m, $d)) {
            return false;
        }
        // если пхп проверка даты не прошла то ввернем false
        if (!checkdate($m, $d, $y)) {
            return false;
        }
        // если проверка даты успега и дальше нет строки со временем то вернем true
        if (isset($eol)) {
            return true;
        }


        /** если же проверка даты успешная, дальше проверяем время */

        // постгре принимает любой из таких форматов времени:
        // Y-m-d 0000 | Y-m-d 9999
        // к дате будет добавлено количество секунд (если число нельзя интерпритировать как H:i) или проставлено время как H:i:0
        if (preg_match("/(\s|,|;)([\d]{4})$/i", $date, $ma)) {
            return true;
        }

        // постгре принимает любой из таких форматов времени:
        // Y-m-d 000000 | Y-m-d 999999
        // к дате будет добавлено количество секунд (если число нельзя интерпритировать как H:i:s) или проставлено время как H:i:s
        if (preg_match("/(\s|,|;)([\d]{6})$/i", $date, $ma)) {
            return true;
        }

        // Проверка времени в формате H:i:s+TZ || H:i:s-TZ
        if (preg_match("/(\s|,|;)([\d]{1,2})\:([\d]{1,2})\:([\d]{1,2})[\+\-]([\d]{0,2})$/i", $date, $ma)) {
            $h = intval($ma[2]);
            $i = intval($ma[3]);
            $s = intval($ma[4]);
            if ($h > 23 || $i > 59 || $s > 59) { return false; }

            return true;
        }

        // Проверка времени в формате H:i:s.milliseconds
        if (preg_match("/(\s|,|;)([\d]{1,2})\:([\d]{1,2})\:([\d]{1,2})\.([\d]{0,10})$/i", $date, $ma)) {
            $h = intval($ma[2]);
            $i = intval($ma[3]);
            $s = intval($ma[4]);
            if ($h > 23 || $i > 59 || $s > 59) { return false; }

            return true;
        }

        // Проверка времени в формате H:i:s
        if (preg_match("/(\s|,|;)([\d]{1,2})\:([\d]{1,2})\:([\d]{1,2})$/i", $date, $ma)) {
            $h = intval($ma[2]);
            $i = intval($ma[3]);
            $s = intval($ma[4]);
            if ($h > 23 || $i > 59 || $s > 59) { return false; }

            return true;
        }

        // Проверка времени в формате H:i
        if (preg_match("/(\s|,|;)([\d]{1,2})\:([\d]{1,2})$/i", $date, $ma)) {
            $h = intval($ma[2]);
            $i = intval($ma[3]);
            if ($h > 23 || $i > 59) { return false; }

            return true;
        }

        // Проверка времени в формате H:i:s pm|am
        if (preg_match("/(\s|,|;)([\d]{1,2})\:([\d]{1,2})\:([\d]{1,2})([\s|,|;]{0,20})(pm|am)$/i", $date, $ma)) {
            //var_dump($ma);
            $h = intval($ma[2]);
            $i = intval($ma[3]);
            $s = intval($ma[4]);
            if (mb_strtolower($ma[6]) == 'pm') $h += 12;
            //var_dump($h);
            if ($h > 23 || $i > 59 || $s > 59) { return false; }
            return true;
        }

        // Проверка времени в формате H:i pn|am
        if (preg_match("/(\s|,|;)([\d]{1,2})\:([\d]{1,2})([\s|,|;]{0,20})(pm|am)$/i", $date, $ma)) {
            //var_dump($ma);
            $h = intval($ma[2]);
            $i = intval($ma[3]);
            if (mb_strtolower($ma[5]) == 'pm') $h += 12;
            //var_dump($h);
            if ($h > 23 || $i > 59) { return false; }
            return true;
        }

        return false;
    }

    /**
     * @param string $locale
     * @return array
     */
    public static function get_list_of_timezones($locale='en')
    {
        /*
        $identifiers = \DateTimeZone::listIdentifiers();
        foreach($identifiers as $i) {
            // create date time zone from identifier
            $dtz = new \DateTimeZone($i);
            // create timezone from identifier
            $tz = \IntlTimeZone::createTimeZone($i);
            // if IntlTimeZone is unaware of timezone ID, use identifier as name, else use localized name
            if ($tz->getID() === 'Etc/Unknown' or $i === 'UTC') $name = $i;
            else $name =  $tz->getDisplayName(false, 3, $locale);
            // time offset
            $offset = $dtz->getOffset(new \DateTime());
            $sign   = ($offset < 0) ? '-' : '+';

            $tzs[] = [
                'code'   => $i,
                'name'   => $i. ' (UTC ' . $sign . date('H:i', abs($offset)) . ') ',// . $name,
                'offset' => $offset,
            ];
        }

        ArrayHelper::multisort($tzs, ['offset', 'name']);
        return array_column($tzs, 'name', 'offset');
        */
        $tzs = [
            ['offset' => -43200, 'name' => "(GMT-12:00) International Date Line West"],
            ['offset' => -39600, 'name' => "(GMT-11:00) Midway Island, Samoa"],
            ['offset' => -36000, 'name' => "(GMT-10:00) Hawaii"],
            ['offset' => -32400, 'name' => "(GMT-09:00) Alaska"],
            ['offset' => -28800, 'name' => "(GMT-08:00) Pacific Time (US and Canada); Tijuana"],
            ['offset' => -25200, 'name' => "(GMT-07:00) Mountain Time (US and Canada), Chihuahua, La Paz, Mazatlan, Arizona"],
            ['offset' => -21600, 'name' => "(GMT-06:00) Central Time (US and Canada), Saskatchewan, Guadalajara, Mexico City, Monterrey, Central America"],
            ['offset' => -18000, 'name' => "(GMT-05:00) Eastern Time (US and Canada), Indiana (East), Bogota, Lima, Quito"],
            ['offset' => -14400, 'name' => "(GMT-04:00) Atlantic Time (Canada), Caracas, La Paz, Santiago"],
            ['offset' => -12600, 'name' => "(GMT-03:30) Newfoundland and Labrador"],
            ['offset' => -10800, 'name' => "(GMT-03:00) Brasilia, Buenos Aires, Georgetown, Greenland"],
            ['offset' => -7200,  'name' => "(GMT-02:00) Mid-Atlantic, Azores, Cape Verde Islands"],
            ['offset' => 0,      'name' => "(GMT) Greenwich Mean Time: Dublin, Edinburgh, Lisbon, London, Casablanca, Monrovia"],
            ['offset' => 3600,   'name' => "(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague, Sarajevo, Skopje, Warsaw, Zagreb, Brussels, Copenhagen, Madrid, Paris, Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna, West Central Africa"],
            ['offset' => 7200,   'name' => "(GMT+02:00) Bucharest"],
            ['offset' => 7200,   'name' => "(GMT+02:00) Cairo"],
            ['offset' => 7200,   'name' => "(GMT+02:00) Helsinki, Kiev, Riga, Sofia, Tallinn, Vilnius"],
            ['offset' => 7200,   'name' => "(GMT+02:00) Athens, Istanbul, Minsk"],
            ['offset' => 7200,   'name' => "(GMT+02:00) Jerusalem"],
            ['offset' => 7200,   'name' => "(GMT+02:00) Harare, Pretoria"],
            ['offset' => 10800,  'name' => "(GMT+03:00) Moscow, St. Petersburg, Volgograd"],
            ['offset' => 10800,  'name' => "(GMT+03:00) Kuwait, Riyadh"],
            ['offset' => 10800,  'name' => "(GMT+03:00) Nairobi"],
            ['offset' => 10800,  'name' => "(GMT+03:00) Baghdad"],
            ['offset' => 12600,  'name' => "(GMT+03:30) Tehran"],
            ['offset' => 14400,  'name' => "(GMT+04:00) Abu Dhabi, Muscat"],
            ['offset' => 14400,  'name' => "(GMT+04:00) Baku, Tbilisi, Yerevan"],
            ['offset' => 16200,  'name' => "(GMT+04:30) Kabul"],
            ['offset' => 18000,  'name' => "(GMT+05:00) Ekaterinburg"],
            ['offset' => 18000,  'name' => "(GMT+05:00) Islamabad, Karachi, Tashkent"],
            ['offset' => 19800,  'name' => "(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi"],
            ['offset' => 20700,  'name' => "(GMT+05:45) Kathmandu"],
            ['offset' => 21600,  'name' => "(GMT+06:00) Astana, Dhaka"],
            ['offset' => 21600,  'name' => "(GMT+06:00) Sri Jayawardenepura"],
            ['offset' => 21600,  'name' => "(GMT+06:00) Almaty, Novosibirsk"],
            ['offset' => 23400,  'name' => "(GMT+06:30) Yangon Rangoon"],
            ['offset' => 25200,  'name' => "(GMT+07:00) Bangkok, Hanoi, Jakarta"],
            ['offset' => 25200,  'name' => "(GMT+07:00) Krasnoyarsk"],
            ['offset' => 28800,  'name' => "(GMT+08:00) Beijing, Chongqing, Hong Kong SAR, Urumqi"],
            ['offset' => 28800,  'name' => "(GMT+08:00) Kuala Lumpur, Singapore"],
            ['offset' => 28800,  'name' => "(GMT+08:00) Taipei"],
            ['offset' => 28800,  'name' => "(GMT+08:00) Perth"],
            ['offset' => 28800,  'name' => "(GMT+08:00) Irkutsk, Ulaanbaatar"],
            ['offset' => 32400,  'name' => "(GMT+09:00) Seoul"],
            ['offset' => 32400,  'name' => "(GMT+09:00) Osaka, Sapporo, Tokyo"],
            ['offset' => 32400,  'name' => "(GMT+09:00) Yakutsk"],
            ['offset' => 34200,  'name' => "(GMT+09:30) Darwin"],
            ['offset' => 34200,  'name' => "(GMT+09:30) Adelaide"],
            ['offset' => 36000,  'name' => "(GMT+10:00) Canberra, Melbourne, Sydney"],
            ['offset' => 36000,  'name' => "(GMT+10:00) Brisbane"],
            ['offset' => 36000,  'name' => "(GMT+10:00) Hobart"],
            ['offset' => 36000,  'name' => "(GMT+10:00) Vladivostok"],
            ['offset' => 36000,  'name' => "(GMT+10:00) Guam, Port Moresby"],
            ['offset' => 39600,  'name' => "(GMT+11:00) Magadan, Solomon Islands, New Caledonia"],
            ['offset' => 43200,  'name' => "(GMT+12:00) Fiji Islands, Kamchatka, Marshall Islands"],
            ['offset' => 43200,  'name' => "(GMT+12:00) Auckland, Wellington"],
            ['offset' => 46800,  'name' => "(GMT+13:00) Nuku'alofa"],
        ];
        $tzs = [
            //['offset' => -21600, 'name' => "(GMT-06:00) Central America"],
            //['offset' => 3600,   'name' => "(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague"],
            //['offset' => 3600,   'name' => "(GMT+01:00) Sarajevo, Skopje, Warsaw, Zagreb"],
            //['offset' => 3600,   'name' => "(GMT+01:00) Brussels, Copenhagen, Madrid, Paris"],
            //['offset' => 3600,   'name' => "(GMT+01:00) West Central Africa"],
            //['offset' => 7200,   'name' => "(GMT+02:00) Bucharest"],
            //['offset' => 7200,   'name' => "(GMT+02:00) Cairo"],
            //['offset' => 7200,   'name' => "(GMT+02:00) Athens, Istanbul, Minsk"],
            //['offset' => 7200,   'name' => "(GMT+02:00) Harare, Pretoria"],
            //['offset' => 10800,  'name' => "(GMT+03:00) Nairobi"],
            //['offset' => 28800,  'name' => "(GMT+08:00) Kuala Lumpur"],
            //['offset' => 28800,  'name' => "(GMT+08:00) Beijing, Chongqing, Urumqi"],



            ['offset' => -43200, 'name' => "(GMT-12:00) International Date Line West"],
            ['offset' => -39600, 'name' => "(GMT-11:00) Midway Island, Samoa"],
            ['offset' => -36000, 'name' => "(GMT-10:00) Hawaii"],
            ['offset' => -32400, 'name' => "(GMT-09:00) Alaska"],
            ['offset' => -28800, 'name' => "(GMT-08:00) Pacific Time (US and Canada); Tijuana"],
            ['offset' => -25200, 'name' => "(GMT-07:00) Chihuahua, La Paz, Mazatlan, Arizona, Mountain Time (US and Canada)"],
            ['offset' => -21600, 'name' => "(GMT-06:00) Central Time (US and Canada), Saskatchewan, Guadalajara, Mexico City, Monterrey"],
            ['offset' => -18000, 'name' => "(GMT-05:00) Eastern Time (US and Canada), Indiana (East), Bogota, Lima, Quito"],
            ['offset' => -14400, 'name' => "(GMT-04:00) America/New York EDT, Atlantic Time (Canada), Caracas, La Paz, Santiago"],
            ['offset' => -12600, 'name' => "(GMT-03:30) Newfoundland and Labrador"],
            ['offset' => -10800, 'name' => "(GMT-03:00) Greenland, Buenos Aires, Georgetown, Brasilia, Greenland"],
            ['offset' => -7200,  'name' => "(GMT-02:00) Mid-Atlantic"],
            ['offset' => -3600,  'name' => "(GMT-01:00) Azores, Cape Verde Islands"],
            ['offset' => 0,      'name' => "(GMT) Greenwich Mean Time: Dublin, Edinburgh, Lisbon, London, Casablanca, Monrovia"],
            ['offset' => 3600,   'name' => "(GMT+01:00) Europe/London BST, Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna"],
            ['offset' => 7200,   'name' => "(GMT+02:00) Europe/Amsterdam CEST, Jerusalem, Helsinki, Kiev, Riga, Sofia, Tallinn, Vilnius"],
            ['offset' => 10800,  'name' => "(GMT+03:00) Europe/Moscow MSK, St. Petersburg, Volgograd, Kuwait, Riyadh, Baghdad"],
            ['offset' => 12600,  'name' => "(GMT+03:30) Tehran"],
            ['offset' => 14400,  'name' => "(GMT+04:00) Abu Dhabi, Muscat, Baku, Tbilisi, Yerevan"],
            ['offset' => 16200,  'name' => "(GMT+04:30) Kabul"],
            ['offset' => 18000,  'name' => "(GMT+05:00) Ekaterinburg, Islamabad, Karachi, Tashkent"],
            ['offset' => 19800,  'name' => "(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi"],
            ['offset' => 20700,  'name' => "(GMT+05:45) Kathmandu"],
            ['offset' => 21600,  'name' => "(GMT+06:00) Astana, Dhaka, Sri Jayawardenepura, Almaty, Novosibirsk"],
            ['offset' => 23400,  'name' => "(GMT+06:30) Yangon Rangoon"],
            ['offset' => 25200,  'name' => "(GMT+07:00) Krasnoyarsk, Bangkok, Hanoi, Jakarta"],
            ['offset' => 28800,  'name' => "(GMT+08:00) Irkutsk, Ulaanbaatar, Perth, Taipei, Singapore, Hong Kong SAR"],
            ['offset' => 32400,  'name' => "(GMT+09:00) Yakutsk, Seoul, Osaka, Sapporo, Tokyo"],
            ['offset' => 34200,  'name' => "(GMT+09:30) Darwin, Adelaide"],
            ['offset' => 36000,  'name' => "(GMT+10:00) Vladivostok, Canberra, Melbourne, Sydney, Brisbane, Hobart, Guam, Port Moresby"],
            ['offset' => 39600,  'name' => "(GMT+11:00) Magadan, Solomon Islands, New Caledonia"],
            ['offset' => 43200,  'name' => "(GMT+12:00) Auckland, Wellington, Fiji Islands, Kamchatka, Marshall Islands"],
            ['offset' => 46800,  'name' => "(GMT+13:00) Nuku'alofa"],
        ];
        return array_column($tzs, 'name', 'offset');
    }

    /**
     * @param $str
     * @param $length
     * @return string
     */
    public static function concatString($str, $length)
    {
        if (mb_strlen($str) <= $length) {
            return $str;
        }

        return mb_substr($str, 0, $length) . '...';
    }

    /**
     * @param integer $left_seconds
     * @return string
     */
    public static function getHumanReadableLeftTime($left_seconds)
    {
        $str = "";
        $hours = intval(floor($left_seconds / 3600));
        if ($hours > 0) {
            $str .= "{$hours} hour(s)";
        }

        $test = intval($left_seconds % 3600);
        //var_dump($test);
        if ($test > 0) {
            $minutes = intval(floor($test / 60));
            if ($minutes > 0) {
                $str .= " {$minutes} minutes";
            }
        }

        $tmp = $hours * 3600;
        $tmp += isset($minutes) ? $minutes * 60 : 0;
        $seconds = $left_seconds - $tmp;
        if ($seconds > 0) {
            $str .= " {$seconds} seconds";
        }

        return trim($str);
    }

    /**
     * @param string $file_path
     * @param string $file_path_min
     * @return int
     */
    public static function compressCss($file_path, $file_path_min)
    {
        /* тут производим минификацию и возвращаем путь к нему */
        /* удалить комментарии */
        /* удалить табуляции, пробелы, символы новой строки и т.д. */
        return @file_put_contents(
            $file_path_min,
            str_replace(
                [' {', '} ', '{ ', ' }', ': ', ' :', '; ', ' ;', ', ', ' ,', ' > '],
                ['{' , '}' , '{' , '}' , ':' , ':' , ';' , ';' , ',' , ',' ,  '>' ],
                str_replace(
                    ["\r\n", "\r", "\n", "\t", '  ', '    ', '    '],
                    '',
                    preg_replace(
                        '!/\*[^*]*\*+([^/][^*]*\*+)*/!',
                        '',
                        file_get_contents($file_path)
                    )
                )
            )
        );
    }

    /**
     * @param string $file_path
     * @param string $file_path_min
     * @return int
     */
    public static function compressJs($file_path, $file_path_min)
    {
        /* тут производим минификацию и возвращаем путь к нему */
        return @file_put_contents(
            $file_path_min,
            str_replace(
            //[";\r\n", ";\r", ";\n", "}\r\n", "}\r", "}\n", "\r\n{", "\r{", "\n{"],
            //[';'    , ';'  , ';'  , '}'    , '}'  , '}'  , '{'    , '{'  , '{'  ],
                [";\r\n", ";\r", ";\n"],
                [';'    , ';'  , ';'  ],
                trim(JSMin::minify(file_get_contents($file_path)))
            )
        );

        /*
        return @file_put_contents(
            $file_path_min,
            file_get_contents($file_path)
        );
        */
    }
}