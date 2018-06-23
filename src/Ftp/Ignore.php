<?php

namespace Jiny\Lamp\Ftp;

use \Jiny\Core\Registry\Registry;

trait Ignore
{
    public function isIgnore($dir, $value)
    {
		if($dir == ".") $path = $value;
		else $path = $dir."/".$value;

        return $this->ignoreMask($path, $this->_ignore);
    }

    public function ignoreData($filename = ".ftpignore")
    {
        // 데이터를 읽어옵니다.
        if($fp = @fopen($filename,"r")){
            $i=0;
            while(($buffer = fgets($fp)) !== false){
                $buffer = trim($buffer, "\n\r");
                if($buffer) $data[$i++] = $buffer;
            }
            fclose($fp);       
        }
        
        return $data;
    }

    /**
	 * Matches filename against patterns.
	 * @param  string   $path  relative path
	 * @param  string[]  $patterns
	 */
	public static function ignoreMask(string $path, array $patterns, bool $isDir = false): bool
	{
		$res = false;
		$path = explode('/', ltrim($path, '/'));
		foreach ($patterns as $pattern) {
			$pattern = strtr($pattern, '\\', '/');
			if ($neg = substr($pattern, 0, 1) === '!') {
				$pattern = substr($pattern, 1);
			}

			if (strpos($pattern, '/') === false) { // no slash means base name
				if (fnmatch($pattern, end($path), FNM_CASEFOLD)) {
					$res = !$neg;
				}
				continue;

			} elseif (substr($pattern, -1) === '/') { // trailing slash means directory
				$pattern = trim($pattern, '/');
				if (!$isDir && count($path) <= count(explode('/', $pattern))) {
					continue;
				}
			}

			$parts = explode('/', ltrim($pattern, '/'));
			if (fnmatch(
				implode('/', $neg && $isDir ? array_slice($parts, 0, count($path)) : $parts),
				implode('/', array_slice($path, 0, count($parts))),
				FNM_CASEFOLD | FNM_PATHNAME
			)) {
				$res = !$neg;
			}
		}
		return $res;
	}
    
}