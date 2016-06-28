<?php

namespace Test\Krizalys\Onedrive;

use Krizalys\Onedrive\Object;
use Mockery as m;

class TestObject extends Object
{
}

class ObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testIsFolderShouldReturnFalse()
    {
        $client = m::mock('Krizalys\Onedrive\Client[]');
        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
        $actual = $object->isFolder();
        $this->assertFalse($actual);
    }
}
