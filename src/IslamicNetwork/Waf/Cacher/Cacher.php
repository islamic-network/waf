<?php

namespace IslamicNetwork\Waf\Cacher;


interface Cacher
{
    const NAMESPACE = 'WAF';
    public function set($key, $value);

    public function get($key);

    public function exists($key);
}