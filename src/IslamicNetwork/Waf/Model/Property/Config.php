<?php

namespace IslamicNetwork\Waf\Model\Property;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class Config
{
    public function __construct($pathToConfig)
    {
        try {
            return Yaml::parse($pathToConfig);
        } catch (ParseException $exception) {
            printf("Error: The Config file $file contains invalid YAML.\nUnable to parse the YAML string: %s\n", $exception->getMessage());
        }
    }
}