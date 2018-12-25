<?php
namespace Tests\Unit;

use IslamicNetwork\Waf\Cacher\Memcached;
use IslamicNetwork\Waf\Model\RateLimit;
use IslamicNetwork\Waf\Model\RuleSet;
use IslamicNetwork\Waf\Model\RuleSetMatcher;

class RateLimitRuleSetMatcherTest extends \PHPUnit\Framework\TestCase
{
    private $ruleSetPath;
    private $ruleSet;
    private $matcher;
    private $request;
    private $server;

    public function setUp()
    {
        $this->ruleSetPath = realpath(__DIR__ . '/../../config/ratelimit.yml');
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

        $mc = new Memcached('127.0.0.1', 11211);
        //$rl = new RateLimit($mc,  $matched['name'], $matched['rate'], $matched['time']);
        $rl = new RateLimit($mc,  $matched['name'], 10, 10);

        // Should we limit this yet?
        for($i=1; $i<=10; $i++) {
            $this->assertFalse($rl->isLimited());
        }

        $this->assertTrue($rl->isLimited());

        sleep(11);

        $this->assertFalse($rl->isLimited());


    }


}