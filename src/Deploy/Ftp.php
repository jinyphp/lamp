<?php
namespace Jiny\Lamp\Deploy;

use \Jiny\Core\Registry;
/**
 * http://php.net/manual/en/book.ftp.php
 */
class Ftp
{

    public $CLI;
    public $_conn;
    public $_ignore;

    public $_rootFTP = [];
    public $_rootLocal;
    
    public function __construct($cli)
    {
        //echo __CLASS__."를 생성합니다.\n";
        $this->CLI = $cli;
        
        $_info = $this->ftpInfo();
        if ($this->connect($_info)) {
            echo "Connected as ".$_info['user']."@".$_info['host']."\n";
            $this->setPassiveMode();
        } else {
            echo "Couldn't connect as ".$_info['user']."\n";
        }
        
        echo "time zone = ".date_default_timezone_get()."\n";

        //date_default_timezone_set("Asia/Seoul");
        //echo "time zone = ".date_default_timezone_get()."\n";

        $this->_ignore = $this->ignoreData();
    }




    public function process()
    {
        switch($this->CLI->_command){
            case 'upload':
                echo "파일을 업로드 합니다.\n";
                $this->upload();
                break;
            case 'ls':
                if (isset($this->CLI->_argv[2])) {
                    $this->ftp_ls($this->CLI->_argv[2]);
                } else {
                    echo "서버 폴더 경로명이 없습니다.\n";
                }
                break;
            case 'rm':
                if (isset($this->CLI->_argv[2])) {
                    $this->ftp_rm($this->CLI->_argv[2]);
                } else {
                    echo "삭제할 파일명이 없습니다..\n";
                }
                break;
            case 'deploy':
                echo "배포를 시작합니다.\n";
                if (isset($this->CLI->_argv[2])) {
                    // $this->ftp_chPath($this->CLI->_argv[2]);
                    $path = $this->CLI->_argv[2];
                    $path = \rtrim($path,"/");
                    $this->ftp_chPath($path);
                    $this->ftp_deploy($path, 0, str_replace("/", DS, $path) );
                } else {
                    echo "삭제할 파일명이 없습니다..\n";
                }
                break; 
        }
    }

    /**
     * 지정 경로로 변경을 합니다.
     * 디렉토리가 없는 경우 생성을 합니다.
     */
    public function ftp_chPath($path)
    {      
        $dir = \explode("/",$path);     
        foreach ($dir as $value) {       
            if ($value) {
                if (@ftp_chdir($this->_conn, $value)) {
                    echo "Current directory is now: " . ftp_pwd($this->_conn) . "\n";
                } else { 
                    // echo "Couldn't change directory ";
                    $this->ftp_mkdir($value);
                    ftp_chdir($this->_conn, $value);
                    echo "Current directory is now: " . ftp_pwd($this->_conn) . "\n";
                } 
            }               
        }
    }

    public function ftp_chdir($path)
    {
        if (@ftp_chdir($this->_conn, $value)) {
            echo "Current directory is now: " . ftp_pwd($this->_conn) . "\n";
            return true;
        } else { 
            echo "Couldn't change directory\n";
            return false;
        }
    }

    public function ftp_deploy($path, $level=0, $dir)
    {

        $pwd = ftp_pwd($this->_conn);
        $node = $this->ftp_load($pwd);

        static $level=0;   
        if (is_dir($dir)) {

            // 디렉토리 목록을 배열로 가지고 옵니다. 
            foreach (scandir($dir) as $value) {
                // . .. 은 제외합니다.
                if($value == "." || $value == "..") continue;

                // 제외파일 및 디렉토리 검사 
                if($this->isIgnore($value)) {
                    echo $value."\t\t\t =ingore\n";                    
                    continue;
                } else {
                    echo $value."\n";
                   
                    for($i=0;$i<$level;$i++) echo "│ ";
                    if (is_dir($dir.DS.$value)) {
                        // 디렉토리                                             
                        echo "├─/".$value;

                        if (@ftp_chdir($this->_conn, $value)) {
                            echo "  Current directory is now: " . ftp_pwd($this->_conn)."\n";                      
                        } else { 
                            $this->ftp_mkdir($value);
                            ftp_chdir($this->_conn, $value);
                            echo "Create directory is now: " . ftp_pwd($this->_conn)."\n";                                                    
                        }

                        $this->ftp_deploy($value, $level++, $dir.DS.$value);
                        $this->ftp_chUP();                                               
                        
                    } else {
                        // 파일                 
                        // 파일을 업로드 합니다.                                         
                        echo "│  ".$value;
                        //echo " = ".$dir.DS.$value;
                        $timestamp = filemtime($dir.DS.$value);
                        //echo "=".$timestamp.", ";
                        $mdtm = $node[$value]['timestamp'];
                        //echo $mdtm;
                        if($timestamp>$mdtm) {
                            //echo "...new";
                            $this->ftp_fileUpload($dir.DS.$value, $value);
                        } else {
                            //echo "...old";
                        }
                            //
                        echo "\n";
                                        
                    }
                  
                }           

            }

            $level--;
            
        } else {
            echo $dir." 디렉토리가 존재하지 않습니다.\n";
        }
    }


    public function ftp_nodeTime($name,$arr)
    {
        return $arr[$name]['timestamp'];
    }

    
   


    public function ftp_chUP()
    {
        if (ftp_cdup($this->_conn)) { 
            //echo "cdup successful\n";
        } else {
            //echo "cdup not successful\n";
        }
    }

    public function ftp_mkdir($path)
    {
        if (ftp_mkdir($this->_conn, $path)) {
            echo "  successfully created ".$path;
        } else {
            echo "  There was a problem while creating ".$path;
        }
    }

    public function list($path, $level=0, $dirname=NULL)
    {
        

    }

    public function ftp_rm($file)
    {
        if (ftp_delete($this->_conn, $file)) {
            echo "$file deleted successful\n";
        } else {
            echo "could not delete $file\n";
        }
    }

    public function ftp_ls($path=".")
    {
        $arr = ftp_rawlist($this->_conn, $path);
        foreach ($arr as $value) {
            echo $value."\n";
        }
    }



    public function upload()
    {
        
        echo "목록\n";
        // $this->_arrFtp = $this->ftp_dir(".");
        // print_r($this->_arrFtp);
        
        //$this->list(".");
        
        // $file = "./data/menu/menu.php";
        // $this->ftp_fileUpload($file);
    }

    
    public function ftp_fileUpload($src, $file)
    {
        $fp = fopen($src, 'r');
        // try to upload $file
        if (ftp_fput($this->_conn, $file, $fp, FTP_BINARY)) {
            echo "  Successfully uploaded";
        } else {
            echo "  There was a problem while uploading";
        }

        fclose($fp);
    }


    public function ftp_isDir($dir)
    {
        $list = $this->ftp_dirList($dir);
    
    }

    















    
     /**
     * 지정된 디렉토리의 정보를 읽어 옵니다.
     */
    public function ftp_load($dir)
    {
        // 내용이 있는 경우만 처리
        //echo $dir."내용을 읽어 옵니다.\n";
        if ($arr = ftp_rawlist($this->_conn, $dir)) {

            $rawlist = [];

            foreach ($arr as $v) {
                //echo $v."\n";
                $info = array();
                $vinfo = preg_split("/[\s]+/", $v, 9);
                //print_r($vinfo);

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

        } else {
            // echo "내용이 없습니다.\n";
        }
    }

    public function ftpInfo()
    {
        $_server['host'] = "hojin.godohosting.com";
        $_server['user'] = "hojin";
        $_server['pass'] = "!@hj68890";

        return $_server;
    }

    public function connect($_server)
    {
        //echo "FTP를 접속합니다...\n";        
 
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

    public function __destruct()
    {
        // close the connection
        ftp_close($this->_conn);  
        echo "FTP 접속을 종료합니다.";
    }

    public function isIgnore($path)
    {
        // $aaa = $this->ignoreData();
        echo $path." \t";
        return $this->ignoreMask($path, $this->_ignore);
    }

    public function ignoreData()
    {
        // 데이터를 읽어옵니다.
/*
        $aaa = array(
            ".git",
            ".gitignore",
            "/resource",
            "!/resource/htmls",
            "/theme");
        print_r($aaa);
*/
        $filename = ".ftpignore";
        $fp = @fopen($filename,"r");
        if($fp){
            $i=0;
            while(($buffer = fgets($fp)) !== false){
                if($buffer) $data[$i++] = trim($buffer, "\n\r");
            }            
        }
        fclose($fp);
        // $this->_ignore = $data;
        //print_r($this->_ignore);
        return $data;
    }


    /**
	 * Matches filename against patterns.
	 * @param  string   $path  relative path
	 * @param  string[]  $patterns
	 */
	public static function ignoreMask(string $path, array $patterns, bool $isDir = false): bool
	{
		$res = false;
		$path = explode('/', ltrim($path, '/'));
		foreach ($patterns as $pattern) {
			$pattern = strtr($pattern, '\\', '/');
			if ($neg = substr($pattern, 0, 1) === '!') {
				$pattern = substr($pattern, 1);
			}

			if (strpos($pattern, '/') === false) { // no slash means base name
				if (fnmatch($pattern, end($path), FNM_CASEFOLD)) {
					$res = !$neg;
				}
				continue;

			} elseif (substr($pattern, -1) === '/') { // trailing slash means directory
				$pattern = trim($pattern, '/');
				if (!$isDir && count($path) <= count(explode('/', $pattern))) {
					continue;
				}
			}

			$parts = explode('/', ltrim($pattern, '/'));
			if (fnmatch(
				implode('/', $neg && $isDir ? array_slice($parts, 0, count($path)) : $parts),
				implode('/', array_slice($path, 0, count($parts))),
				FNM_CASEFOLD | FNM_PATHNAME
			)) {
				$res = !$neg;
			}
		}
		return $res;
	}

}