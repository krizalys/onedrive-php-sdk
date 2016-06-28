<?php

namespace Test\Krizalys\Onedrive;

use Krizalys\Onedrive\Folder;
use Mockery as m;

class FolderTest extends \PHPUnit_Framework_TestCase
{
    public function testFetchDescendantObjects()
    {
        $file1 = $this->getFileMock('file1');
        $file2 = $this->getFileMock('file2');
        $file3 = $this->getFileMock('file3');
        $file4 = $this->getFileMock('file4');

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
    protected function getFileMock($fileId)
    {
        $mock = m::mock('Krizalys\Onedrive\File', array(
            'isFolder' => false,
        ));

        $mock->id = $fileId;
        return $mock;
    }
}
