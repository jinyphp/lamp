<?php

namespace Jiny\Lamp\Ftp;

use \Jiny\Core\Registry\Registry;

trait Process
{
    /**
     * 명령을 수행합니다.
     * 각각의 명령은 메소드로 작성되어 있습니다.
     */
    public function process()
    {

        switch($this->CLI->_command){
        
            
            
            default:
                // 그외명령어 처리
                if ($this->CLI->_command) {
                    if (method_exists($this, $this->CLI->_command)) {
                        $method = $this->CLI->_command;
                        $this->$method($this->CLI->_argv);
                    } else {
                        echo "존재하지 않는 명령어 입니다.";
                    }
                }

        }
        
    }

    /**
     * 
     */    
}