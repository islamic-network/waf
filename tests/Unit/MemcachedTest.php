<?php
namespace Tests\Unit;

use Vesica\Cacher\Memcached;

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

        $mc2 = new Memcached('127.0.0.1', 11211, 'test');
        $this->assertFalse($mc2->exists('one'));
        $mc2->set('one', 'NO');
        $this->assertEquals('NO', $mc2->get('one'));
        $this->assertEquals('happiness', $mc->get('one'));

    }



}
