<?php
use Slim\Http\Request;
use Slim\Http\Response;
use Monolog\Handler\StreamHandler;
use Vesica\Cacher\Memcached;
use Vesica\Waf\Model\RuleSet;
use Vesica\Waf\Model\RuleSetMatcher;
use Vesica\Waf\Model\RateLimit;
use Vesica\Waf\Exceptions\BlackListException;
use Vesica\Waf\Exceptions\RateLimitException;
use Monolog\Logger;
use Vesica\Slim\Middleware\Headers\Validate as HeaderValidationMiddleware;

/** Invoke Middleware for WAF Checks */
$app->add(function (Request $request, Response $response, $next) {

    // Add headers to request
    $request = $request->withAddedHeader('X-WAF-KEY', getenv('WAF_KEY'));
    $request = $request->withAddedHeader('User-Agent', 'VesicaWAF/1.0 ');
    if (!empty($request->getHeader('X-LOAD-BALANCER'))) {
        $request = $request->withHeader('X-LOAD-BALANCER', $request->getHeader('X-LOAD-BALANCER'));
    }

    $wafNamespace = getenv('WAF_PROXY_NAMESPACE');
    $logger = new Logger($wafNamespace);
    $logger->pushHandler( new StreamHandler('php://stdout', $this->logLevel));
    $logId  = uniqid();

    $memCached = new Memcached(
        getenv('MEMCACHED_HOST'),
        getenv('MEMCACHED_PORT'),
        $wafNamespace
    );

    $server = isset($_SERVER) ? $_SERVER : [];

    // Load from file
    $wafRules = new RuleSet(getenv('WAF_CONFIG_URL'));
    $logger->debug($logId . ' WAF Rules loaded from URL.');

    if ($wafRules == null || empty($wafRules)) {
        die('Unable to read WAF rules.');
        $logger->error($logId . ' Unable to read WAF Rules from ' . getenv('WAF_CONFIG_URL'));
    }
    $waf = new RuleSetMatcher($wafRules, $request->getHeaders(), $server);

    $logger->debug($logId . ' Starting WAF Checks.');

    $response = $response->withHeader('X-WAF', getenv('WAF_NAME'));

    if ($waf->isWhitelisted()) {

        $matched = $waf->getMatched();
        $logger->debug($logId . ' Whitelisted. Passing through.', [$matched['name'], $request->getHeaders(), $server]);

        $response = $next($request, $response);

        $response = $response->withHeader('X-WAF-STATUS', 'WHITELISTED');

        return $response;

    } elseif ($waf->isBlacklisted()) {

        $matched = $waf->getMatched();
        $logger->debug($logId . ' BLACKLISTED. Blocking.', [$matched['name'], $request->getHeaders(), $server]);
        throw new BlackListException('Blacklisted');

    } elseif ($waf->isRatelimited()) {

        $matched = $waf->getMatched();
        $logger->debug($logId . ' RATELIMIT MATCHED.', [$matched['name'], $request->getHeaders(), $server]);
        $rl = new RateLimit($memCached, $matched['name'], $matched['rate'], $matched['time']);

        if ($rl->isLimited()) {

            $logger->debug($logId . ' RATELIMITED.', [$matched['name'], $request->getHeaders(), $server]);
            throw new RateLimitException('Ratelimited');
        }

    } else {
        $matched = $waf->getDefaultRateLimitMatch();
        // Check for the default matched rate limit.
        $logger->debug($logId . ' Not Whitelisted or Blacklisted. Starting Default Ratelimit check.', [$matched['name'], $request->getHeaders(), $server]);

        $rl = new RateLimit($memCached, $matched['name'], $matched['rate'], $matched['time']);
        if ($rl->isLimited()) {

            $logger->debug($logId . ' DEFAULT RATELIMITED.' . $matched['name'], [$request->getHeaders(), $server]);
            throw new RateLimitException('Default Ratelimited');

        }
    }

    $logger->debug($logId . ' All clear. Letting request through.', [$matched['name'], $request->getHeaders(), $server]);

    $response = $next($request, $response);

    return $response;
});
