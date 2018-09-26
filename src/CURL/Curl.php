<?php
/*
 * This file is part of the jinyPHP package.
 *
 * (c) hojinlee <infohojin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Jiny\Lamp\CURL;

use \Jiny\Core\Registry;

class Curl
{
    /**
     * 
     */
    public function __construct()
    {
        //echo __CLASS__."를 생성합니다.\n";
    }


    /**
     * 
     */
    public function download($url, $filename) {
        if (extension_loaded("curl")) {
            echo "cURL 파일을 다운로드 합니다. \n";
            $ch = curl_init($url);
            $fp = fopen($filename, "w");
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            return true;

        } else {
            echo "cURL extension is not available";
            return false;
        }
    }

    /**
     * 
     */
}

