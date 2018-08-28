# A Work in Progress - Use at your own risk

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
