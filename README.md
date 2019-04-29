## بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ

[![CircleCI](https://circleci.com/gh/islamic-network/waf.svg?style=shield)](https://circleci.com/gh/islamic-network/waf) 
[![](https://img.shields.io/github/release/islamic-network/waf.svg)](https://github.com/islamic-network/waf/releases)
[![](https://img.shields.io/github/license/islamic-network/waf.svg)](https://github.com/islamic-network/waf/blob/master/LICENSE)

# A WAF for Developers

**This README file is still a work in progress.**

This is a WAF written in PHP.  To configure and use it, you need to know YAML and understand the various parts of an HTTP request.

It is completely dockerised and to deploy it you will need to run a docker command and specify some environment variables.


## Current Status
This project is stable but has a basic feature set. It also gets updates, often, but a breaking change will go in a new major version.

Some of the things to do are listed under [issues](https://github.com/islamic-network/waf/issues).

## Contributions and Support

Pull requests are always welcome. For feature requests, please feel free to raise an issue.

You can also join the Islamic Network Discord Server to discuss the WAF or any of the other apps or APIs @ https://discord.gg/FwUy69M.

## Why was it written?

We needed a WAF for the AlAdhan API.

We tried to use Incapsula and it wasn't something we could afford for the free services offered by [Islamic Network](https://islamic.network).

CloudFlare was good, but it seemed to have been blocked by ISPs in Russia and China (and it doesn't really allow us to write any custom rules for the WAF).

If you've ever tried to use something like ModSecurity, you'll know it's tedious. 

This WAF allows you to write rules in a yaml file - that's much easier to read and write for most developers.

## Who is this for?

For developers looking to deploy a WAF within their apps or outside their API Gateway.

Eventually, we will provide OWASP ruleset files that you can simply include in your installation.

We will also, God willing, offer a hosted service and make the production deployment mechanism open source in due course. 

**The hosted service is currently being trailled.** It basically allows you to manage your ruleset file in a git repo and automatically deploys to your hosted WAF. 

If you'd like to trial this, please email support@islamic.network.

## Why YAML and PHP?

Because they're easy to use, easy to maintain and easy to manage.

# Installation and Usage

This WAF is production ready and can be deployed as a proxy using the provided Dockerfile or docker-compose file.

You can even use the already published docker image at quay.io/islamic-network/waf or islamicnetwork/waf.


## The underlying library and how it works

You'll need to understand some PHP for this section.

To see how the waf processes your YAML file, see the bootstrap/wafMiddleware.php file.

In a nutshell, this is what it does:


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

## Defining Rulesets, Rules and Matchers
The WAF reads a Ruleset YAML file and decides if any of the above code will return true or not.

Let's have a look at the structure of this file.

```yaml
# Note that values separated with a comma are always OR and each of the global keys are always AND
blacklist:
  - name: my blacklist # required
    headers: # required
      request: # required.HTTP_ appended
        X-FORWARDED_FOR: [123.456.78.9, 78.99.90.3]
        FORWARDED: [123.456.78.9, 78.99.90.3]
        USER-aGENT: [Mozilla/5.0, python-requests/2.8]
      server: # required
        rEQUEST_URI: [path/one, path/two]
        QUERY_STRING: [one=yes&two=no&three=maybe, another=0&someother=1]

whitelist:
  - name: my whitelist # required
    headers: # required
      request: # required HTTP_ appended
          X_FORWARDED_FOR: [123.456.78.9, 78.99.90.3]
          FORWARDED: [123.456.78.9, 78.99.90.3]
          X_FORWARDED: [123.456.78.9, 78.99.90.3]
          X_CLUSTER_CLIENT_IP: [123.456.78.9, 78.99.90.3]
          CLIENT_IP: [123.456.78.9, 78.99.90.3]
          USER_AGENT: [Mozilla/5.0, python-requests/2.8]
          REFERER: [http://something.com, 'something else']
          COOKIES: [cookie_one, another_cookie]
      server: # required
          REQUEST_URI: [path/one, path/two]
          QUERY_STRING: [one=yes&two=no&three=maybe]
ratelimit:
  - name: limiter # required
    headers: # required
      request: # required HTTP_ appended
          X_FORWARDED_FOR: [123.456.78.9, 78.99.90.3]
          FORWARDED: [123.456.78.9, 78.99.90.3]
          X_FORWARDED: [123.456.78.9, 78.99.90.3]
          X_CLUSTER_CLIENT_IP: [123.456.78.9, 78.99.90.3]
          CLIENT_IP: [123.456.78.9, 78.99.90.3]
          USER_AGENT: [Mozilla/5.0, python-requests/2.8]
          REFERER: [http://something.com, 'something else']
          COOKIES: [cookie_one, another_cookie]
      server: # required
          REQUEST_URI: [path/one, path/two]
          QUERY_STRING: [one=yes&two=no&three=maybe]
    limit:
      rate: 1000
      time: 3600 #60 = 1 minute, 3600 = 1 hour, 86400 = 1 day
  - name: another limiter # required
    headers: # required
        request: # required HTTP_ appended
            X_FORWARDED_FOR: [123.456.78.9, 78.99.90.3]
            FORWARDED: [123.456.78.9, 78.99.90.3]
            X_FORWARDED: [123.456.78.9, 78.99.90.3]
            X_CLUSTER_CLIENT_IP: [123.456.78.9, 78.99.90.3]
            CLIENT_IP: [123.456.78.9, 78.99.90.3]
            USER_AGENT: [Mozilla/5.0, python-requests/2.8]
            REFERER: [http://something.com, 'something else']
            COOKIES: [cookie_one, another_cookie]
        server: # required
            REQUEST_URI: [path/one, path/two]
            QUERY_STRING: [one=yes&two=no&three=maybe]
    limit:
      rate: 1000
      time: 3600 #60 = 1 minute, 3600 = 1 hour, 86400 = 1 day
````

## Ruleset

Currently, 3 Rulesets are supported. In the above file, these are:
1. Whitelist
2. Blacklist
3. Ratelimit

### Rule

An instance of a ruleset, is a rule. So in the above Yaml, there is 1 whitelist rule, 1 blacklist rule, and there are 2 ratelimit rules.

A rule comprises a name, matchers (and submatchers) and a message (the message is coming soon). See https://github.com/islamic-network/waf/issues/8.

#### Matcher

Currently, only the 'headers' matcher is supported, and in that you can specify request and server headers to match. Header names can have - or _ and are case agnostic.

A 'body' matcher is in progress. See https://github.com/islamic-network/waf/issues/6.

#### How Matchers Work

Each matcher or submatcher can be an array.

So the blacklist rule 'my blacklist' has a headers matcher which basically reads like this:


```
// The below is pseudo code

if the request header
    x-forwarded-for contains 123.456.78.9 OR 78.99.90.3
    AND
    forwarded contains 123.456.78.9 OR 78.99.90
    AND
    user-agent contains Mozilla/5.0 OR python-requests/2.8
AND the server header contains
    request-uri contains path/one OR path/two
    AND
    query-string contains one=yes&two=no&three=maybe OR another=0&someother=1
THEN
    this rule is matched (isBlacklisted returns true)
ELSE
    this rule is unmatched (isBlacklisted returns false)

```


