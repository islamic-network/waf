<?php
namespace Tests\Unit;

use Vesica\Cacher\Memcached;
use Vesica\Waf\Helper\RateLimitHelper;
use Vesica\Waf\Model\RateLimit;
use Vesica\Waf\Model\RuleSet;
use Vesica\Waf\Model\RuleSetMatcher;

class RateLimitRuleSetMatcherTest extends \PHPUnit\Framework\TestCase
{
    private $ruleSetPath;
    private $ruleSet;
    private $matcher;
    private $request;
    private $server;

    public function setUp()
    {
        $this->ruleSetPath = realpath(__DIR__ . '/../config-files/ratelimit.yml');
        $this->ruleSet = new RuleSet($this->ruleSetPath);

    }

    public function testRateLimited()
    {
        $this->request = [
            'HTTP_USER_AGENT' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36'],
            'HTTP_X_FORWARDED_FOR' => ['78.99.90.3, 128.098.765.478, 190.678.545.676'],
            'HTTP_X_FORWARDED_PROTO' => ['http']
        ];

        $this->server = [];

        $this->matcher = new RuleSetMatcher($this->ruleSet, $this->request, $this->server);
        $this->assertTrue($this->matcher->isRatelimited());
        $matched = $this->matcher->getMatched();
        $this->assertEquals('limiter', $matched['name']);

        $this->ruleSetPath = realpath(__DIR__ . '/../config-files/ratelimit-no-server.yml');
        $this->ruleSet = new RuleSet($this->ruleSetPath);

        $this->matcher = new RuleSetMatcher($this->ruleSet, $this->request, $this->server);
        $this->assertTrue($this->matcher->isRatelimited());
        $matched = $this->matcher->getMatched();
        $this->assertEquals('limiter', $matched['name']);
    }

    public function testIsNotRateLimited()
    {
        $this->request = [
            'HTTP_USER_AGENT' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36'],
            'HTTP_X_FORWARDED_FOR' => ['190.678.545.676'],
            'HTTP_X_FORWARDED_PROTO' => ['http']
        ];

        $this->server = [
            'REQUEST_URI' => '/v1/methods',
            'QUERY_STRING' => 'one=two&three=4'
        ];

        $this->matcher = new RuleSetMatcher($this->ruleSet, $this->request, $this->server);

        $this->assertFalse($this->matcher->isRatelimited());

        $this->ruleSetPath = realpath(__DIR__ . '/../config-files/ratelimit-no-server.yml');
        $this->ruleSet = new RuleSet($this->ruleSetPath);

        $this->matcher = new RuleSetMatcher($this->ruleSet, $this->request, $this->server);

        $this->assertFalse($this->matcher->isRatelimited());
    }

    public function testDefault()
    {
        $this->request = [
            'HTTP_X_FORWARDED_FOR' => ['78.99.90.3, 128.098.765.478, 190.678.545.676'],
        ];
        $this->server = [];
        $this->matcher = new RuleSetMatcher($this->ruleSet, $this->request, $this->server);
        $matched = $this->matcher->getDefaultRateLimitMatch();

        $mc = new Memcached('127.0.0.1', 11211);
        $rl = new RateLimit($mc, $matched['name'], $matched['rate'], $matched['time']);

        // Should we limit this yet?
        for($i=1; $i<=60; $i++) {
            $this->assertFalse($rl->isLimited());
        }

        $this->assertTrue($rl->isLimited());
    }

    public function testRateLimiting()
    {
        $this->request = [
            'HTTP_USER_AGENT' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36'],
            'HTTP_X_FORWARDED_FOR' => ['78.99.90.3, 128.098.765.478, 190.678.545.676'],
            'HTTP_X_FORWARDED_PROTO' => ['http']
        ];

        $this->server = [];

        $this->matcher = new RuleSetMatcher($this->ruleSet, $this->request, $this->server);

        $this->assertTrue($this->matcher->isRatelimited());

        $matched = $this->matcher->getMatched();

        $mc = new Memcached('127.0.0.1', 11211, 'accountX');
        //$rl = new RateLimit($mc,  $matched['name'], $matched['rate'], $matched['time']);
        $rl = new RateLimit($mc,  $matched['name'], 10, 5);

        // Should we limit this yet?
        for($i=1; $i<=10; $i++) {
            $this->assertFalse($rl->isLimited());
        }

        $this->assertTrue($rl->isLimited());

        sleep(6);

        $this->assertFalse($rl->isLimited());

    }

    public function testRateLimitingHelperGetDefaultRateLimitHash()
    {
        $this->request = [
            'HTTP_USER_AGENT' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36'],
            'HTTP_X_FORWARDED_FOR' => ['78.99.90.3, 128.098.765.478, 190.678.545.676'],
            'HTTP_X_FORWARDED_PROTO' => ['http']
        ];

        $this->server = [];
        $this->ruleSetPath = realpath(__DIR__ . '/../config-files/ratelimit-default-multi.yml');
        $this->ruleSet = new RuleSet($this->ruleSetPath);
        $rule = RateLimitHelper::getDefaultRule($this->ruleSet->getRatelimits());
        $hash = RateLimitHelper::getDefaultRateLimitHash($rule, $this->request, $this->server);

        $matchedKeys[] = ['78.99.90.3, 128.098.765.478, 190.678.545.676'];
        $matchedKeys[] = ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36'];

        $this->assertEquals(md5(serialize($matchedKeys)), $hash);

        $matchedKeys = [];

        $this->ruleSetPath = realpath(__DIR__ . '/../config-files/ratelimit-no-server.yml');
        $this->ruleSet = new RuleSet($this->ruleSetPath);
        $rule = RateLimitHelper::getDefaultRule($this->ruleSet->getRatelimits());
        $hash = RateLimitHelper::getDefaultRateLimitHash($rule, $this->request, $this->server);

        $matchedKeys[] = ['78.99.90.3, 128.098.765.478, 190.678.545.676'];

        $this->assertEquals(md5(serialize($matchedKeys)), $hash);

        $this->ruleSetPath = realpath(__DIR__ . '/../config-files/ratelimit.yml');
        $this->ruleSet = new RuleSet($this->ruleSetPath);
        $rule = RateLimitHelper::getDefaultRule($this->ruleSet->getRatelimits());
        $hash = RateLimitHelper::getDefaultRateLimitHash($rule, $this->request, $this->server);

        $this->assertEquals(md5(serialize($this->request)), $hash);

    }


}
