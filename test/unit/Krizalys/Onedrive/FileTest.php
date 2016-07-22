<?php

namespace Test\Krizalys\Onedrive;

use Krizalys\Onedrive\File;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class FileTest extends MockeryTestCase
{
    private function mockClient(array $expectations = array())
    {
        $names = implode(',', array_keys($expectations));

        $client = m::mock("Krizalys\Onedrive\Client[$names]", array(
            array(
                'state' => (object) array(
                    'token' => (object) array(
                        'data' => (object) array(
                            'access_token' => 'TeSt/AcCeSs+ToKeN',
                        ),
                    ),
                ),
            ),
        ));

        foreach ($expectations as $name => $callback) {
            $expectation = $client->shouldReceive($name);
            $callback($expectation);
        }

        return $client;
    }

    public function testFetchContentShouldCallOnceClientApiGet()
    {
        $client = $this->mockClient(array(
            'apiGet' => function ($expectation) {
                $expectation->once();
            },
        ));

        $file = new File($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
        $file->fetchContent();
    }

    public function testCopyShouldCallOnceClientCopyFile()
    {
        $client = $this->mockClient(array(
            'copyFile' => function ($expectation) {
                $expectation->once();
            },
        ));

        $file = new File($client, 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
        $file->copy('path/to/file');
    }
}
