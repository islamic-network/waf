<?php
namespace Tests\Unit;

use IslamicNetwork\Waf\Model\RuleSet;

class RuleSetMatcherTest extends \PHPUnit\Framework\TestCase
{
    private $ruleSetPath;
    private $ruleSet;
    private $request;
    private $server;

    public function setUp()
    {
        $this->ruleSetPath = realpath(__DIR__ . '/../../config/ruleset.yml');
        $this->ruleSet = new RuleSet($this->ruleSetPath);
        $this->request = [
            'Host' => ['somesite.com'],
            'HTTP_CACHE_CONTROL' => ['max-age=0'],
            'HTTP_USER_AGENT' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36'],
            'HTTP_COOKIE' => ['__cfduid=da485f2a3b010fd12e5f0e00242564af315305346; _ga=GA1.2.403375278.1530098001; _gid=GA1.2.180041647.1533659599'],
            'HTTP_X_FORWARDED_FOR' => ['78.99.90.3, 128.098.765,478, 190.678.545.676'],
            'HTTP_X_FORWARDED_PROTO' => ['http']
        ];

        $this->server = [
            'REQUEST_URI' => '/v1/methods',
            'QUERY_STRING' => 'one=two&three=4'
        ];

    }

    public function testRuleMatch()
    {
        $this->assertTrue();
    }


}