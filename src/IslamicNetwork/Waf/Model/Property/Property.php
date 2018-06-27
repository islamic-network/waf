<?php

namespace IslamicNetwork\Waf\Model\Property;
use IslamicNetwork\Waf\Model\Property\Config;
use IslamicNetwork\Waf\Model\RuleSet\RuleSet;


class Property
{
    private $path;
    private $config;
    private $ruleSetFiles;
    private $ruleSets;

    /**
     * Constructor
     *
     * @param string $pathToPropertyFolder Without trailing slash. Example: /my/site/path
     */
    public function __construct($pathToPropertyFolder, $loadFromCache = true)
    {
        if ($loadFromCache) {
            // Try loading from the cache before doing any of this stuff.
            $this->loadFromCache();
        } else {
            $this->path = $pathToPropertyFolder;
            $this->validate();
            $this->loadConfig();
            $this->loadRuleSets();
            $this->saveToCache();
        }
    }

    private function exists()
    {
        return file_exists($this->path);
    }

    private function validate()
    {
        if (!$this->isValid()) {

        }
    }

    private function isValid()
    {
        return $this->exists() 
            && $this->hasConfig()
            && $this->hasDefaultRuleSet()
            && $this->hasRuleSetsFolder();
    }

    private function hasConfig()
    {
        return file_exists($this->path . '/config.yml');
    }

    private function hasDefaultRuleSet()
    {
        return file_exists($this->path . '/default.yml');
    }

    private function hasRuleSetsFolder()
    {
        return file_exists($this->path . '/rulesets');
    }

    private function loadConfig()
    {
        $this->config = new Config($this->path . '/config.yml');
    }

    private function loadRuleSets()
    {
        $this->ruleSets = new \stdClass();
        $this->loadDefaultRuleSet();
    }

    private function loadCustomRuleSets()
    {
        $this->ruleSetFiles = [];
        $this->ruleSets = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->path . '/rulesets'));
        // Add file to list of files
        $this->ruleSetFiles = array_keys(
            array_filter(
                iterator_to_array($iterator), function($file) {
                    if ($file->getExtension === 'yml') {      
                        $this->ruleSets[] = new RuleSet($file->getPathname());
                        return $file->isFile();
                    }
                }
            )
        );

    }

    private function loadDefaultRuleSet()
    {
        $this->ruleSets->default = new RuleSet($this->path . '/default.yml');
    }

    private function areRuleSetsValid()
    {

    }
}