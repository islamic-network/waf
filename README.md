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

We will also, God willing, offer a hosted service. If you'd like to trial this, please email support@islamic.network.

## Why YAML and PHP?

# usage
```php
<?php

$ruleset = new RuleSet($file);
$matcher = new \IslamicNetwork\Waf\Model\RuleSetMatcher($ruleset, $_REQUEST, $_SERVER);
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


# Rules

blacklist Response code 403
whitelist
ratelimit (per hour, per day, per minute, per second) Response code 429
patching
redirect
rewrite


## Rules Paramters
ips
countries
user_agents
http_referers
http_cookies
url_paths
headers
query_strings
request_body

## Patching Parameters:
headers (inject, modify, delete)
query_strings (inject, modify, delete)
reqest_body (inject, modify, delete)




# Order of execution:

Rules:
whitelist
blacklist
ratelimit
patching
redirect
rewrite
