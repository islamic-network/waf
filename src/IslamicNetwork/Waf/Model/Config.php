<?php

namespace IslamicNetwork\Waf\Model;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class Config
{
    private $pathToConfig;

    public function __construct($pathToConfig)
    {
        $this->pathToConfig = $pathToConfig;
        
    }

    public function get()
    {
        try {
            return Yaml::parse(file_get_contents($this->pathToConfig));
        } catch (ParseException $exception) {
            printf("Error: The Config file $file contains invalid YAML.\nUnable to parse the YAML string: %s\n", $exception->getMessage());
        }
    }
}