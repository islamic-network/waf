<?php

namespace IslamicNetwork\Waf\Model;

use IslamicNetwork\Waf\Helper\IpHelper;


class RuleSetMatcher
{
    private $request;
    private $server;
    private $ruleSet;
    private $matched;
    const WHITELIST = 'whitelist';
    const BLACKLIST = 'blacklist';
    const RATELIMIT = 'ratelimit';

    public function __construct(RuleSet $ruleSet, $request, $server)
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
            foreach ((array)$rule['headers']['request'] as $key => $value) {
                if (isset($this->request[$key])) {
                    $matchedKeys++;

                    // Even if one key does not match we assume the rule does not match. Return false.
                    if ($this->doAllFail($value, $this->request[$key][0], $key)) {
                        return false;
                    }
                }
            }

            foreach ((array)$rule['headers']['server'] as $key => $value) {
                if (isset($this->server[$key])) {
                    $matchedKeys++;
                    // Even if one key does not match we assume the rule does not match. Return false.
                    if ($this->doAllFail($value, $this->server[$key][0])) {
                        return false;
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