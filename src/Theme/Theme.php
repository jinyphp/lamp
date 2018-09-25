<?php
namespace Jiny\Lamp\Theme;

use Sunra\PhpSimple\HtmlDomParser;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use \Jiny\Core\Registry;
/**
 * 테마 simple 크롤러
 */
class Theme
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
            $this->themeList($this->CLI->_argv);
        }     
    }


    /**
     * 테마를 삭제합니다.
     */
    public function remove($argv)
    {
        if ($this->isThemeName($argv)) {
            $name = $this->CLI->_argv[2];
            echo $name." 테마를 삭제합니다.\n";

            $path = $this->path();
            // page 3.238
            if (is_dir($path.$name)) {
                if (@rmdir($path.$name)) {
                    echo "테마 폴더를 삭제하였습니다.\n";
                } else {
                    echo "테마폴더를 삭제할 수 없습니다. 폴더안의 파일을 먼저 삭제해 주세요. 또는 delete 명령을 사용해 주세요\n";
                }

            } else {
                echo "삭제할 테마 폴더가 없습니다.\n";
            }

        } 
    }

    public function delete($argv)
    {
        if ($this->isThemeName($argv)) {
            $name = $this->CLI->_argv[2];
            echo $name." 테마를 삭제합니다.\n";

            $path = $this->path();
            $this->fileSystem->remove($path.$name);
        } 
    }


    /**
     * 테마를 생성합니다.
     */
    public function make($argv)
    {
        if ($this->isThemeName($argv)) {
            $name = $this->CLI->_argv[2];
            $path = $this->path();
            
            if (!is_dir($path.$name)) {
                mkdir($path.$name);
                echo "테마 폴더를 생성하였습니다.\n";

                // Page 3.284
                $themefile = $path."jiny/default/theme.ini";
                if (file_exists($themefile)) {
                    if (copy($themefile, $path.$name.DS."theme.ini")) {
                        echo "기본 설정파일을 복사합니다.\n";
                    } else {
                        echo "설정파일 복사를 할 수 없습니다.\n";
                    }
                } else {
                    echo "테마 설정파일을 자동생성할 수 없습니다. 수동으로 생성해 주세요.\n";
                }
            } else {
                echo "이미 존재하는 테마명 입니다.";
            }
            
        }         
    }


    /**
     * 테마의 목록을 출력합니다.
     */
    public function themeList($argv)
    {
        echo "/theme 목록을 출력합니다.\n";
        $str = \file_get_contents("./theme/theme.json");
        $themes = \json_decode($str);

        foreach ($themes as $key => $value) {
            if ($value->active == "*") {
                echo "[*] ".$key."\n";
            } else {
                echo "[] ".$key."\n";
            }
                
        }
    }

    public function set($argv)
    {
        if ($this->isThemeName($argv)) {
            $str = \file_get_contents("./theme/theme.json");
            $themes = \json_decode($str);

            foreach ($themes as $key =>$obj) {
                echo $key."\n";
            }

            echo "\n name=".$key;
            if( isset( $themes[$key] ) ) {
                echo "테마가 존재합니다.\n";
            } else {
                echo "테마가 존재하지 않습니다.\n";
            }
        }           

    }

    /**
     * 테마명 입력을 확인합니다.
     */
    private function isThemeName($argv)
    {
        if (isset($argv[2])) {            
            return true;
        }

        echo "테마명을 입력해 주세요\n";         
        return false;
    }

    /** 
     * 테마 폴더의 존재를 확인합니다.
     */
    private function isTheme($argv)
    {
        if (is_dir("./theme/".$argv[2])) {
            return true;
        } 

        echo "테마명 폴더가 없습니다. 먼저 생성해 주세요.\n";
        return false; 
    }

    public function geturl($argv)
    {
        if (!$this->isThemeName($argv)) {
            return false;
        } else {
            if(!$this->isTheme($argv)){
                return false;
            }
        }
        
        $this->download($argv);
        
    }

    private function path()
    {
        return "./theme/";
    }

    private function download($argv)
    {
        $tempfile = "temp.htm";
        if (isset($argv[3])) {
            echo $argv[3]." 를 다운로드 합니다.";
            $this->CLI->CURL->download($argv[3], $this->path().$argv[2].DS.$tempfile);           
            echo "= OK\n";


            echo "다운로드한 HTML DOM을 분석합니다.\n";
            if ($str = file_get_contents("./theme/".$argv[2]."/temp.htm")) {
                $dom = HtmlDomParser::str_get_html( $str );
                $uri = parse_url($argv[3]);
            } else {
                echo "다운로드한 html을 읽을 수 없습니다.\n";
                return;
            }
            
                
            // 이미지를 찾아 다운로드 합니다.
            // Find all images 
            echo "HTML내 이미지를 다운로드 합니다.\n";
            $this->downImg( $dom->find('img'), $argv );
                
            // CSS를 찾아 다운로드 합니다.
            echo "HTML내 CSS를 다운로드 합니다.\n";
            $this->downCSS( $dom->find('link'), $argv );

        }  else {
            echo "다운로드할 URL이 없습니다.";
        }
    }


    /**
     * 문서 돔에서 이미지를 다운로드 합니다.
     */
    public function downImg($arr, $argv)
    {
        foreach($arr as $element) {
            $uri = parse_url($argv[3]);

            switch ($this->srcType($element->src)) {
                case '/':
                    echo "절대경로에서 다운로드 합니다. ";
                    echo $element->src." \n";

                    $u = $uri['scheme']."://".$uri['host'].$element->src;
                    echo $u."\n";

                    // page 3.257
                    // 파일경로를 분석합니다.
                    $path = pathinfo($element->src);
                    //print_r($path);
                    if (is_dir("./theme/".$argv[2].DS.$path['dirname'])) {
                        echo "OK \n";
                    } else {
                        echo "디렉토리 경로가 없습니다. \n";
                        $this->CLI->fileSystem->mkdir("./theme/".$argv[2].DS.$path['dirname']);
                    }

                    $this->CLI->CURL->download($u, "./theme/".$argv[2].DS.$element->src);
                    break;

                case '.':
                    echo "상대경로에서 다운로드 합니다. ";
                    echo $element->src." \n";

                    $u = $uri['scheme']."://".$uri['host'].$element->src;
                    echo $u."\n";

                    // page 3.257
                    // 파일경로를 분석합니다.
                    $path = pathinfo($element->src);
                    //print_r($path);
                    if (is_dir("./theme/".$argv[2].DS.$path['dirname'])) {
                        echo "OK \n";
                    } else {
                        echo "디렉토리 경로가 없습니다. \n";
                        $this->CLI->fileSystem->mkdir("./theme/".$argv[2].DS.$path['dirname']);
                    }

                    $this->CLI->CURL->download($u, "./theme/".$argv[2].DS.$element->src);
                    break;
            }
            
        }
    }

    /**
     * 문서 돔에서 CSS를 다운로드 합니다.
     */
    public function downCSS($arr, $argv)
    {
        foreach($arr as $element) {
            if($element->rel == "stylesheet"){

                $uri = parse_url($argv[3]);

                switch ($this->srcType($element->href)) {
                    case '/':
                        echo "절대경로에서 다운로드 합니다. ";
                        echo $element->href." \n";        
                        $u = $uri['scheme']."://".$uri['host'].$element->href;
                        echo $u."\n";
        
                        // page 3.257
                        // 파일경로를 분석합니다.
                        $path = pathinfo($element->href);
                        //print_r($path);
                        if (is_dir("./theme/".$argv[2].DS.$path['dirname'])) {
                            echo "OK \n";
                        } else {
                            echo "디렉토리 경로가 없습니다. \n";
                            $this->CLI->fileSystem->mkdir("./theme/".$argv[2].DS.$path['dirname']);
                        }
        
                        $filename = "./theme/".$argv[2].DS.$element->href;

                        $this->CLI->CURL->download($u, $filename);
        
                        // 코드 beatifier                   
                        $str = $this->beatifier( file_get_contents($filename) );        
                        file_put_contents($filename, $str);

                        break;

                    case '.':
                        echo "상대경로에서 다운로드 합니다. ";
                        echo $element->href." \n";
                        $u = $uri['scheme']."://".$uri['host'].$element->href;
                        echo $u."\n";

                        // 3권 p.257
                        // 파일경로를 분석합니다.
                        $path = pathinfo($element->href);
                        //print_r($path);
                        if (is_dir("./theme/".$argv[2].DS.$path['dirname'])) {
                            echo "OK \n";
                        } else {
                            echo "디렉토리 경로가 없습니다. \n";
                            $this->CLI->fileSystem->mkdir("./theme/".$argv[2].DS.$path['dirname']);
                        }
        
                        $filename = "./theme/".$argv[2].DS.$element->href;
                        
                        $this->CLI->CURL->download($u, $filename);
        
                        // 코드 beatifier                   
                        $str = $this->beatifier( file_get_contents($filename) );        
                        file_put_contents($filename, $str);

                        break;
                }
                
                
                
            }
        }
    }

    /**
     * url의 구조를 파악합니다.
     */
    public function srcType($string)
    {
        $url = parse_url($string);
        if(isset($url['scheme'])){
            echo $string."== 스킵\n";
            if ($url['scheme'] == "http" || $url['scheme'] == "https") {
                // 외부의 연결파일은 다운로드 할 필요가 없습니다.
            }

        } else if($string[0] == "/") {
            // 절대 경로의 파일입니다.
            echo $string."== 절대경로\n";
            return "/";     

        } else {
            // 상대 경로의 파일입니다.
            echo $string."== 상대경로\n";
            return ".";
        }

    }

    /**
     * 문서를 정리합니다.
     */
    public function beatifier($str)
    {
        $str = str_replace(";",";\n", $str);
        $str = str_replace("}","\n}\n\n", $str);
        $str = str_replace("{"," {\n", $str);

        $str = str_replace("@"," \n@", $str);

        return $str;
    }

    public function parser($argv)
    {
        if ($this->isThemeName($argv)) {
            $str = file_get_contents("./theme/".$argv[2]."/temp.htm");

            $ThemeParser = new \Jiny\Lamp\Theme\Parser;
            $ThemeParser->setTheme($argv[2]);
            $ThemeParser->process($str);
        }
        
    }

    /**
     * 
     */
}