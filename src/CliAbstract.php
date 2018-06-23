<?php

namespace Jiny\Lamp;

abstract class CliAbstract 
{
    const VERSION = "0.0.1";

    public function version()
    {
        echo "version ".VERSION." \n";
    }
    
}

