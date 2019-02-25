<?php

namespace Vesica\Waf\Helper;


class IpHelper
{
    /**
     * @param string $cidr
     * @return array
     */
    public static function cidrToRange(string $cidr): array
    {
        $range = array();
        $cidr = explode('/', $cidr);
        $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
        $range[1] = long2ip((ip2long($range[0])) + pow(2, (32 - (int)$cidr[1])) - 1);

        return $range;
    }

    /**
     * @param array $range
     * @return array
     */
    public static function rangeToAddresses(array $range): array
    {
        $a = [];

        for ($ip = ip2long($range[0]); $ip<=ip2long($range[1]); $ip++)
        {
            $a[] = long2ip($ip);
        }

        return $a;

    }

    /**
     * @param string $cidr
     * @return array|string
     */
    public static function cidrToIps(string $cidr)
    {
        if (self::isCidr($cidr)) {
            return self::rangeToAddresses(self::cidrToRange($cidr));
        }

        return $cidr;
    }

    /**
     * @param string $ip
     * @return bool
     */
    public static function isCidr(string $ip): bool
    {
        $x = explode("/", $ip);

        return count($x) > 1;

    }

    /**
     * @return array
     */
    public static function getIpHeaders(): array
    {
        return [
            'HTTP_X_FORWARDED_FOR',
            'X_FORWARDED',
            'FORWARDED',
            'X_CLUSTER_CLIENT_IP',
            'CLIENT_IP',
            'REMOTE_ADDR'
        ];
    }

}
