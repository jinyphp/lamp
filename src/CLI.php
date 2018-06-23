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
        //echo __CLASS__."를 생성합니다.\n";
        if($argv) {
            $this->_argv = $argv;
            
            if (isset($argv[1])) {
                $cmd = \explode(":", $argv[1]);
                if(isset($cmd[0])) $this->_class = $cmd[0];
                if(isset($cmd[1])) $this->_method = $cmd[1];
    
                $this->fileSystem = new Filesystem();
                $this->ThemeParser = new \Jiny\Lamp\Parser;
                $this->CURL = new \Jiny\Lamp\CURL\Curl;

                $this->File = new \Jiny\Lamp\File($this);
    
                if (isset($argv[1])) {
                    $this->process($argv);
                } else {
                    echo "인자값이 없습니다.\n";
                }
    
            } else {
                // help 메세지를 출력합니다.
                echo "CLI Lamp ".self::VERSION."\n";
                $msg = file_get_contents("./vendor/jiny/lamp/src/Help");
                echo $msg;
            }

            
        }       
        
    }

    
    public function process($argv)
    {
        switch ($argv[1]) {
            case '-v':
                $this->version();
                break;

            case 'theme':
                $this->_class = new \Jiny\Lamp\ThemeCLI($this);            
                if(isset($cmd[1])){
                    if (method_exists($this->_class, $this->_method)) {
                        echo "메서드가 있습니다.";
                    }
                } else {
                    // 클래스 __invoke() 실행
                    ($this->_class)();
                }
                break;

            case 'theme:make':
                $cli = new \Jiny\Lamp\ThemeCLI($this);
                $cmd = \explode(":", $argv[1]);
                
                if(isset($this->_method)){
                    if (method_exists($cli, $this->_method)) {
                        echo "메서드가 있습니다.";
                        $method = $this->_method;
                        if(isset($argv[2])){
                            $cli->$method($argv[2]);
                        } else {
                            echo "테마명을 입력해 주세요\n";
                        }     
                    }
                }
                break;

            case 'theme:del':
                echo $argv[2]." 를 삭제합니다.\n";
                if ($argv[2]) {
                    $this->fileSystem->remove("./theme/".$argv[2]);            
                }
                break;

            case 'theme:parser':
                echo $argv[2]." 를 분석합니다.\n";
                if ($argv[2]) {
                    $str = file_get_contents("./theme/".$argv[2]."/temp.htm");
                    $this->ThemeParser->setTheme($argv[2]);
                    $this->ThemeParser->process($str);
                }
                break;

            case 'theme:geturl':
                $cli = new \Jiny\Lamp\ThemeCLI($this);
                $cli->geturl($argv);

                
                break;
            case 'theme:set':
                
                    break;

        }

        

        /////////////////////
        $key = \explode(":", $argv[1]);
        if (isset($key[0])) $this->_mode = $key[0];
        if (isset($key[1])) $this->_command = $key[1];
        switch ($this->_mode) {
            case 'ftp':
                $this->ftp(); 
                break;
            case 'deploy':
                $this->deploy();
                break;
        }

        // 메모리 삭제
        unset($cli);

    }

    /**
     * FTP 명령을 수행합니다.
     */
    public function ftp()
    {
        $cli = new \Jiny\Lamp\FTP\Client($this);
        $cli->process();
    }

    /**
     * FTP 배포를 진행합니다.
     */
    public function deploy()
    {
        $cli = new \Jiny\Lamp\Deploy\Server($this);
        $cli->process();
    }
    
    /**
     * Class End
     */
}