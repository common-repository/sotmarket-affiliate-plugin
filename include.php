<?php

define('ERROR_STATUS_INFO', 10);
define('ERROR_STATUS_CONFIG', 20);
define('ERROR_STATUS_RPC', 30);

define('SOTMARKET_TRANS_DOMAIN', 'sotmarket_trans_domain');

/**
 *
 * @copyright   Copyright (c) 2013, SOTMARKET.RU
 * @version     3.0.5 от 11.04.2013
 * @author      автор плагина ( k-v-n@inbox.ru )
 */
 
if (!function_exists('sotmarketAutoload')) {
    function sotmarketAutoload($className)
    {
        static $aIncludePath = array();

        if (empty($aIncludePath)) {
            $aIniPath = array();
            $aIniPath[] = '.';
            $aIniPath = array_merge($aIniPath, explode(PATH_SEPARATOR, get_include_path()));
            $aIniPath = array_unique($aIniPath);
            $sBaseDirs = ini_get('open_basedir');

            if (empty($sBaseDirs)) {
                $aIncludePath = array_values($aIniPath);
            } else {
                $aIncludePath = array();
                $aBaseDirPath[] = '.';
                $aBaseDirPath = array_merge($aBaseDirPath, explode(PATH_SEPARATOR, $sBaseDirs));
                $aBaseDirPath = array_unique($aBaseDirPath);
                foreach ($aBaseDirPath as $sBasePath) {
                    foreach ($aIniPath as $sPath) {
                        if (empty($sBasePath)) continue;
                        if (strpos($sPath, $sBasePath) !== false) {
                            $aIncludePath[] = $sPath;
                        }
                    }
                }
            }
        }
        foreach ($aIncludePath as $sIncludePath)
        {
            if (is_file($sIncludePath . "/" . $className . '.php')) {
                require_once($sIncludePath . "/" . $className . '.php');
            }
        }
    }

    spl_autoload_register('sotmarketAutoload');
}


set_include_path(get_include_path()
                 . PATH_SEPARATOR . dirname(__FILE__) . '/classes'
                 . PATH_SEPARATOR . dirname(__FILE__) . '/classes/packages'
);

/**
 * @deprecated
 * @return string
 */
function sotmarket_site_url()
{
	if (defined('WP_SITEURL')){
		return WP_SITEURL;
	}else {
		return home_url().'/';
	}

}

function sotm_encode($string,$key) {
    $key = sha1($key);
    $strLen = strlen($string);
    $keyLen = strlen($key);
    $j = 0;
    $hash = '';
    for ($i = 0; $i < $strLen; $i++) {
        $ordStr = ord(substr($string,$i,1));
        if ($j == $keyLen) { $j = 0; }
        $ordKey = ord(substr($key,$j,1));
        $j++;
        $hash .= strrev(base_convert(dechex($ordStr + $ordKey),16,36));
    }
    return $hash;
}

function sotm_decode($string,$key) {
    $key = sha1($key);
    $strLen = strlen($string);
    $keyLen = strlen($key);
    $j = 0;
    $hash = '';
    for ($i = 0; $i < $strLen; $i+=2) {
        $ordStr = hexdec(base_convert(strrev(substr($string,$i,2)),36,16));
        if ($j == $keyLen) { $j = 0; }
        $ordKey = ord(substr($key,$j,1));
        $j++;
        $hash .= chr($ordStr - $ordKey);
    }
    return $hash;
}

require_once dirname(__FILE__) . '/assert.php';
require_once dirname(__FILE__) . '/dump.php';
?>