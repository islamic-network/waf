<?php
namespace Tests\Unit;

use IslamicNetwork\Waf\Cacher\Memcached;

class MemcachedTest extends \PHPUnit\Framework\TestCase
{


    public function testDefault()
    {

        $mc = new Memcached('127.0.0.1', 11211);
        $mc->set('one', 'yes');
        $this->assertEquals('yes', $mc->get('one'));
        $this->assertFalse($mc->exists('five'));
        $this->assertTrue($mc->exists('one'));
        $this->assertNotEquals('newValue', $mc->get('one'));
        $mc->set('one', 'happiness');
        $this->assertEquals('happiness', $mc->get('one'));
        $this->assertNotEquals('yes', $mc->get('one'));

    }



}