<?php

namespace IslamicNetwork\Waf\Model;


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

    private function doAllFail(array $value, $toMatch)
    {
        $res = [];
        foreach ((array)$value as $vx) {
            // Even if one of these CSV values is matched, this particular key is acceptable
            if (strpos($toMatch, $vx) === false) {
                $res[] = false;
            } else {
                $res[] = true;
            }
        }

        if (in_array(true, $res)) {
            // All do not fail.
            return false;
        }

        // All failed.
        return true;
    }

    public function isAMatch($rule, $type)
    {

        // TODO: This won't work to match CIDR IP entries (it will only work for IP addresses currently). See answer by Samuel Parkinson on https://stackoverflow.com/questions/594112/matching-an-ip-to-a-cidr-mask-in-php-5
        // to see how to deal with this issue.
            foreach ((array)$rule['headers']['request'] as $key => $value) {
                if (isset($this->request[$key])) {
                    // Even if one key entirely fails, return false
                    if ($this->doAllFail($value, $this->request[$key][0])) {
                        return false;
                    }
                }
            }

            foreach ((array)$rule['headers']['server'] as $key => $value) {
                if (isset($this->server[$key])) {
                    // Even if one key entirely fails, return false
                    if ($this->doAllFail($value, $this->server[$key][0])) {
                        return false;
                    }
                }
            }

        return true;
    }

    private function matchRules($ruleList, $type)
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

    public function getMatched()
    {
        return $this->matched;
    }

    public function isWhitelisted()
    {
        return $this->matchRules($this->ruleSet->getWhitelists(), self::WHITELIST);
    }

    public function isBlacklisted()
    {
        return $this->matchRules($this->ruleSet->getBlacklists(), self::BLACKLIST);
    }

    public function isRatelimited()
    {
        return $this->matchRules($this->ruleSet->getRatelimits(), self::RATELIMIT);
    }



}