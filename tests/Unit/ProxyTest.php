<?php
/**
 * Created by IntelliJ IDEA.
 * User: meezaan
 * Date: 28/08/2018
 * Time: 10:20
 */

namespace Tests\Unit;


use IslamicNetwork\Waf\Model\Proxy;

class Proxyest extends \PHPUnit\Framework\TestCase
{
    public function testProxy()
    {
        $proxy = new Proxy('https://api.aladhan.com/v1/status');
        $proxy->emit();

    }

}