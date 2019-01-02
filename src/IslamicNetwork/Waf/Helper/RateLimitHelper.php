<?php

namespace IslamicNetwork\Waf\Helper;


use IslamicNetwork\Waf\Model\RuleSet;
use IslamicNetwork\Waf\Model\RuleSetMatcher;

class RateLimitHelper
{
    public static function getDefaultRule(array $ruleList): array
    {
        foreach ($ruleList as $rule) {
            if (trim($rule['name']) === 'default') {
                return $rule;
            }
        }

        return [];
    }

    public static function getDefaultRateLimitHash(array $rule, array $request, array $server): ?string
    {
        // TODO: Check $rule
        if (isset($rule['headers']['request'])) {
            $ruleKeysRequest = array_keys($rule['headers']['request']);
        }
        if (isset($rule['headers']['server'])) {
            $ruleKeysServer = array_keys($rule['headers']['server']);
        }
        $requestKeys = array_keys($request);
        $serverKeys = array_keys($server);
        // Take the first incoming key and generate a hash of its value
        foreach($ruleKeysRequest as $keyR) {
            if (in_array($keyR, $requestKeys)) {
                return self::hash($request[$keyR]);
            }
        }

        foreach($ruleKeysServer as $keyS) {
            if (in_array($keyS, $serverKeys)) {
                return self::hash($server[$keyS]);
            }
        }

        // hash the entire server request
        return self::hash($server);
    }

    private static function hash($value): string
    {
        if(is_array($value)) {
            return md5(serialize($value));
        }

        return md5((string) $value);
    }

}
