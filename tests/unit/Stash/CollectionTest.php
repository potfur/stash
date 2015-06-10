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


use Fake\Entity;
use Fake\Yada;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \MongoCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var DocumentConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;

    public function setUp()
    {
        $this->collection = $this->getMockBuilder('\MongoCollection')->disableOriginalConstructor()->getMock();
        $this->converter = $this->getMock('\Stash\DocumentConverterInterface');
    }

    public function testName()
    {
        $this->collection->expects($this->once())->method('getName')->with();

        $collection = new Collection($this->collection, $this->converter);
        $collection->getName();
    }

    public function testInsertFail()
    {
        $this->converter->expects($this->any())->method('convertToDatabaseValue')->willReturn(['yada' => 'foo']);
        $this->collection->expects($this->any())->method('insert')->with(['yada' => 'foo'], [])->willReturn(null);

        $collection = new Collection($this->collection, $this->converter);
        $this->assertFalse($collection->insert(new Yada(['yada' => 'foo']), []));
    }

    public function testInsert()
    {
        $this->converter->expects($this->once())->method('convertToDatabaseValue')->willReturn(['yada' => 'foo']);
        $this->collection->expects($this->once())->method('insert')->with(['yada' => 'foo'], [])->willReturn(['ok' => 1]);

        $collection = new Collection($this->collection, $this->converter);
        $collection->insert(new Yada(['yada' => 'foo']), []);
    }

    public function testSaveFail()
    {
        $this->converter->expects($this->any())->method('convertToDatabaseValue')->willReturn(['yada' => 'foo']);
        $this->collection->expects($this->any())->method('insert')->with(['yada' => 'foo'], [])->willReturn(null);

        $collection = new Collection($this->collection, $this->converter);
        $this->assertFalse($collection->save(new Yada(['yada' => 'foo']), []));
    }

    public function testSave()
    {
        $this->converter->expects($this->once())->method('convertToDatabaseValue')->willReturn(['yada' => 'foo']);
        $this->collection->expects($this->once())->method('save')->with(['yada' => 'foo'], [])->willReturn(['ok' => 1]);

        $collection = new Collection($this->collection, $this->converter);
        $collection->save(new Yada(['yada' => 'foo']), []);
    }

    /**
     * @dataProvider entityProvider
     */
    public function testInsertUpdatesIdentifier($entity)
    {
        $this->converter->expects($this->any())->method('convertToDatabaseValue')->willReturn(['_id' => new \MongoId()]);
        $this->collection->expects($this->any())->method('insert')->willReturn(['ok' => 1]);

        $collection = new Collection($this->collection, $this->converter);
        $collection->insert($entity, []);

        $this->assertInstanceOf('\MongoId', $entity->_id);
    }

    /**
     * @dataProvider entityProvider
     */
    public function testSaveUpdatesIdentifier($entity)
    {
        $this->converter->expects($this->any())->method('convertToDatabaseValue')->willReturn(['_id' => new \MongoId()]);
        $this->collection->expects($this->any())->method('save')->willReturn(['ok' => 1]);

        $collection = new Collection($this->collection, $this->converter);
        $collection->save($entity, []);

        $this->assertInstanceOf('\MongoId', $entity->_id);
    }

    public function entityProvider()
    {
        return [
            [new Entity()],
            [new \stdClass()]
        ];
    }

    public function testFind()
    {
        $cursor = $this->getMockBuilder('\MongoCursor')->disableOriginalConstructor()->getMock();

        $this->collection->expects($this->once())->method('find')->with(['foo' => 'bar'], [])->willReturn($cursor);

        $collection = new Collection($this->collection, $this->converter);
        $collection->find(['foo' => 'bar']);
    }

    public function testFindOne()
    {
        $this->collection->expects($this->once())->method('findOne')->with(['foo' => 'bar'], [])->willReturn(['yada' => 'foo']);
        $this->converter->expects($this->once())->method('convertToPHPValue')->with(['yada' => 'foo']);

        $collection = new Collection($this->collection, $this->converter);
        $collection->findOne(['foo' => 'bar']);
    }

    public function testFindAndModify()
    {
        $this->collection->expects($this->once())->method('findAndModify')->with(['foo' => 'bar'], ['foo' => 'yada'])->willReturn(['foo' => 'yada']);
        $this->converter->expects($this->once())->method('convertToPHPValue')->with(['foo' => 'yada']);

        $collection = new Collection($this->collection, $this->converter);
        $collection->findAndModify(['foo' => 'bar'], ['foo' => 'yada']);
    }

    public function testRemove()
    {
        $this->collection->expects($this->once())->method('remove')->with(['foo' => 'bar'], []);

        $collection = new Collection($this->collection, $this->converter);
        $collection->remove(['foo' => 'bar']);
    }

    /**
     * @expectedException \Stash\InvalidEntityException
     * @expectedExceptionMessage Entity does not have
     */
    public function testRemoveEntityWithoutField()
    {
        $entity = new Yada();

        $collection = new Collection($this->collection, $this->converter);
        $collection->remove($entity);
    }

    /**
     * @expectedException \Stash\InvalidEntityException
     * @expectedExceptionMessage Entity identifier is empty
     */
    public function testEntityWithoutIdValue()
    {
        $entity = new Entity();

        $collection = new Collection($this->collection, $this->converter);
        $collection->remove($entity);
    }

    public function testRemoveEntity()
    {
        $id = new \MongoId();
        $entity = new Entity($id);

        $this->collection->expects($this->once())->method('remove')->with(['_id' => $id], []);

        $collection = new Collection($this->collection, $this->converter);
        $collection->remove($entity);
    }

    public function testCall()
    {
        $this->collection->expects($this->once())->method('__toString');

        $collection = new Collection($this->collection, $this->converter);
        $collection->__toString();
    }

    public function testGetSet()
    {
        $this->collection->w = 1;

        $collection = new Collection($this->collection, $this->converter);
        $collection->w = 1;

        $this->assertEquals(1, $collection->w);
        $this->assertEquals(1, $this->collection->w);
    }
}
