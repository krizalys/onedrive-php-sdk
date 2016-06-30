<?php

namespace Test\Krizalys\Onedrive;

use Krizalys\Onedrive\Folder;
use Mockery as m;

class FolderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * "public" is required to be called from within anonymous functions in
     * PHP 5.3.
     */
    public function mockClient(array $expectations = array())
    {
        $names  = implode(',', array_keys($expectations));
        $client = m::mock("Krizalys\Onedrive\Client[$names]");

        foreach ($expectations as $name => $callback) {
            $expectation = $client->shouldReceive($name);
            $callback($expectation);
        }

        return $client;
    }

    /**
     * @param mixed $fileId
     */
    private function mockFile($fileId)
    {
        $file = m::mock('Krizalys\Onedrive\File', array(
            'isFolder' => false,
        ));

        $file->id = $fileId;
        return $file;
    }

    public function testFetchDescendantObjects()
    {
        $self  = $this;
        $file1 = $this->mockFile('file1');
        $file2 = $this->mockFile('file2');
        $file3 = $this->mockFile('file3');
        $file4 = $this->mockFile('file4');

        $folder = new Folder($self->mockClient(array(
            'fetchObjects' => function ($expectation) use ($self, $file1, $file2, $file3, $file4) {
                $expectation->andReturn(array(
                    $file1,
                    new Folder($self->mockClient(array(
                        'fetchObjects' => function ($expectation) use ($file2, $file3) {
                            $expectation->andReturn(array(
                                $file2,
                                $file3,
                            ));
                        },
                    ))),
                    $file4,
                ));
            },
        )));

        $expected = array($file2, $file3, $file1, $file4);
        $actual   = $folder->fetchDescendantObjects();
        $this->assertEquals($expected, $actual);
    }
}
