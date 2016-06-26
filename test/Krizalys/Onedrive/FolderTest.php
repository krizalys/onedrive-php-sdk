<?php

namespace Test\Krizalys\Onedrive
{
    use Krizalys\Onedrive\Folder;

    class FolderTest extends \PHPUnit_Framework_TestCase
    {
        public function testFetchDescendantObjects()
        {
            $file1 = $this->getFileMock('file1');
            $file2 = $this->getFileMock('file2');
            $file3 = $this->getFileMock('file3');
            $file4 = $this->getFileMock('file4');
            
            $folderMock = $this->getFolderMock(
                array(
                    $file1,
                    $this->getFolderMock(
                        array(
                            $file2,
                            $file3,
                        )
                    ),
                    $file4,
                )
            );
            
            $expected = array($file2, $file3, $file1, $file4);
            $actual = $folderMock->fetchDescendantObjects();
            
            $this->assertEquals($expected, $actual);
        }
        
        /**
         * @param Object[] $childObjects
         * @return \PHPUnit_Framework_MockObject_MockObject|Folder
         */
        protected function getFolderMock($childObjects)
        {
            $mock = $this->getMockBuilder('\\Krizalys\\Onedrive\\Folder')
                ->disableOriginalConstructor()
                ->setMethods(array('fetchChildObjects'))
                ->getMock();
            
            $mock->method('fetchChildObjects')
                ->willReturn($childObjects);
            
            return $mock;
        }
        
        /**
         * @param mixed $fileId
         * @return \PHPUnit_Framework_MockObject_MockObject|File
         */
        protected function getFileMock($fileId)
        {
            $mock = $this->getMockBuilder('\\Krizalys\\Onedrive\\File')
                ->disableOriginalConstructor()
                ->getMock();
            
            $mock->id = $fileId;
            
            return $mock;
        }
    }
}
