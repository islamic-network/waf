<?php

namespace IslamicNetwork\Waf\Model\Property;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class RuleSet
{
    public function __construct($pathToRuleSet)
    {
        try {
            return Yaml::parse($pathToRuleSet);
        } catch (ParseException $exception) {
            printf("Error: The RuleSet file $file contains invalid YAML.\nUnable to parse the YAML string: %s\n", $exception->getMessage());
        }
    }
}