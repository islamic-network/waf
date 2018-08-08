<?php
namespace Tests\Unit;

use IslamicNetwork\Waf\Model\RuleSet;

class RuleSetTest extends \PHPUnit\Framework\TestCase
{
    private $ruleSetPath;
    private $ruleSet;

    public function setUp()
    {
        $this->ruleSetPath = realpath(__DIR__ . '/../../config/ruleset.yml');
        $this->ruleSet = new RuleSet($this->ruleSetPath);
    }

    public function testRuleSetFormat()
    {
        $this->assertArrayHasKey('blacklist', $this->ruleSet->getAll());
        $this->assertArrayHasKey('whitelist', $this->ruleSet->getAll());
        $this->assertArrayHasKey('ratelimit', $this->ruleSet->getAll());

    }

    public function testRuleSetCount()
    {
        $this->assertCount(1, $this->ruleSet->getBlacklists());
        $this->assertCount(1, $this->ruleSet->getWhitelists());
        $this->assertCount(2, $this->ruleSet->getRateLimits());
    }

    public function testBlackList()
    {
        $bl = $this->ruleSet->getBlacklists()[0];
        $this->assertEquals('my blacklist', $bl['name']);
        $this->assertArrayHasKey('headers', $bl);
        $this->assertArrayHasKey('request', $bl['headers']);
        $this->assertArrayHasKey('server', $bl['headers']);
        $this->assertArrayHasKey('HTTP_X_FORWARDED_FOR', $bl['headers']['request']);
        $this->assertArrayNotHasKey('X-FORWARDED_FOR', $bl['headers']['request']);
        $this->assertArrayHasKey('HTTP_USER_AGENT', $bl['headers']['request']);
        $this->assertArrayHasKey('REQUEST_URI', $bl['headers']['server']);
        $this->assertArrayNotHasKey('rEQUEST_URI', $bl['headers']['server']);
        $this->assertCount(2, $bl['headers']['request']['HTTP_USER_AGENT']);
        $this->assertCount(2, $bl['headers']['server']['REQUEST_URI']);
    }

    public function testWhiteList()
    {
        $bl = $this->ruleSet->getWhitelists()[0];
        $this->assertEquals('my whitelist', $bl['name']);
        $this->assertArrayHasKey('headers', $bl);
        $this->assertArrayHasKey('request', $bl['headers']);
        $this->assertArrayHasKey('server', $bl['headers']);
        $this->assertArrayHasKey('HTTP_X_FORWARDED_FOR', $bl['headers']['request']);
        $this->assertArrayNotHasKey('X-FORWARDED_FOR', $bl['headers']['request']);
        $this->assertArrayHasKey('HTTP_USER_AGENT', $bl['headers']['request']);
        $this->assertArrayHasKey('REQUEST_URI', $bl['headers']['server']);
        $this->assertArrayNotHasKey('rEQUEST_URI', $bl['headers']['server']);
        $this->assertCount(2, $bl['headers']['request']['HTTP_USER_AGENT']);
        $this->assertCount(2, $bl['headers']['server']['REQUEST_URI']);
    }

    public function testRateLimit()
    {
        $rl = $this->ruleSet->getRatelimits();
        $this->assertCount(2, $rl);
        $bl = $rl[0];
        $this->assertEquals('limiter', $bl['name']);
        $this->assertArrayHasKey('headers', $bl);
        $this->assertArrayHasKey('request', $bl['headers']);
        $this->assertArrayHasKey('server', $bl['headers']);
        $this->assertArrayHasKey('HTTP_X_FORWARDED_FOR', $bl['headers']['request']);
        $this->assertArrayNotHasKey('X-FORWARDED_FOR', $bl['headers']['request']);
        $this->assertArrayHasKey('HTTP_USER_AGENT', $bl['headers']['request']);
        $this->assertArrayHasKey('REQUEST_URI', $bl['headers']['server']);
        $this->assertArrayNotHasKey('rEQUEST_URI', $bl['headers']['server']);
        $this->assertCount(2, $bl['headers']['request']['HTTP_USER_AGENT']);
        $this->assertCount(2, $bl['headers']['server']['REQUEST_URI']);
        $this->assertArrayHasKey('limit', $bl);
        $this->assertCount(2, $bl['limit']);
    }
}