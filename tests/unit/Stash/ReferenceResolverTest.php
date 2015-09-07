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


use Fake\Foo;

class ReferenceResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var ModelCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $models;

    public function setUp()
    {
        $this->collection = $this->getMockBuilder('\Stash\Collection')->disableOriginalConstructor()->getMock();

        $this->connection = $this->getMockBuilder('\Stash\Connection')->disableOriginalConstructor()->getMock();
        $this->connection->expects($this->any())->method('getCollection')->willReturn($this->collection);

        $model = $this->getMock('\Stash\ModelInterface');
        $model->expects($this->any())->method('getCollection')->willReturn('stdclass');
        $model->expects($this->any())->method('getClass')->willReturn(\Fake\Foo::class);

        $this->models = $this->getMock('\Stash\ModelCollection');
        $this->models->expects($this->any())->method($this->anything())->willReturn($model);
    }

    public function testStoreEntity()
    {
        $entity = new Foo(1);

        $referencer = new ReferenceResolver($this->models);
        $referencer->connect($this->connection);

        $result = $referencer->store($entity);

        $this->assertEquals(['$ref' => 'stdclass', '$id' => 1], $result);
    }

    public function testStore()
    {
        $entity = new Foo(1);

        $referencer = new ReferenceResolver($this->models);
        $referencer->connect($this->connection);

        $result = $referencer->store($entity);

        $this->assertEquals(['$ref' => 'stdclass', '$id' => 1], $result);
    }

    public function testStoreNull()
    {
        $referencer = new ReferenceResolver($this->models);
        $referencer->connect($this->connection);
        $result = $referencer->store(null);

        $this->assertNull($result);
    }

    /**
     * @expectedException \Stash\InvalidEntityException
     * @expectedExceptionMessage Entity does not have "_id" field
     */
    public function testEntityWithoutIdProperty()
    {
        $referencer = new ReferenceResolver($this->models);
        $referencer->connect($this->connection);

        $referencer->store(new \stdClass());
    }

    /**
     * @expectedException \Stash\InvalidEntityException
     * @expectedExceptionMessage Entity identifier is empty
     */
    public function testEntityWithEmptyIdProperty()
    {
        $referencer = new ReferenceResolver($this->models);
        $referencer->connect($this->connection);

        $referencer->store(new Foo());
    }

    public function testResolve()
    {
        $entity = new Foo(1);
        $this->collection->expects($this->once())->method('findById')->willReturn($entity);

        $referencer = new ReferenceResolver($this->models);
        $referencer->connect($this->connection);

        $result = $referencer->resolve(['$ref' => 'stdclass', '$id' => 1]);

        $this->assertInstanceOf(\Fake\Foo::class, $result);
        $this->assertEquals($entity->_id, $result->_id);
    }

    public function testResolveNull()
    {
        $referencer = new ReferenceResolver($this->models);
        $referencer->connect($this->connection);
        $result = $referencer->resolve(null);

        $this->assertNull($result);
    }

    /**
     * @expectedException \Stash\InvalidReferenceException
     * @expectedExceptionMessage Unable to resolve reference, not connected
     */
    public function testNotConnected()
    {
        $referencer = new ReferenceResolver($this->models);
        $referencer->resolve(['$ref' => 'stdclass', '$id' => 1]);
    }

    /**
     * @expectedException \Stash\InvalidReferenceException
     * @expectedExceptionMessage Invalid reference array
     * @dataProvider invalidReferenceProvider
     */
    public function testInvalidReference($reference)
    {
        $referencer = new ReferenceResolver($this->models);
        $referencer->connect($this->connection);

        $referencer->resolve($reference);
    }

    public function invalidReferenceProvider()
    {
        return [
            ['foo'],
            [['$ref' => 'stdclass']],
            [['$id' => 1]]
        ];
    }
}
