<?php

namespace Test\Krizalys\Onedrive;

use Krizalys\Onedrive\Folder;
use Mockery as m;

class FolderTest extends \PHPUnit_Framework_TestCase
{
    private function mockClient($objects)
    {
        $client = m::mock('Krizalys\Onedrive\Client[fetchObjects]');

        $client
            ->shouldReceive('fetchObjects')
            ->andReturn($objects);

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
        $file1 = $this->mockFile('file1');
        $file2 = $this->mockFile('file2');
        $file3 = $this->mockFile('file3');
        $file4 = $this->mockFile('file4');

        $folder = new Folder($this->mockClient(array(
            $file1,
            new Folder($this->mockClient(array(
                $file2,
                $file3,
            ))),
            $file4,
        )));

        $expected = array($file2, $file3, $file1, $file4);
        $actual   = $folder->fetchDescendantObjects();
        $this->assertEquals($expected, $actual);
    }
}
