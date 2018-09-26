<?php
/*
 * This file is part of the jinyPHP package.
 *
 * (c) hojinlee <infohojin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Jiny\Lamp\File;

use Sunra\PhpSimple\HtmlDomParser;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use \Jiny\Core\Registry;

class File
{
    public $CLI;
    public $fileSystem;


    /**
     * 초기화
     */
    public function __construct($cli)
    {
        $this->CLI = $cli;
        $this->fileSystem = new Filesystem();
    }


    /**
     * 명령을 수행합니다.
     */
    public function process()
    {
        if ($this->CLI->_command) {
            if (method_exists($this, $this->CLI->_command)) {
                $method = $this->CLI->_command;
                $this->$method($this->CLI->_argv);
            } else {
                echo "존재하지 않는 명령어 입니다.";
            }

        } else {
            $this->list($this->CLI->_argv);
        }     
    }


    /**
     * 
     */
    public function list($argv)
    {
        echo "메뉴\n";
        $path = "./resource";

        $tree = $this->tree($path);
        print_r($tree);
    }


    /**
     * 
     */
    public function tree($path, $level=0)
    {
        // 재귀호출 카운트
        static $level = 0; 
        $tree = [];

        if (is_dir($path)) {
            $dir = scandir($path);
            foreach ($dir as $name) {

                // 디렉토리 제어 폴더는 . .. 은 제외합니다.
                if($name == ".") continue;
                if($name == "..") continue;

                for($i=0;$i<$level;$i++) echo "│ ";
                           

                // 서브트리
                if (is_dir($path.DS.$name)) {
                    echo "├─/".$name."\n";
                    //$tree[$name] = ;
                    array_push(
                        $tree, 
                        $this->tree($path.DS.$name, $level++)
                    );

                    $level--;
                    
                } else {
                    echo "│  ".$name."\n";
                    array_push($tree, $name);
                    // $tree[$name] = $name;                     
                }
            }
            return $tree;        
        }
    }

    /**
     * 
     */
}