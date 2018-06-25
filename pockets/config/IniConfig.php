<?php

namespace Core\pockets\config;

use Piwik\Ini\IniReader;

class IniConfig
{
    const CONFIG_PATH = "/home/resource_config/";

    const CONFIG_FILE_SUFFIX = ".ini";

    private static $iniReader = NULL;

    private static $fileHandle = [];

    private static function getSingleton()
    {
        if (!self::$iniReader) {
            self::$iniReader = new IniReader();
        }
        return self::$iniReader;
    }

    private static function getHandleKey($name)
    {
        return md5($name);
    }

    public static function getConfigFile($fileName)
    {
        if ($fileName) {
            $fileHash = self::getHandleKey($fileName);
            if (!isset(self::$fileHandle[$fileHash])) {
                $iniReader = self::getSingleton();
                self::$fileHandle[$fileHash] = $iniReader;
            }
            return self::$fileHandle[$fileHash];
        } else {
            throw new \Exception('请先设置配置文件');
        }
    }

    public static function getConfigSelect($fileName, $section)
    {
        $fileConfig = self::getConfigFile($fileName);
        if (isset($fileConfig[$section])) {
            return $fileConfig[$section];
        } else {
            throw new \Exception($fileName . "-" . $section . "-配置项不存在，请检查配置文件");
        }
    }

    public static function getConfigKey($fileName, $section, $key)
    {
        $sectionConfig = self::getConfigSelect($fileName, $section);
        if (isset($sectionConfig[$key])) {
            return $sectionConfig[$key];
        } else {
            throw new \Exception($fileName . "-" . $section . "-配置值不存在，请检查配置文件");
        }
    }
}