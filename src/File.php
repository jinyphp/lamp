<?php
namespace Jiny\Lamp;

class File
{
    public $CLI;
    public $_ignore;
    
    public function __construct($cli)
    {
        // echo __CLASS__."를 생성합니다.\n";
        $this->CLI = $cli;

        /*
        $this->_ignore = array("/.git",
        "/vendor",
        "/public",
        "/resource",
        "/theme",
        "/app");
        sort($this->_ignore);
        print_r($this->_ignore);
        */
    }

    /**
     * 디렉토리, 파일이름을 출력합니다.
     */
    public function list($path,$level=0)
    {
        static $level=0;

        if (is_dir($path)) {

            // 디렉토리 목록을 배열로 가지고 옵니다. 
            $dirARR = scandir($path);
            foreach ($dirARR as $value) {
                // . .. 은 제외합니다.
                if($value == "." || $value == "..") continue;

                for($i=0;$i<$level;$i++) echo "│ ";
                
                if (is_dir($path.DS.$value)) {
                    // 디렉토리
                    // 제외파일 및 디렉토리 검사
                    if($this->isIgnore("/".$value)) {
                        continue;
                    } else {
                        echo "├─".$value."\n";
                        $this->list($path.DS.$value, $level++);
                    }
                    
                } else {
                    // 파일
                    // 제외파일 및 디렉토리 검사
                    if ($this->isIgnore($value)) {
                        continue;
                    } else {
                        

                        echo "│ ".$value."\n";
                    }                    
                }               
            }

            $level--;
            
        } else {
            echo $pathDir." 디렉토리가 존재하지 않습니다.\n";
        }

    }

    public function isIgnore($path)
    {
        foreach ($this->_ignore as $key) {
            $key = \rtrim($key,"/");
            /*
            if ($key[0] =="!") {
                $aa = \substr($key,1);
                if ($path == $aa) return false;
            }
            */
            if ($path == $key) return true; 
        }

        return false;
    }

  


    
}