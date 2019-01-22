## بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ

[![CircleCI](https://circleci.com/gh/islamic-network/waf.svg?style=svg)](https://circleci.com/gh/islamic-network/waf) 

# A WAF for Developers

This is a WAF written in PHP. 

You can either use it with your app or deploy it with a proxy.


## Current Status
This project is still in Alpha mode. Some of the things to do are listed under [issues](https://github.com/islamic-network/waf/issues).

## Why was it written?

We needed a WAF for the AlAdhan API.

We tried to use Incapsula and it wasn't something we could afford for the free services offered by [Islamic Network](https://islamic.network).

CloudFlare was good, but it seemed to have been blocked by ISPs in Russia and China (and it doesn't really allow us to write any custom rules for the WAF).

If you've ever tried to use something like ModSecurity, you'll know it's tedious. 

This WAF allows you to write rules in a yaml file - that's much easier to read and write for most developers.

## Who is this for?

For developers looking to deploy a WAF within their apps or outside their API Gateway.

Eventually, we will provide OWASP ruleset files that you can simply include in your installation.

We will also, God willing, offer a hosted service. 

**This is currently being trailled.** It basically allows you to manage your ruleset file in a git repo and automatically deploys to your hosted WAF. 

If you'd like to trial this, please email support@islamic.network.

## Why YAML and PHP?

Because they're easy to use, easy to maintain and easy to manage.

# Installation and Usage
To install, run ```composer require islamic-network/waf```.

You can then use this in your app using the following:

```php
<?php

use IslamicNetwork\Waf\Model\RuleSet;
use IslamicNetwork\Waf\Model\RuleSetMatcher;
use Slim\Http\Request; // Or any other PSR7 Compliant http request object


$ruleset = new RuleSet($filePath);
$matcher = new RuleSetMatcher($ruleset, $request->getHeaders(), $_SERVER);

if ($matcher->isWhitelisted()) {
    // Do nothing. Maybe append headers.
}
if ($matcher->isBlacklisted()) {
    // Throw http 403
}

if ($matcher->isRatelimited()) {
    $rl = new \IslamicNetwork\Waf\Model\RateLimit($memcached, $matcher->getMatched()['rate'], $matcher->getMatched()['time']);
    if ($rl->isLimited()) {
        // Throw http 429
    }
    
}

```
