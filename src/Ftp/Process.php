<?php

namespace Jiny\Lamp\Ftp;

use \Jiny\Core\Registry\Registry;

trait Process
{
    public function process()
    {
        echo "FTP process command\n";

        switch($this->CLI->_command){
            case 'ls':
                if (isset($this->CLI->_argv[2])) {
                    $this->ls($this->CLI->_argv[2]);
                } else {
                    echo "서버 폴더 경로명이 없습니다.\n";
                }
                break;
            case 'upload':
                echo "파일을 업로드 합니다.\n";
                if (isset($this->CLI->_argv[2])) {
                    $src = $this->CLI->_argv[2];
                    $file = basename($src);
                    echo $src."=>".$file;
                    $this->upload($src, $file);
                    echo "\n";
                } else {
                    echo "업로드 파일명이 없습니다.\n";
                }                
                break;
            case 'rm':
                echo "파일을 삭제 합니다.\n";
                if (isset($this->CLI->_argv[2])) {
                    $this->rm($this->CLI->_argv[2]);
                } else {
                    echo "삭제할 파일명이 없습니다..\n";
                }
                break;

            case 'mkdir':
                echo "디렉토리를 생성 합니다.\n";
                if (isset($this->CLI->_argv[2])) {
                    $this->mkdir($this->CLI->_argv[2]);
                } else {
                    echo "생성할 디렉토리가 없습니다..\n";
                }
                break;
            
            case 'rmdir':
                echo "디렉토리를 삭제 합니다.\n";
                if (isset($this->CLI->_argv[2])) {
                    $this->rmdir($this->CLI->_argv[2]);
                } else {
                    echo "삭제할 디렉토리가 없습니다..\n";
                }
                break;   
        }
    }

    public function rm($file)
    {
        if (ftp_delete($this->_conn, $file)) {
            echo "$file deleted successful\n";
        } else {
            echo "could not delete $file\n";
        }
    }

    public function ls($path=".")
    {
        $arr = ftp_rawlist($this->_conn, $path);
        foreach ($arr as $value) {
            echo $value."\n";
        }
    }
}