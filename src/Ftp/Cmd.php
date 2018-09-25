<?php

namespace Jiny\Lamp\Ftp;

use \Jiny\Core\Registry\Registry;

trait Cmd
{
    
    /**
     * FTP 디렉토리를 생성합니다.
     */
    public function mkdir($argv)
    {
        if (isset($argv[2])) {
            echo "디렉토리를 생성 합니다.\n";
            $path = $argv[2];
            $this->_mkdir($path);           

            echo "\n";
        } else {
            echo "생성할 디렉토리가 없습니다..\n";
        }        
    }

    /**
     * 디렉토리 생성 구현체
     */
    public function _mkdir($path)
    {
        if (ftp_mkdir($this->_conn, $path)) {
            echo "successfully created ".$path;
        } else {
            echo "There was a problem while creating ".$path;
        }
    }

    /**
     * 디렉토리를 삭제합니다.
     */
    public function rmdir($path)
    {
        if (isset($argv[2])) {
            echo "디렉토리를 삭제 합니다.\n";
            $path = $argv[2];

            if (ftp_rmdir($this->_conn, $path)) {
                echo "successfully removed ".$path."\n";
            } else {
                echo "There was a problem while removing ".$path."\n";
            }

        } else {
            echo "삭제할 디렉토리가 없습니다..\n";
        }   
    }


    /**
     * 파일을 업로드 합니다.
     */
    public function upload($argv)
    {
        if (isset($argv[2])) {
            $src = $argv[2];
            $file = basename($src);
            echo $src."=>".$file;
            $this->_upload($src, $file);            

            echo "\n";
        } else {
            echo "업로드 파일명이 없습니다.\n";
        }
        
    }

    /**
     * 실제 업로드 구현체
     */
    public function _upload($src, $file)
    {
        $fp = fopen($src, 'r');
        // try to upload $file
        if (ftp_fput($this->_conn, $file, $fp, FTP_BINARY)) {
            echo "\t Successfully uploaded";
        } else {
            echo "\t There was a problem while uploading";
        }

        fclose($fp);
    }

    /**
     * ftp 설정파일을 복사합니다.
     */
    public function init()
    {
        // Page 3.284
        if (file_exists(".ftpconfig.php")) {
            echo "ftp 설정파일이 존재합니다.";

        } else {
            $file = "./vendor/jiny/lamp/.ftpconfig.php";
            if (file_exists($file)) {
                if (copy($file, ".ftpconfig.php")) {
                    echo "기본 설정파일(.ftpconfig.php)을 복사합니다.\n";
                } else {
                    echo "설정파일 복사를 할 수 없습니다.\n";
                }
            } else {
                echo "수동으로 생성해 주세요.\n";
            }

            $file = "./vendor/jiny/lamp/.ftpignore";
            if (file_exists($file)) {
                if (copy($file, ".ftpignore")) {
                    echo "기본 설정파일(.ftpignore)을 복사합니다.\n";
                } else {
                    echo "설정파일 복사를 할 수 없습니다.\n";
                }
            } else {
                echo "수동으로 생성해 주세요.\n";
            }


        }
    }



    public function rm($argv)
    {
        if (isset($argv[2])) {
            echo "파일을 삭제 합니다.\n";
            if (ftp_delete($this->_conn, $argv[2])) {
                echo $argv[2]." deleted successful\n";
            } else {
                echo "could not delete ".$argv[2]."\n";
            }

        } else {
            echo "삭제할 파일명이 없습니다..\n";
        }        
    }

    public function ls($argv)
    {
        if (isset($argv[2])) {
            echo "목록을 출력합니다.\n";
            $path = $argv[2];

            $arr = ftp_rawlist($this->_conn, $path);
            foreach ($arr as $value) {
                echo $value."\n";
            }

        } else {
            echo "서버 폴더 경로명이 없습니다.\n";
        }        
    }

    /**
     * Class End
     */
}