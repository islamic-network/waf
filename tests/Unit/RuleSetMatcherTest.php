<?php
namespace Tests\Unit;

use IslamicNetwork\Waf\Model\RuleSet;
use IslamicNetwork\Waf\Model\RuleSetMatcher;

class RuleSetMatcherTest extends \PHPUnit\Framework\TestCase
{
    private $ruleSetPath;
    private $ruleSet;
    private $matcher;
    private $request;
    private $server;


    public function testWhiteListed()
    {
        $this->ruleSetPath = realpath(__DIR__ . '/../../config/whitelist.yml');
        $this->ruleSet = new RuleSet($this->ruleSetPath);
        $this->request = [
            'HTTP_USER_AGENT' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36'],
            'HTTP_X_FORWARDED_FOR' => ['78.99.90.3, 128.098.765.478, 190.678.545.676'],
            'HTTP_X_FORWARDED_PROTO' => ['http']
        ];

        $this->server = [
            'REQUEST_URI' => '/v1/methods',
            'QUERY_STRING' => 'one=two&three=4'
        ];

        $this->matcher = new RuleSetMatcher($this->ruleSet, $this->request, $this->server);
        $this->assertTrue($this->matcher->isWhiteListed());
    }

    public function testBlackListed()
    {
        $this->ruleSetPath = realpath(__DIR__ . '/../../config/blacklist.yml');
        $this->ruleSet = new RuleSet($this->ruleSetPath);
        $this->request = [
            'HTTP_USER_AGENT' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36'],
            'HTTP_X_FORWARDED_FOR' => ['78.99.90.3, 128.098.765.478, 190.678.545.676'],
            'HTTP_X_FORWARDED_PROTO' => ['http']
        ];

        $this->server = [
            'REQUEST_URI' => '/v1/methods',
            'QUERY_STRING' => 'one=two&three=4'
        ];

        $this->matcher = new RuleSetMatcher($this->ruleSet, $this->request, $this->server);
        $this->assertTrue($this->matcher->isBlacklisted());
    }

    public function testNotWhiteListed()
    {
        $this->ruleSetPath = realpath(__DIR__ . '/../../config/whitelist.yml');
        $this->ruleSet = new RuleSet($this->ruleSetPath);
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

        $this->assertFalse($this->matcher->isWhiteListed());
    }

    public function testNotBlackListed()
    {
        $this->ruleSetPath = realpath(__DIR__ . '/../../config/blacklist.yml');
        $this->ruleSet = new RuleSet($this->ruleSetPath);
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


}