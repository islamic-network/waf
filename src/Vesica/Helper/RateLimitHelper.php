<?php

namespace Vesica\Waf\Helper;


use Vesica\Waf\Model\RuleSet;
use Vesica\Waf\Model\RuleSetMatcher;

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
        $ruleKeysRequest = [];
        $ruleKeysServer = [];
        $matchedKeys = [];
        $totalKeysToMatch = 0;

        // TODO: Check $rule
        if (isset($rule['headers']['request'])) {
            $ruleKeysRequest = array_keys($rule['headers']['request']);
        }
        if (isset($rule['headers']['server'])) {
            $ruleKeysServer = array_keys($rule['headers']['server']);
        }
        // Total matched keys.
        $totalKeysToMatch = count($ruleKeysServer) + count($ruleKeysRequest);

        $requestKeys = array_keys($request);
        $serverKeys = array_keys($server);

        // Take the first incoming key and generate a hash of its value
        foreach($ruleKeysRequest as $keyR) {
            if (in_array($keyR, $requestKeys)) {
                $matchedKeys[] = $request[$keyR];
            }
        }

        foreach($ruleKeysServer as $keyS) {
            if (in_array($keyS, $serverKeys)) {
                $matchedKeys[] = $server[$keyS];
            }
        }

        // If matched keys are the same as the total to match, use this to rate limit. Otherwise the whole request
        if (count($matchedKeys) === $totalKeysToMatch) {
            return self::hash($matchedKeys);
        }

        // otherwise hash the entire server request
        return self::hash($request);
    }

    private static function hash($value): string
    {
        if(is_array($value)) {
            return md5(serialize($value));
        }

        return md5((string) $value);
    }

}
