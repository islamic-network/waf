<?php
/**
 * Created by IntelliJ IDEA.
 * User: meezaan
 * Date: 28/08/2018
 * Time: 10:20
 */

namespace Tests\Unit;


use IslamicNetwork\Waf\Helper\IpHelper;

class IpHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testCidrToRange()
    {
        $cidr = "73.35.143.32/27";

        $range = IpHelper::cidrToRange($cidr);

        $this->assertCount(2, $range);
        $this->assertEquals('73.35.143.32', $range[0]);
        $this->assertEquals('73.35.143.63', $range[1]);
    }

    public function testRangeToAddresses()
    {
        $cidr = "73.35.143.32/27";

        $range = IpHelper::cidrToRange($cidr);

        $addresses = IpHelper::rangeToAddresses($range);

        $this->assertCount(32, $addresses);
        $this->assertEquals('73.35.143.32', $addresses[0]);
        $this->assertEquals('73.35.143.33', $addresses[1]);
        $this->assertEquals('73.35.143.34', $addresses[2]);
        $this->assertEquals('73.35.143.35', $addresses[3]);
        $this->assertEquals('73.35.143.36', $addresses[4]);
        $this->assertEquals('73.35.143.37', $addresses[5]);
        $this->assertEquals('73.35.143.38', $addresses[6]);
        $this->assertEquals('73.35.143.39', $addresses[7]);
        $this->assertEquals('73.35.143.40', $addresses[8]);
        $this->assertEquals('73.35.143.41', $addresses[9]);
        $this->assertEquals('73.35.143.42', $addresses[10]);
        $this->assertEquals('73.35.143.43', $addresses[11]);
        $this->assertEquals('73.35.143.44', $addresses[12]);
        $this->assertEquals('73.35.143.45', $addresses[13]);
        $this->assertEquals('73.35.143.46', $addresses[14]);
        $this->assertEquals('73.35.143.47', $addresses[15]);
        $this->assertEquals('73.35.143.48', $addresses[16]);
        $this->assertEquals('73.35.143.49', $addresses[17]);
        $this->assertEquals('73.35.143.50', $addresses[18]);
        $this->assertEquals('73.35.143.51', $addresses[19]);
        $this->assertEquals('73.35.143.52', $addresses[20]);
        $this->assertEquals('73.35.143.53', $addresses[21]);
        $this->assertEquals('73.35.143.63', $addresses[31]);

    }

    public function testIsNotCidr()
    {
        $ip = "11.12.13.14";
        $this->assertFalse(IpHelper::isCidr($ip));

    }

    public function testIsCidr()
    {
        $ip = "11.12.13.14/27";
        $this->assertTrue(IpHelper::isCidr($ip));

    }

}