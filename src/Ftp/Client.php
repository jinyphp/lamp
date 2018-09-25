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
        $this->CLI = $cli;

        if (extension_loaded('ftp')) {

            if ($this->CLI->_command == "init") {
                // 초기화 명령은 접속하지 않음.

            } else {
                // FTP접속 및 로그인
                $_info = $this->ftpInfo();
                if ($this->connect($_info)) {
                    // ignore 처리            
                    $this->_ignore = $this->ignoreData();

                    echo $this->_server." Connected as ".$_info['user']."@".$_info['host']."\n";
                    $this->_serverType = $_info['server'];
                    if ($_info['mode'] == "passive") {
                        $this->setPassiveMode();
                    }                  
            
                    // 배포 루트 디렉토리 설정시
                    $this->rootInit($_info);

                } else {
                    echo "Couldn't connect as ".$_info['user']."\n";
                }
            }          

        } else {
            echo "please, check enable ftp extension in php.ini";
            exit;
        }
    }

    /**
     * 배포 루트 디렉토리 설정시
     */
    public function rootInit($_info)
    {
        if (isset($_info['root'])) {
            $root = \explode("/",$_info['root']);
            foreach ($root as $name ){
                $this->cd($name); 
            }
            $this->pwd(); 
        }
    }

    /**
     * 설정파일을 읽어옵니다.
     */
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
        echo "connect to ".$_server['host']."\n";

        // set up a connection or die
        $this->_conn = ftp_connect($_server['host']) or die("Couldn't connect to ".$_server['host']);

        // login with username and password
        return ftp_login($this->_conn, $_server['user'], $_server['pass']);  
    }

    /**
     * 패시브 모드
     */
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

        /**
     * FTP 현재의 경로를 반환합니다.
     */
    public function pwd()
    {
        return ftp_pwd($this->_conn);
    }

    /**
     * FTP 상위 디렉토리 이동합니다.
     */
    public function up()
    {
        if (ftp_cdup($this->_conn)) { 
            //echo "cdup successful\n";
        } else {
            //echo "cdup not successful\n";
        }
    }

    /**
     * FTP 디렉토리를 변경합니다.
     */
    public function cd($path)
    {
        if (@ftp_chdir($this->_conn, $path)) {
            echo "Current directory is now: " . ftp_pwd($this->_conn) . "\n";
            return true;
        } else { 
            echo "Couldn't change directory\n";
            return false;
        }
    }

    /**
     * FTP 디렉토리를 변경합니다.
     * 디렉토리가 없는 경우 생성을 합니다.
     */
    public function mkcd($path)
    {      
        $dir = \explode("/",$path);     
        foreach ($dir as $value) {       
            if ($value) {
                //echo ">>>>>>".$value."\n";
                if (@ftp_chdir($this->_conn, $value)) {
                    echo "Current directory is now: " . ftp_pwd($this->_conn) . "\n";
                } else { 
                    // echo "Couldn't change directory ";
                    $this->mkdir($value);
                    ftp_chdir($this->_conn, $value);
                    echo "Current directory is now: " . ftp_pwd($this->_conn) . "\n";
                } 
            }               
        }
    }

    /**
     * FTP 접속을 종료합니다.
     */
    public function __destruct()
    {
        // close the connection
        if ($this->_conn) {
            ftp_close($this->_conn);  
            echo "FTP 접속을 종료합니다."; 
        }   
    }
    
    /**
     * Class End
     */
}