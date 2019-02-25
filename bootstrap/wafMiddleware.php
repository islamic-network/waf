<?php
use Slim\Http\Request;
use Slim\Http\Response;

/** Invoke Middleware for WAF Checks */
$app->add(function (Request $request, Response $response, $next) {

    // Add headers to request
    $request = $request->withAddedHeader('X-WAF-KEY', getenv('WAF_KEY'));

    $logger = new \Monolog\Logger('AlAdhanApi/WAF');
    $logger->pushHandler( new \Monolog\Handler\StreamHandler('php://stdout', $this->logLevel));
    $logId  = uniqid();
    $wafNamespace = getenv('WAF_PROXY_NAMESPACE');

    $memCached = new \IslamicNetwork\Waf\Cacher\Memcached(
        getenv('MEMCACHED_HOST'),
        getenv('MEMCACHED_PORT'),
        $wafNamespace
    );

    $server = isset($_SERVER) ? $_SERVER : [];

    // Check if rules exist in cache
    $logger->debug($logId . ' Loading WAF Rules from cache.');
    $wafRules = $memCached->get('wafruleset');

    if ($wafRules === null) { // There is nothing in the cache
        $logger->debug($logId . ' WAF Rules not found in cache. Loading from URL.');

        // Load from file
        $wafRules = new \IslamicNetwork\Waf\Model\RuleSet(getenv('WAF_CONFIG_URL'));
        $logger->debug($logId . ' WAF Rules loaded from URL.');

        // Stick them in the cache.
        $logger->debug($logId . ' Storing rules in cache for 5 mins.');
        $memCached->set('wafruleset', $wafRules, (int) getenv('WAF_CONFIG_EXPIRY'));

    }

    if ($wafRules == null || empty($wafRules)) {
        die('Unable to read WAF rules.');
        $logger->error($logId . ' Unable to read WAF Rules from memcached or ' . getenv('WAF_YAML_URL'));
    }
    $waf = new \IslamicNetwork\Waf\Model\RuleSetMatcher($wafRules, $request->getHeaders(), $server);

    $logger->debug($logId . ' Starting WAF Checks.');
    
    $response = $next($request, $response);
    $response = $response->withHeader('X-WAF', getenv('WAF_NAME'));

    if ($waf->isWhitelisted()) {

        $matched = $waf->getMatched();
        $logger->debug($logId . ' Whitelisted. Passing through.', [$matched['name'], $request->getHeaders(), $server]);

        $response = $response->withHeader('X-WAF-STATUS', 'WHITELISTED');

        return $response;

    } elseif ($waf->isBlacklisted()) {

        $matched = $waf->getMatched();
        $logger->debug($logId . ' BLACKLISTED. Blocking.', [$matched['name'], $request->getHeaders(), $server]);
        throw new \Vesica\Waf\Exceptions\BlackListException('Blacklisted');

    } elseif ($waf->isRatelimited()) {

        $matched = $waf->getMatched();
        $logger->debug($logId . ' RATELIMIT MATCHED.', [$matched['name'], $request->getHeaders(), $server]);
        $rl = new \IslamicNetwork\Waf\Model\RateLimit($memCached, $matched['name'], $matched['rate'], $matched['time']);

        if ($rl->isLimited()) {

            $logger->debug($logId . ' RATELIMITED.', [$matched['name'], $request->getHeaders(), $server]);
            throw new \Vesica\Waf\Exceptions\RateLimitException('Ratelimited');

        }

    } else {
        $matched = $waf->getDefaultRateLimitMatch();
        // Check for the default matched rate limit.
        $logger->debug($logId . ' Not Whitelisted or Blacklisted. Starting Default Ratelimit check.', [$matched['name'], $request->getHeaders(), $server]);

        $rl = new \IslamicNetwork\Waf\Model\RateLimit($memCached, $matched['name'], $matched['rate'], $matched['time']);
        if ($rl->isLimited()) {

            $logger->debug($logId . ' DEFAULT RATELIMITED.' . $matched['name'], [$request->getHeaders(), $server]);
            throw new \Vesica\Waf\Exceptions\RateLimitException('Default Ratelimited');

        }
    }

    $logger->debug($logId . ' All clear. Letting request through.', [$matched['name'], $request->getHeaders(), $server]);

    return $response;
});
