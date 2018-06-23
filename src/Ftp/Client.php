<?php
namespace Jiny\Lamp\Ftp;

use \Jiny\Core\Registry;
/**
 * http://php.net/manual/en/book.ftp.php
 */
class Client
{
    public $CLI;

    public $_conn;
    public $_ignore;

    use Cmd, Process, Ignore;

    public function __construct($cli)
    {
        //echo __CLASS__."를 생성합니다.\n";
        // ignore 처리
        $this->CLI = $cli;
        $this->_ignore = $this->ignoreData();

        /*
        print_r($this->_ignore);
        $path = "/vendor/jiny";
        if ($this->isIgnore($path)) {
            echo "ignore\n";
        } else {
            echo "pass\n";            
        }
        */


        // FTP접속 및 로그인
        $_info = $this->ftpInfo();
        if ($this->connect($_info)) {
            echo "Connected as ".$_info['user']."@".$_info['host']."\n";
            $this->setPassiveMode();
        } else {
            echo "Couldn't connect as ".$_info['user']."\n";
        }

    }

    public function ftpInfo()
    {
        $user = include ".ftpconfig.php";
        return $user['default'];
    }

    public function connect($_server)
    { 
        // set up a connection or die
        $this->_conn = ftp_connect($_server['host']) or die("Couldn't connect to ".$_server['host']);

        // login with username and password
        return ftp_login($this->_conn, $_server['user'], $_server['pass']);  
    }

    public function setPassiveMode()
    {
        // turn passive mode on
        ftp_pasv($this->_conn, true);
    }

    public function load($path)
    {
        // 내용이 있는 경우만 처리
        if ($arr = ftp_rawlist($this->_conn, $path)) {

            $rawlist = [];

            foreach ($arr as $v) {
               
                $info = array();
                $vinfo = preg_split("/[\s]+/", $v, 9);

                if ($vinfo[0] !== "total") {
                    $info['chmod'] = $vinfo[0];
                    $info['type'] = $vinfo[0]{0} === 'd' ? 'directory' : 'file'; 

                    $info['num'] = $vinfo[1];
                    $info['owner'] = $vinfo[2];
                    $info['group'] = $vinfo[3];
                    $info['size'] = $vinfo[4];

                    $info['month'] = $vinfo[5];
                    $info['day'] = $vinfo[6];
                    $info['time'] = $vinfo[7];

                    $info['name'] = $vinfo[8];

                    $info['timestamp'] = strtotime($info['month']." ".$info['day']." ".$info['time']);
              
                    $rawlist[$info['name']] = $info;
                }
            }

            return $rawlist;
        } 

    }

    public function __destruct()
    {
        // close the connection
        ftp_close($this->_conn);  
        echo "FTP 접속을 종료합니다.";        
    }
    
    /**
     * Class End
     */
}