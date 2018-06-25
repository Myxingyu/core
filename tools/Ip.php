<?php

namespace Core\tools;

use Vectorface\Whip\Whip;
use Vectorface\Whip\IpRange\IpWhitelist;

class Ip
{
    private static $queueWhiteList = [
        'ipv4' => []
    ];

    private static $clientIp = NULL;

    public static function getClientIp()
    {
        if (is_null(self::$clientIp)) {
            $whip = new Whip(Whip::PROXY_HEADERS);
            $clientAddress = $whip->getValidIpAddress();
            if ($clientAddress === false) {
                $whip = new Whip(Whip::REMOTE_ADDR);
                $clientAddress = $whip->getValidIpAddress();
            }
            if ($clientAddress === false) {
                self::$clientIp = "";
            } else {
                self::$clientIp = $clientAddress;
            }
        }
        return self::$clientIp;
    }

    public static function getQueueClientIP()
    {
        $clientIp = self::getClientIp();
        $range = new IpWhitelist(self::$queueWhiteList);
        if ($range->isIpWhitelisted($clientIp)) {
            return $clientIp;
        } else {
            return "";
        }
    }
}