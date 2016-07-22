<?php

namespace Test\Krizalys\Onedrive;

use Krizalys\Onedrive\Object;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class TestObject extends Object
{
}

class ObjectTest extends MockeryTestCase
{
    private $object;

    private $client;

    protected function setUp()
    {
        parent::setUp();
        $client       = $this->mockClient();
        $this->object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
        $this->client = $client;
    }

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

    private function mockClient(array $expectations = array())
    {
        $names  = implode(',', array_keys($expectations));
        $client = m::mock("Krizalys\Onedrive\Client[$names]");

        foreach ($expectations as $name => $callback) {
            $expectation = $client->shouldReceive($name);
            $callback($expectation);
        }

        return $client;
    }

    public function testIsFolderShouldReturnFalse()
    {
        $actual = $this
            ->object
            ->isFolder();

        $this->assertFalse($actual);
    }

    public function testGetIdShouldReturnExpectedValue()
    {
        $actual = $this
            ->object
            ->getId();

        $this->assertEquals('file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123', $actual);
    }

    public function testGetParentIdWithParentIdShouldReturnExpectedValue()
    {
        $object = new TestObject($this->client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123', array(
            'parent_id' => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
        ));

        $actual = $object->getParentId();
        $this->assertEquals('folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123', $actual);
    }

    public function testGetParentIdWithoutParentIdShouldReturnExpectedValue()
    {
        $payload = $this->mockPayloadFolder(array(
            'parent_id' => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($expectation) use ($payload) {
                $expectation->andReturn($payload);
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
        $actual = $object->getParentId();
        $this->assertEquals('folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123', $actual);
    }

    public function testGetNameWithNameShouldReturnExpectedValue()
    {
        $object = new TestObject($this->client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123', array(
            'name' => 'test-object',
        ));

        $actual = $object->getName();
        $this->assertEquals('test-object', $actual);
    }

    public function testGetNameWithoutNameShouldReturnExpectedValue()
    {
        $payload = $this->mockPayloadFolder(array(
            'name' => 'test-object',
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($expectation) use ($payload) {
                $expectation->andReturn($payload);
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
        $actual = $object->getName();
        $this->assertEquals('test-object', $actual);
    }

    public function testGetDescriptionWithDescriptionShouldReturnExpectedValue()
    {
        $object = new TestObject($this->client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123', array(
            'description' => 'Some test description',
        ));

        $actual = $object->getDescription();
        $this->assertEquals('Some test description', $actual);
    }

    public function testGetDescriptionWithoutDescriptionShouldReturnExpectedValue()
    {
        $payload = $this->mockPayloadFolder(array(
            'description' => 'Some test description',
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($expectation) use ($payload) {
                $expectation->andReturn($payload);
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
        $actual = $object->getDescription();
        $this->assertEquals('Some test description', $actual);
    }

    public function testGetSizeWithSizeShouldReturnExpectedValue()
    {
        $object = new TestObject($this->client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123', array(
            'size' => 123,
        ));

        $actual = $object->getSize();
        $this->assertEquals(123, $actual);
    }

    public function testGetSizeWithoutSizeShouldReturnExpectedValue()
    {
        $payload = $this->mockPayloadFolder(array(
            'size' => 123,
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($expectation) use ($payload) {
                $expectation->andReturn($payload);
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
        $actual = $object->getSize();
        $this->assertEquals(123, $actual);
    }

    public function testGetSourceWithSourceShouldReturnExpectedValue()
    {
        $object = new TestObject($this->client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123', array(
            'source' => 'http://te.st/123/source',
        ));

        $actual = $object->getSource();
        $this->assertEquals('http://te.st/123/source', $actual);
    }

    public function testGetSourceWithoutSourceShouldReturnExpectedValue()
    {
        $payload = $this->mockPayloadFolder(array(
            'source' => 'http://te.st/123/source',
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($expectation) use ($payload) {
                $expectation->andReturn($payload);
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
        $actual = $object->getSource();
        $this->assertEquals('http://te.st/123/source', $actual);
    }

    public function testGetCreatedTimeWithCreatedTimeShouldReturnExpectedValue()
    {
        $object = new TestObject($this->client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123', array(
            'created_time' => '1999-12-31T23:59:59+0000',
        ));

        $actual = $object->getCreatedTime();
        $this->assertEquals(strtotime('1999-12-31T23:59:59+0000'), $actual);
    }

    public function testGetCreatedTimeWithoutCreatedTimeShouldReturnExpectedValue()
    {
        $payload = $this->mockPayloadFolder(array(
            'created_time' => '1999-12-31T23:59:59+0000',
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($expectation) use ($payload) {
                $expectation->andReturn($payload);
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
        $actual = $object->getCreatedTime();
        $this->assertEquals(strtotime('1999-12-31T23:59:59+0000'), $actual);
    }

    public function testGetUpdatedTimeWithUpdatedTimeShouldReturnExpectedValue()
    {
        $object = new TestObject($this->client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123', array(
            'updated_time' => '1999-12-31T23:59:59+0000',
        ));

        $actual = $object->getUpdatedTime();
        $this->assertEquals(strtotime('1999-12-31T23:59:59+0000'), $actual);
    }

    public function testGetUpdatedTimeWithoutUpdatedTimeShouldReturnExpectedValue()
    {
        $payload = $this->mockPayloadFolder(array(
            'updated_time' => '1999-12-31T23:59:59+0000',
        ));

        $client = $this->mockClient(array(
            'fetchProperties' => function ($expectation) use ($payload) {
                $expectation->andReturn($payload);
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
        $actual = $object->getUpdatedTime();
        $this->assertEquals(strtotime('1999-12-31T23:59:59+0000'), $actual);
    }

    public function testMoveShouldCallOnceClientMoveObject()
    {
        $client = $this->mockClient(array(
            'moveObject' => function ($expectation) {
                $expectation->once();
            },
        ));

        $object = new TestObject($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
        $object->move('path/to/file');
    }
}
