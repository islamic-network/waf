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

    public function isAMatch($rule, $type)
    {
        foreach ($rule['headers']['request'] as $key => $value) {
            if (isset($this->request[$key])) {
                // If even one property of the rule does not exist in the request, it does not match, return false.
                if(strpos($this->request[$key], $value) === false) {
                    return false;
                }
            }
        }

        foreach ($rule['headers']['server'] as $key => $value) {
            if (isset($this->server[$key])) {
                // If even one property of the rule does not exist in the server, it does not match, return false.
                if(strpos($this->server[$key], $value) === false) {
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