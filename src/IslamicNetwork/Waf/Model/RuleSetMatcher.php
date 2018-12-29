<?php

namespace IslamicNetwork\Waf\Model;

use IslamicNetwork\Waf\Helper\IpHelper;
use IslamicNetwork\Waf\Helper\RateLimitHelper;


class RuleSetMatcher
{
    private $request;
    private $server;
    private $ruleSet;
    private $matched;
    const WHITELIST = 'whitelist';
    const BLACKLIST = 'blacklist';
    const RATELIMIT = 'ratelimit';

    public function __construct(RuleSet $ruleSet, array $request, array $server)
    {
        $this->request = $request;
        $this->server = $server;
        $this->ruleSet = $ruleSet;
    }

    private function doAllFail(array $value, $toMatch, $headerName): bool
    {
        $res = [];
        foreach ((array)$value as $vx) {
            if (in_array($headerName, IpHelper::getIpHeaders())) {
                // Cache this - it's quite expensive, I think.
                $vx = IpHelper::cidrToIps($vx);
            }
            foreach ((array) $vx as $vy) {
                // Even if one of these CSV values is matched, this particular key is acceptable
                if (strpos($toMatch, $vy) === false) {
                    $res[] = false;
                } else {
                    $res[] = true;
                }
            }
        }

        if (in_array(true, $res)) {
            // All do not fail.
            return false;
        }

        // All failed.
        return true;
    }

    public function isAMatch($rule, $type): bool
    {
        $matchedKeys = 0;
        if (isset($rule['headers']['request'])) {
            foreach ((array)$rule['headers']['request'] as $key => $value) {
                if (isset($this->request[$key])) {
                    $matchedKeys++;
                    $request = (array)$this->request[$key];
                    // Even if one key does not match we assume the rule does not match. Return false.
                    if ($this->doAllFail($value, $request[0], $key)) {
                        return false;
                    }
                }
            }
        }

        if (isset($rule['headers']['server'])) {
            foreach ((array)$rule['headers']['server'] as $key => $value) {
                if (isset($this->server[$key])) {
                    $matchedKeys++;
                    $server = (array)$this->server[$key];
                    // Even if one key does not match we assume the rule does not match. Return false.
                    if ($this->doAllFail($value, $server[0], $key)) {
                        return false;
                    }
                }
            }
        }

        if ($matchedKeys > 0) {
            return true;
        }

        return false;
    }

    private function matchRules($ruleList, $type): bool
    {
        foreach ($ruleList as $rule) {
            // If even one is a match, return true
            if ($this->isAMatch($rule, $type)) {
                $this->matched['name'] = $rule['name'];
                $this->matched['type'] = $type;
                if ($type == self::RATELIMIT) {
                    $this->matched['rate'] = $rule['limit']['rate'];
                    $this->matched['time'] = $rule['limit']['time'];
                }

                return true;
            }
        }

        return false;
    }

    public function getDefaultRateLimitMatch(): array
    {
        // get default rule
        $rule = RateLimitHelper::getDefaultRule($this->ruleSet->getRatelimits());
        $hash = RateLimitHelper::getDefaultRateLimitHash($rule, $this->request, $this->server);

        return [
            'name' => $hash,
            'type' => self::RATELIMIT,
            'rate' => $rule['limit']['rate'],
            'time' => $rule['limit']['time']
        ];

    }

    public function getMatched(): array
    {
        return $this->matched;
    }

    public function isWhitelisted(): bool
    {
        return $this->matchRules($this->ruleSet->getWhitelists(), self::WHITELIST);
    }

    public function isBlacklisted(): bool
    {
        return $this->matchRules($this->ruleSet->getBlacklists(), self::BLACKLIST);
    }

    public function isRatelimited(): bool
    {
        return $this->matchRules($this->ruleSet->getRatelimits(), self::RATELIMIT);
    }



}