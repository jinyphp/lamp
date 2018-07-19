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

    public $_server;
    public $_serverType;

    use Cmd, Process, Ignore;

    public function __construct($cli)
    {
        //echo __CLASS__."를 생성합니다.\n";
        // ignore 처리
        $this->CLI = $cli;
        $this->_ignore = $this->ignoreData();

        // FTP접속 및 로그인
        $_info = $this->ftpInfo();
        if ($this->connect($_info)) {
            echo $this->_server." Connected as ".$_info['user']."@".$_info['host']."\n";
            $this->setPassiveMode();
            $this->_serverType = $_info['server'];

            // 배포 루트 디렉토리 설정시
            if (isset($_info['root'])) {
                $root = \explode("/",$_info['root']);
                foreach ($root as $name ){
                    $this->cd($name); 
                }
                $this->pwd(); 
            }

        } else {
            echo "Couldn't connect as ".$_info['user']."\n";
        }

    }

    public function ftpInfo()
    {
        $user = include ".ftpconfig.php";

        if (isset($this->CLI->_argv[3])) {
            $key = $this->CLI->_argv[3];
            $this->_server = $key;
            
            if ($user[$key]) return $user[$key];
        }

        $key = $user['default'];
        $this->_server = $key;

        return $user[$key];
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
                if ($this->_serverType == "linux") {
                    // Linux 서버 체크
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
                } else {
                    // Windows 서버 체크
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