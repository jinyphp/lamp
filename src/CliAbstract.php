<?php
/*
 * This file is part of the jinyPHP package.
 *
 * (c) hojinlee <infohojin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Jiny\Lamp;

abstract class CliAbstract 
{
    const VERSION = "0.1.0";

    public function version()
    {
        echo "version ".VERSION." \n";
    }

    /**
     * 
     */
}

