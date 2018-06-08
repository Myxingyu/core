<?php

namespace Core\tools;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Vectorface\Whip\Whip;

class Log
{
    const DEFAULT_LOG_PATH_PREFIX = "/data/logs_bak";

    const DEFAULT_LOG_DIRECTORY = "Core";

    private static $singleLog = [];

    private static $singleStreamHandler = [];

    private static $singleStreamHandlerPath = [];

    private static $singleFormatter = [];

    private static $levels = [
        'debug' => Logger::DEBUG,
        'info' => Logger::INFO,
        'notice' => Logger::NOTICE,
        'warning' => Logger::WARNING,
        'error' => Logger::ERROR,
        'critical' => Logger::CRITICAL,
        'alert' => Logger::ALERT,
        'emergency' => Logger::EMERGENCY
    ];

    private static $logSetting = [
        'logPath' => "",
        'logSlice' => "daily",
        'expireDay' => 30,
        'logList' => [
            'general' => [
                'dir' => "general/",
                'logLevel' => Logger::DEBUG
            ],
            'db' => [
                'dir' => "db/",
                'logLevel' => Logger::DEBUG
            ],
            'sys' => [
                'dir' => "sys/",
                'logLevel' => Logger::DEBUG
            ],
            'redis' => [
                'dir' => "redis/",
                'logLevel' => Logger::DEBUG
            ],
            'curl' => [
                'dir' => "curl/",
                'logLevel' => Logger::DEBUG
            ],
            'queue' => [
                'dir' => "queue/",
                'logLevel' => Logger::DEBUG
            ],
            'api' => [
                'dir' => "api/",
                'logLevel' => Logger::DEBUG
            ],
            'oss' => [
                'dir' => "oss/",
                'logLevel' => Logger::DEBUG
            ]
        ]
    ];

    private static function getSingleLog($logPath)
    {
        if (!isset(self::$singleLog[$logPath])) {
            self::$singleLog[$logPath] = new Logger($logPath);
        }
        return self::$singleLog[$logPath];
    }

    private static function setLogPath()
    {
        if (isset($_SERVER['APP_ANME'])) {
            self::$logSetting['logPath'] = self::DEFAULT_LOG_PATH_PREFIX . $_SERVER['APP_ANME'] . DIRECTORY_SEPARATOR;
        } else {
            self::$logSetting['logPath'] = self::DEFAULT_LOG_PATH_PREFIX . self::DEFAULT_LOG_DIRECTORY . DIRECTORY_SEPARATOR;
        }
    }

    private static function getSlice()
    {
        switch (self::$logSetting['logSlice']) {
            case "hourly":
                $logSuffix = "-" . date("YmdH") . ".log";
                break;
            case "daily":
                $logSuffix = "-" . date("Ymd") . ".log";
                break;
        }
        return $logSuffix;
    }

    public static function __callStatic($method, $arguments)
    {
        self::setLogPath();
        $methodList = explode("_", strtolower($method));
        if (count($methodList) > 1) {

        }
    }

    private static function getBackTrace($backtrace)
    {
        $callFile = ltrim(str_replace([
            dirname(dirname(__DIR__)),
            '.php',
            '/'
        ], [
            "",
            "",
            "_"
        ], $backtrace[0]['file']), "-");
        return [$callFile, $backtrace[0]['line']];
    }
}