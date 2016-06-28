<?php

namespace Test\Krizalys\Onedrive;

use Krizalys\Onedrive\Object;
use Mockery as m;

class TestObject extends Object
{
}

class ObjectTest extends \PHPUnit_Framework_TestCase
{
    private function mockPayloadFolder(array $values = array())
    {
        return (object) array_merge(array(
            'parent_id'    => 'folder.0000000000000000.0000000000000000!000',
            'name'         => '',
            'description'  => '',
            'size'         => 0,
            'source'       => 'http://localhost/',
            'created_time' => '1970-01-01T00:00:00+0000',
            'updated_time' => '1970-01-01T00:00:00+0000',
        ), $values);
    }

    private function mockClient($methods = array())
    {
        $names  = implode(',', array_keys($methods));
        $client = m::mock("Krizalys\Onedrive\Client[$names]");

        foreach ($methods as $name => $method) {
            $client
                ->shouldReceive($name)
                ->andReturnUsing($method);
        }

        return $client;
    }

    public function testIsFolderShouldReturnFalse()
    {
        $client = $this->mockClient();
        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
        $actual = $object->isFolder();
        $this->assertFalse($actual);
    }

    public function testFetchPropertiesShouldSetParentId()
    {
        $payload = $this->mockPayloadFolder(array(
            'parent_id' => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($objectId) use ($payload) {
                return $payload;
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!456');
        $object->fetchProperties();
        $actual = $object->getParentId();
        $this->assertEquals('folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123', $actual);
    }

    public function testFetchPropertiesShouldSetName()
    {
        $payload = $this->mockPayloadFolder(array(
            'name' => 'test-object',
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($objectId) use ($payload) {
                return $payload;
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!456');
        $object->fetchProperties();
        $actual = $object->getName();
        $this->assertEquals('test-object', $actual);
    }

    public function testFetchPropertiesShouldSetDescription()
    {
        $payload = $this->mockPayloadFolder(array(
            'description' => 'Some test description',
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($objectId) use ($payload) {
                return $payload;
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!456');
        $object->fetchProperties();
        $actual = $object->getDescription();
        $this->assertEquals('Some test description', $actual);
    }

    public function testFetchPropertiesShouldSetSize()
    {
        $payload = $this->mockPayloadFolder(array(
            'size' => 123,
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($objectId) use ($payload) {
                return $payload;
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!456');
        $object->fetchProperties();
        $actual = $object->getSize();
        $this->assertEquals(123, $actual);
    }

    public function testFetchPropertiesShouldSetSource()
    {
        $payload = $this->mockPayloadFolder(array(
            'source' => 'http://te.st/123/source',
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($objectId) use ($payload) {
                return $payload;
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!456');
        $object->fetchProperties();
        $actual = $object->getSource();
        $this->assertEquals('http://te.st/123/source', $actual);
    }

    public function testFetchPropertiesShouldSetCreatedTime()
    {
        $payload = $this->mockPayloadFolder(array(
            'created_time' => '1999-12-31T23:59:59+0000',
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($objectId) use ($payload) {
                return $payload;
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!456');
        $object->fetchProperties();
        $actual = $object->getCreatedTime();
        $this->assertEquals(strtotime('1999-12-31T23:59:59+0000'), $actual);
    }

    public function testFetchPropertiesShouldSetUpdatedTime()
    {
        $payload = $this->mockPayloadFolder(array(
            'updated_time' => '1999-12-31T23:59:59+0000',
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($objectId) use ($payload) {
                return $payload;
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!456');
        $object->fetchProperties();
        $actual = $object->getUpdatedTime();
        $this->assertEquals(strtotime('1999-12-31T23:59:59+0000'), $actual);
    }
}
