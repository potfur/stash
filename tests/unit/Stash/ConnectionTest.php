<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash;


class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \MongoCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var \MongoDB|\PHPUnit_Framework_MockObject_MockObject
     */
    private $database;

    /**
     * @var \MongoClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    /**
     * @var DocumentConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;

    public function setUp()
    {
        $this->collection = $this->getMockBuilder('\MongoCollection')->disableOriginalConstructor()->getMock();
        $this->collection->expects($this->any())->method('getName')->willReturn('test');

        $this->database = $this->getMockBuilder('\MongoDB')->disableOriginalConstructor()->getMock();
        $this->database->expects($this->any())->method('selectCollection')->willReturn($this->collection);

        $this->client = $this->getMockBuilder('\MongoClient')->disableOriginalConstructor()->getMock();
        $this->client->expects($this->any())->method('selectDB')->willReturn($this->database);

        $this->converter = $this->getMock('\Stash\DocumentConverterInterface');
    }

    public function testGetCollection()
    {
        $connection = new Connection($this->client, $this->converter);
        $connection->selectDB('test');

        $result = $connection->getCollection('test');

        $this->assertInstanceOf('\Stash\Collection', $result);
        $this->assertEquals('test', $result->getName());
    }

    public function testGetBufferedCollection()
    {
        $connection = new Connection($this->client, $this->converter);
        $connection->selectDB('test');

        $resultA = $connection->getCollection('test');
        $resultB = $connection->getCollection('test');

        $this->assertSame($resultA, $resultB);
    }
}
