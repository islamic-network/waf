<?php
namespace Tests\Unit;

use IslamicNetwork\Waf\Model\RuleSet;
use IslamicNetwork\Waf\Model\RuleSetMatcher;

class BlackListRuleSetMatcherTest extends \PHPUnit\Framework\TestCase
{
    private $ruleSetPath;
    private $ruleSet;
    private $matcher;
    private $request;
    private $server;

    public function setUp()
    {
        $this->ruleSetPath = realpath(__DIR__ . '/../../config/blacklist.yml');
        $this->ruleSet = new RuleSet($this->ruleSetPath);

    }

    public function testBlackListed()
    {
        $this->request = [
            'HTTP_USER_AGENT' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36'],
            'HTTP_X_FORWARDED_FOR' => ['78.99.90.7, 123.456.78.99, 190.678.545.676'],
            'HTTP_X_FORWARDED_PROTO' => ['http']
        ];

        $this->server = [];

        $this->matcher = new RuleSetMatcher($this->ruleSet, $this->request, $this->server);
        $this->assertTrue($this->matcher->isBlacklisted());
        $matched = $this->matcher->getMatched();
        $this->assertEquals('my blacklist', $matched['name']);
    }

    public function testBlackListed4()
    {
        $this->request = [
            'HTTP_USER_AGENT' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36'],
        ];

        $this->server = [
            'REMOTE_ADDR' => ['1.1.1.1']
        ];

        $this->matcher = new RuleSetMatcher($this->ruleSet, $this->request, $this->server);
        $this->assertTrue($this->matcher->isBlacklisted());
        $matched = $this->matcher->getMatched();
        $this->assertEquals('my blacklist', $matched['name']);
    }

    public function testisNotBlackListed()
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

        $this->assertFalse($this->matcher->isBlacklisted());
    }

    public function testBlackListed2()
    {
        $this->request = [
            'HTTP_USER_AGENTA' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36'],
            //'HTTP_X_FORWARDED_FOR' => ['78.99.90.7/22, 124.456.78.99, 190.678.545.676'],
            //'HTTP_X_FORWARDED_PROTO' => ['http']
        ];

        $this->server = [];

        $this->matcher = new RuleSetMatcher($this->ruleSet, $this->request, $this->server);
        $this->assertFalse($this->matcher->isBlacklisted());
    }

}