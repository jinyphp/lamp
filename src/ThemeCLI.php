<?php
namespace Jiny\Lamp;

use Sunra\PhpSimple\HtmlDomParser;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use \Jiny\Core\Registry;

class ThemeCLI
{
    public $CLI;
    public function __construct($cli)
    {
        echo __CLASS__."를 생성합니다.\n";
        $this->CLI = $cli;
    }

    public function __invoke()
    {
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

    public function set($name)
    {
        echo __METHOD__."<br>\n";

        $str = \file_get_contents("./theme/theme.json");
        $themes = \json_decode($str);

        foreach ($themes as $key =>$obj) {
            echo $key."\n";
        }

   print_r($themes);

        echo "\n name=".$key;
        if( isset( $themes[$key] ) ) {
            echo "테마가 존재합니다.\n";
        } else {
            echo "테마가 존재하지 않습니다.\n";
        }   

    }

    public function make($name=NULL)
    {
        echo __METHOD__."<br>";
        if ($name) {
            if (!is_dir("./theme/".$name)) {
                mkdir("./theme/".$name);
            }
        }
        
    }

    public function geturl($argv)
    {
        if (!isset($argv[2])) {
            echo "테마명이 없습니다.\n";
            return;
        } else {
            if (!is_dir("./theme/".$argv[2])) {
                echo "테마명 폴더가 없습니다. 먼저 생성해 주세요.\n";
                return;
            }                   
        }

        if ($argv[3]) {
            echo $argv[3]." 를 다운로드 합니다.";
            $this->CLI->CURL->download($argv[3], "./theme/".$argv[2]."/temp.htm");           
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
            // 이미지를 다운로드 합니다.
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

    public function theme()
    {
        echo __METHOD__."<br>";
        
    }
    public function downImg($arr, $argv)
    {
        echo __METHOD__."\n";
        foreach($arr as $element) {
            $uri = parse_url($argv[3]);

            switch ($this->srcType($element->src)) {
                case '/':
                    echo "절대경로에서 다운로드 합니다. ";
                    echo $element->src." \n";

                    $u = $uri['scheme']."://".$uri['host'].$element->src;
                    echo $u."\n";

                    // 3권 p.257
                    // 파일경로를 분석합니다.
                    $path = pathinfo($element->src);
                    print_r($path);
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

                    // 3권 p.257
                    // 파일경로를 분석합니다.
                    $path = pathinfo($element->src);
                    print_r($path);
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

    public function downCSS($arr, $argv)
    {
        echo __METHOD__."\n";
        foreach($arr as $element) {
            if($element->rel == "stylesheet"){

                $uri = parse_url($argv[3]);

                switch ($this->srcType($element->href)) {
                    case '/':
                        echo "절대경로에서 다운로드 합니다. ";
                        echo $element->href." \n";        
                        $u = $uri['scheme']."://".$uri['host'].$element->href;
                        echo $u."\n";
        
                        // 3권 p.257
                        // 파일경로를 분석합니다.
                        $path = pathinfo($element->href);
                        print_r($path);
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
                        print_r($path);
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

    public function srcType($string)
    {
        $url = parse_url($string);
        if(isset($url['scheme'])){
            echo $string."== 스킵\n";
            if ($url['scheme'] == "http" || $url['scheme'] == "https") {
            }
        } else if($string[0] == "/") {
            echo $string."== 절대경로\n";
            return "/";     

        } else {
            echo $string."== 상대경로\n";
            return ".";
        }

    }

    public function beatifier($str)
    {
        $str = str_replace(";",";\n", $str);
        $str = str_replace("}","\n}\n\n", $str);
        $str = str_replace("{"," {\n", $str);

        $str = str_replace("@"," \n@", $str);

        return $str;
    }
}