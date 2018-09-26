<?php
/*
 * This file is part of the jinyPHP package.
 *
 * (c) hojinlee <infohojin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Jiny\Lamp\Deploy;

use \Jiny\Core\Registry;

/**
 * http://php.net/manual/en/book.ftp.php
 */
class Server
{
    public $CLI;
    public $FTP;

    public $_conn;
    public $_ignore;


    /**
     * 
     */
    public function __construct($cli)
    {
        //echo __CLASS__."를 생성합니다.\n";
        $this->CLI = $cli;
        $this->FTP = new \Jiny\Lamp\Ftp\Client($cli);
    }


    /**
     * 배포 프로세서
     */
    public function process()
    {
        if (isset($this->CLI->_argv[2])) {
            $path = $this->CLI->_argv[2];
            $path = \rtrim($path,"/");

            // 배포경로로 이동
            if(is_dir($path)) {
                $this->FTP->mkcd($path);                   
            }

            $this->deployNew($path, 0, str_replace("/", DS, $path));
        } else {
            echo "배포할 디렉토리가 없습니다.\n";
        }
    }


    /**
     * 신규배포를 시작합니다.
     * 신규 배포는 전체 파일을 업로드 합니다.
     */
    public function deployNew($path, $level=0, $dir)
    {
        // echo "신규배포를 업로드 합니다. $path\n";

        $pwd = $this->FTP->pwd();
        $node = $this->FTP->load($pwd);
       
        // 재귀호출 카운트
        static $level = 0; 
          
        if (is_dir($dir)) {
            // 디렉토리
            // 목록을 배열로 가지고 옵니다.
            echo $dir." 디렉토리를 검사합니다.\n"; 
            foreach (scandir($dir) as $value) {

                // . .. 은 제외합니다.
                if($value == "." || $value == "..") continue;

                //echo $value."\n";
                for($i=0;$i<$level;$i++) echo "│ ";             
                if (is_dir($dir.DS.$value)) {
                    echo "├─/".$value;
                    // 제외파일 및 디렉토리 검사       
                    if($this->FTP->isIgnore($dir, $value)) {
                        echo "\t =ingore ".$dir .$value;
                        echo "\n";                   
                        continue;
                    } 

                    //echo $value."는 디렉토리 입니다.\n"; 
                    $this->deployDirectory($dir, $value, $level++);
                   
                } else {
                    echo "│  ".$value;             
                    // 제외파일 및 디렉토리 검사       
                    if($this->FTP->isIgnore($dir, $value)) {
                        echo "\t =ingore ".$dir .$value;
                        echo "\n";                   
                        continue;
                    } 
                    $this->deployFile($dir, $value, $node);
                    echo "\n"; 
                }                      

            }

            $level--;            
        } else {
            // 파일.
            // echo $dir."는 파일입니다.\n";
            echo "│  ".$dir;             
            // 제외파일 및 디렉토리 검사 
            $info = \pathinfo($dir);
            $value = $info['basename'];
            if($this->FTP->isIgnore($info['dirname'], $info['basename'])) {
                echo "\t =ingore ".$dir .$value;
                echo "\n";                  
            } else {
                $this->deployFile($info['dirname'], $info['basename'], $node);
                echo "\n";
            }         
            //
        }

    }


    /**
     * 디렉토리를 배포합니다.
     */
    public function deployDirectory($dir, $value, $level)
    {
        echo "\t";
        if ($this->FTP->cd($value)) {
            //echo "  Current directory is now: " . $this->FTP->pwd()."\n";                      
        } else { 
            $this->FTP->_mkdir($value);
            $this->FTP->cd($value);
            //echo "Create directory is now: " . $this->FTP->pwd()."\n";                                                    
        }

        $this->deployNew($value, $level, $dir.DS.$value);
        $this->FTP->up();
    }


    /**
     * 파일을 배포합니다.
     */
    public function deployFile($dir, $value, $node)
    {   
        $mode = $this->CLI->_command;
        if ($value[0] == ".") {
            $mode = "upload";
        }

        switch($mode){ 
            case 'upload':
                $this->upload($dir, $value);
                break;

            case 'update':
            default:          
                $this->update($dir, $value, $node);  
                break;
        }
       
        
    }


    /**
     * 파일을 업로드 합니다.
     */
    public function upload($dir, $value)
    {
        $this->FTP->_upload($dir.DS.$value, $value);
    }

    
    /**
     * 파일을 갱신합니다.
     */
    public function update($dir, $value, $node)
    {
        if (isset($node[$value]['timestamp'])) {
            $mdtm = $node[$value]['timestamp'];
            $timestamp = filemtime($dir.DS.$value);

            if($timestamp > $mdtm) {
                $this->FTP->_upload($dir.DS.$value, $value);
            } else {
                // echo "...old";
            }
        } else {
            $this->FTP->_upload($dir.DS.$value, $value);
        }
    }

    /**
     * Class End
     */    
}