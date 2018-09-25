<?php
namespace Jiny\Lamp;

use Sunra\PhpSimple\HtmlDomParser;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use \Jiny\Core\Registry;

class CLI extends CliAbstract
{
    public $Instance;
    public $_class;
    public $_method;

    public $fileSystem;
    public $File;
    public $ThemeParser;
    public $CURL;

    public $_argv;
    public $_mode, $_command;

    public function __construct($argv)
    {
        if($argv) {
            $this->_argv = $argv;
            $this->parser($argv);         
        } else {
            $this->help();
        }        
    }

    private function help()
    {
        echo "CLI Lamp ".self::VERSION."\n\n";

        $path = ".".DS."vendor".DS."jiny".DS."lamp".DS."src".DS."Help";
        $msg = file_get_contents($path);
        echo $msg;
    }

    private function parser($argv)
    {
        if ($this->isAction($argv)) {
            $cmd = \explode(":", $argv[1]);
            if(isset($cmd[0])) $this->_class = $cmd[0];
            if(isset($cmd[1])) $this->_method = $cmd[1];

            $this->fileSystem = new Filesystem();            
            $this->CURL = new \Jiny\Lamp\CURL\Curl;
            $this->File = new \Jiny\Lamp\File($this);

            $this->process($argv);
        }        
    }

    
    public function process($argv)
    {
        switch ($argv[1]) {
            case '-v':
                $this->version();
                break;

            default:
                $this->isMode($argv[1]);
                $mode = $this->_mode;
                if (method_exists($this, $mode)) {
                    $this->$mode()->process();
                } else {
                    echo "명령이 없습니다.";
                } 
               
        }
        
        // 메모리 삭제
        unset($cli);
    }

    public function isAction($argv)
    {
        if (isset($argv[1])) {
            return true;
        } else {
            // help 메세지를 출력합니다.
            $this->help();
            return false;
        }
    }

    public function isMode($key)
    {
        $arr = \explode(":", $key);
        if (isset($arr[0])) {
            $this->_mode = $arr[0];
        }
        if (isset($arr[1])) {
            $this->_command = $arr[1];
        }

        return $arr;
    }

    /**
     * FTP 명령을 수행합니다.
     */
    public function ftp()
    {
        return new \Jiny\Lamp\FTP\Client($this);
    }

    /**
     * FTP 배포를 진행합니다.
     */
    public function deploy()
    {
        return new \Jiny\Lamp\Deploy\Server($this);
    }

    public function theme()
    {
        return new \Jiny\Lamp\Theme\Theme($this);
    }

    public function menu()
    {
        return new \Jiny\Lamp\Menu\Menu($this);
    }


    /**
     * Class End
     */
}