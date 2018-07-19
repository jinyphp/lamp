<?php

namespace Jiny\Lamp\Ftp;

use \Jiny\Core\Registry\Registry;

trait Cmd
{
    /**
     * FTP 디렉토리를 변경합니다.
     */
    public function cd($path)
    {
        if (@ftp_chdir($this->_conn, $path)) {
            echo "Current directory is now: " . ftp_pwd($this->_conn) . "\n";
            return true;
        } else { 
            echo "Couldn't change directory\n";
            return false;
        }
    }

    /**
     * FTP 디렉토리를 변경합니다.
     * 디렉토리가 없는 경우 생성을 합니다.
     */
    public function mkcd($path)
    {      
        $dir = \explode("/",$path);     
        foreach ($dir as $value) {       
            if ($value) {
                //echo ">>>>>>".$value."\n";
                if (@ftp_chdir($this->_conn, $value)) {
                    echo "Current directory is now: " . ftp_pwd($this->_conn) . "\n";
                } else { 
                    // echo "Couldn't change directory ";
                    $this->mkdir($value);
                    ftp_chdir($this->_conn, $value);
                    echo "Current directory is now: " . ftp_pwd($this->_conn) . "\n";
                } 
            }               
        }
    }

    /**
     * FTP 디렉토리를 생성합니다.
     */
    public function mkdir($path)
    {
        if (ftp_mkdir($this->_conn, $path)) {
            echo "successfully created ".$path."\n";
        } else {
            echo "There was a problem while creating ".$path."\n";
        }
    }

    public function rmdir($path)
    {
        if (ftp_rmdir($this->_conn, $path)) {
            echo "successfully removed ".$path;
        } else {
            echo "There was a problem while removing ".$path;
        }
    }

    /**
     * FTP 현재의 경로를 반환합니다.
     */
    public function pwd()
    {
        return ftp_pwd($this->_conn);
    }

    /**
     * FTP 상위 디렉토리 이동합니다.
     */
    public function up()
    {
        if (ftp_cdup($this->_conn)) { 
            //echo "cdup successful\n";
        } else {
            //echo "cdup not successful\n";
        }
    }

    public function upload($src, $file)
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
     * Class End
     */
}