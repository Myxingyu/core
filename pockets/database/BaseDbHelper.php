<?php

namespace Core\pockets\database;

use Core\pockets\config\IniConfig;
use Core\tools\Log;
use Medoo\Medoo;

class BaseDbHelper
{
    //mysql默认端口
    const _DEFAULT_MYSQL_PORT_ = 3306;
    //mysql配置文件名
    const _CONFIG_FILE_ = '';
    //mysql配置字段
    const _CONFIG_SELECT_ = '';
    //数据库名
    const _DATABASE_ = '';
    //日志最大行数
    const _LOG_MAX_NUM_ = 1000;

    protected static $tableName = "";

    protected static $singleton = [];

    protected static $lastQuerySingletonKey = "";

    protected static $writeFunctionList = [
        "insert" => true,
        "update" => true,
        "delete" => true,
        "replace" => true
    ];

    protected static $readFunctionList = [
        "select" => true,
        "get" => true,
        "has" => true,
        "count" => true,
        "max" => true,
        "min" => true,
        "avg" => true,
        "sum" => true
    ];

    protected static $transactionFunctionList = [
        "action" => true
    ];

    protected static $debugFunctionList = [
        "debug" => true,
        "error" => true,
        "log" => true,
        "last" => true,
        "id" => true,
        "query" => true
    ];

    protected static function getConnection($master)
    {
        $singletonKey = static::_DATABASE_ . ":" . (int)$master;
        static::$lastQuerySingletonKey = $singletonKey;
        if (isset(self::$singleton[$singletonKey])) {
            if (count(self::$singleton[$singletonKey]->log()) > static::_LOG_MAX_NUM_) {
                unset(self::$singleton[$singletonKey]);
            }
        }
        if (!isset(self::$singleton[$singletonKey])) {
            $dbConfig = self::getConfig($master);
            try {
                self::$singleton[$singletonKey] = new Medoo([
                    'database_type' => 'mysql',
                    'charset' => $dbConfig["charset"],
                    'database_name' => $dbConfig["db"],
                    'server' => $dbConfig["host"],
                    'username' => $dbConfig["user"],
                    'password' => $dbConfig["passwd"],
                    'port' => isset($dbConfig["port"]) ? $dbConfig["port"] : self::_DEFAULE_MYSQL_PORT_
                ]);
            } catch (\Exception $e) {
                Log::db_error_error(static::_CONFIG_FILE_ . "#" . static::_CONFIG_SELECT_ . "#" . $e->getMessage(), $dbConfig);
            }
        }
        return self::$singleton[$singletonKey];
    }

    private static function getConfig($master)
    {
        $config = IniConfig::getConfigSelect(static::_CONFIG_FILE_, static::_CONFIG_SELECT_);
        if (isset($config["rw_separate"]) && $config["rw_separate"]) {
            $hosts = explode(",", $config["host"]);
            if ($master) {
                $config["host"] = $hosts[$config["deploy"] - 1];
            } else {
                unset($hosts[$config["deploy"] - 1]);
                $config["host"] = $hosts[array_rand($hosts)];
            }
        }
        return $config;
    }

    public static function __callstatic($method, $args)
    {
        if (isset(self::$readFunctionList[$method])) {
            $medoo = self::getConnection(false);
            array_unshift($args, static::$tableName);
        } elseif (isset(self::$writeFunctionList[$method])) {
            $medoo = self::getConnection(true);
            array_unshift($args, static::$tableName);
        } elseif (isset(self::$debugFunctionList[$method])) {
            if ($method == 'query') {
                $medoo = self::getConnection(false);
            } elseif (isset(self::$singleton[self::$lastQuerySingletonKey])) {
                $medoo = self::$singleton[self::$lastQuerySingletonKey];
            }
        } elseif (isset(self::$transactionFunctionList[$method])) {
            $medoo = self::getConnection(true);
        } else {
            throw new \Exception("use undefined Medoo function", __LINE__);
        }
        $result = call_user_func_array([
            $medoo,
            $method
        ], $args);
        if ($result === false) {
            $errorInfo = $medoo->error();
            if ($errorInfo[0] != '00000' || $errorInfo[1] !== NULL) {
                $sql = $medoo->last();
                Log::db_error_error($sql, $errorInfo);
            }
        }
        return $result;
    }
}