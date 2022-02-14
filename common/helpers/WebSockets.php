<?php

namespace common\helpers;

use Yii;
use yii\base\Exception;
use common\models\Servers;
use frontend\models\NodeApi;

/**
 * This is the static Functions.
 *
 */
class WebSockets
{
    public $host = null;
    public $port = null;
    public $connect_string = null;

    private $socks;

    public function __construct($site_token=null)
    {
        return $this->open($site_token);
    }

    public function open($site_token=null)
    {
        $Server = Servers::find()
            //->asArray()
            ->where([
                'server_type' => Servers::SERVER_TYPE_SIGN,
                'server_status' => Servers::SERVER_ACTIVE_YES
            ])->limit(1)->all();
        // wss://$Server[0]->server_url/ws/webfm/$site_token
        if (!$site_token)  { $site_token = NodeApi::site_token_key(); }
        $url = "ssl://". $Server[0]->server_url . "/ws/webfm/" . $site_token;
        //var_dump($url); exit;
        /*
        if ((!$host && !$port) && !$connect_url) {
            throw new Exception("You must specify host && port or connect_url");
        }
        */
        $key = base64_encode(uniqid());
        $query = parse_url($url);


        $path = $query['path'];
        if (isset($query['query']) && strlen($query['query']) > 0) {
            $path .= '?' . $query['query'];
        }

        //  wss://$Server[0]->server_url/ws/webfm/$site_token

        $header = ""
            . "GET {$path} HTTP/1.1" . "\r\n"
            . "pragma: no-cache" . "\r\n"
            . "cache-control: no-cache" . "\r\n"
            . "Upgrade: WebSocket" . "\r\n"
            . "Connection: Upgrade" . "\r\n"
            . "Host: {$query['host']}" . "\r\n"
            . "Origin: http://{$_SERVER['HTTP_HOST']}" . "\r\n"
            . "Sec-WebSocket-Key: {$key}" . "\r\n"
            . "Sec-WebSocket-Version: 13" . "\r\n"
            . "\r\n";

        //$this->socks = fsockopen($query['host'], $query['port'], $errno, $errstr, 2);
        $this->socks = stream_socket_client($url, $errno, $errstr, 30);
        //var_dump($this->socks);
        if ($this->socks) {
            if (fwrite($this->socks, $header)) {

                $headers = fread($this->socks, 2000);
                //var_dump($headers); exit;
                return $headers;

            } else {
                throw new Exception("Failed write headers to socks {$query['host']}:{$query['port']}. Error:: {$errno}:{$errstr}");
            }
        } else {
            throw new Exception("Failed connect to socks {$query['host']}:{$query['port']}. Error:: {$errno}:{$errstr}");
        }
    }

    public function close()
    {
        if ($this->socks) {
            return fclose($this->socks);
        }
        return false;
    }

    public function sendData($data)
    {
        if ($this->socks) {
            if (fwrite($this->socks, $this->hybi10Encode($data))) {
                $wsdata = fread($this->socks, 2000);
                return $this->hybi10Decode($wsdata);
            } else {
                return false;
                //throw new Exception("Failed write data to socks.");
            }
        } else {
            return false;
            //throw new Exception("Need connect to socks before");
        }
    }

    public function sendFsChange($data)
    {
        //var_dump($url);
        $headersOpen = $this->open();
        //var_dump($headersOpen);
        $dataAnswer = $this->sendData($data);
        //var_dump($dataAnswer);
        $this->close();
        //exit;
        return $dataAnswer;
    }

    private function hybi10Encode($payload, $type = 'text', $masked = true)
    {
        $frameHead = array();
        $frame = '';
        $payloadLength = strlen($payload);

        switch ($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;

            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;

            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;

            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }

            // most significant bit MUST be 0 (close connection if frame too big)
            if ($frameHead[2] > 127) {
                $this->close();
                return false;
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }

        if ($masked === true) {
            // generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);
        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }

    private function hybi10Decode($data)
    {
        $bytes = $data;
        $dataLength = '';
        $mask = '';
        $coded_data = '';
        $decodedData = '';
        $secondByte = sprintf('%08b', ord($bytes[1]));
        $masked = ($secondByte[0] == '1') ? true : false;
        $dataLength = ($masked === true) ? ord($bytes[1]) & 127 : ord($bytes[1]);

        if($masked === true)
        {
            if($dataLength === 126)
            {
                $mask = substr($bytes, 4, 4);
                $coded_data = substr($bytes, 8);
            }
            elseif($dataLength === 127)
            {
                $mask = substr($bytes, 10, 4);
                $coded_data = substr($bytes, 14);
            }
            else
            {
                $mask = substr($bytes, 2, 4);
                $coded_data = substr($bytes, 6);
            }
            for($i = 0; $i < strlen($coded_data); $i++)
            {
                $decodedData .= $coded_data[$i] ^ $mask[$i % 4];
            }
        }
        else
        {
            if($dataLength === 126)
            {
                $decodedData = substr($bytes, 4);
            }
            elseif($dataLength === 127)
            {
                $decodedData = substr($bytes, 10);
            }
            else
            {
                $decodedData = substr($bytes, 2);
            }
        }

        return $decodedData;
    }


}