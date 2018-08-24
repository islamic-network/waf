<?php

namespace IslamicNetwork\Waf\Model;

use Symfony\Component\Yaml\Yaml;


class RuleSet
{
    private $ruleSetFile;
    private $ruleSets;

    /**
     * Constructor
     *
     * @param string $pathToPropertyFolder Without trailing slash. Example: /my/site/path
     */
    public function __construct($ruleSetFile)
    {

            $this->ruleSetFile = $ruleSetFile;
            $this->load();
    }


    private function load()
    {
        try {
            $this->ruleSets = Yaml::Parse(file_get_contents($this->ruleSetFile));
        } catch (\Exception $e){
            // TODO: Log an exception or do something else;
        }
    }

    public function getAll()
    {
        return $this->ruleSets;
    }

    public function getBlacklists()
    {
        $this->normaliseHeaderNames($this->ruleSets['blacklist']);

        // TODO: validate - make sure it has a name

        return $this->ruleSets['blacklist'];
    }

    public function getWhitelists()
    {
        $this->normaliseHeaderNames($this->ruleSets['whitelist']);

        // TODO: validate - make sure it has a name

        return $this->ruleSets['whitelist'];
    }

    public function getRatelimits()
    {
        $this->normaliseHeaderNames($this->ruleSets['ratelimit']);

        // TODO: Validate these - make sure limit is set with 2 ints.

        return $this->ruleSets['ratelimit'];
    }

    private function normaliseHeaderNames(array &$ruleset)
    {
        //if (isset($rs['headers']['request'])) {
            foreach ($ruleset as $rsKey => $rs) {
                foreach ($rs['headers']['request'] as $key => $value) {
                    unset($ruleset[$rsKey]['headers']['request'][$key]);
                    $ruleset[$rsKey]['headers']['request']['HTTP_' . str_replace('-', '_', strtoupper($key))] = $value;
                }
            }
       // }

        if (isset($rs['headers']['server'])) {
                foreach ($rs['headers']['server'] as $key => $value) {
                    unset($ruleset[$rsKey]['headers']['server'][$key]);
                    $ruleset[$rsKey]['headers']['server'][str_replace('-', '_', strtoupper($key))] = $value;
                }
            }
    }

}