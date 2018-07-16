<?php
namespace Tests\Unit;

use IslamicNetwork\Waf\Model\Property\Property;

class PropertyTest extends \PHPUnit\Framework\TestCase
{
    private $propertyPath;
    private $property;

    public function setUp()
    {
        $this->propertyPath = realpath(__DIR__ . '/../../properties/example.com');
        $this->property = new Property($this->propertyPath, false);
    }

    public function testPath()
    {
        $this->assertEquals($this->propertyPath, $this->property->getPath());
        $this->assertEquals(true, $this->property->hasConfig());
    }

    public function testConfig()
    {   
        $config = $this->property->getConfig();
        $this->assertArrayHasKey('config', $config);
    }
}