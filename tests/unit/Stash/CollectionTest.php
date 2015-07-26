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
        $this->collection->expects($this->any())->method('getName')->willReturn('stdclass');

        $model = $this->getMock('\Stash\ModelInterface');
        $model->expects($this->any())->method('getClass')->willReturn('stdClass');
        $model->expects($this->any())->method('getCollection')->willReturn('stdclass');

        $this->converter = $this->getMock('\Stash\DocumentConverterInterface');
    }

    public function testInsertFail()
    {
        $this->converter->expects($this->any())->method('convertToDatabaseValue')->willReturn(['field' => 'foo']);
        $this->collection->expects($this->any())->method('insert')->with(['field' => 'foo'], [])->willReturn(null);

        $collection = new Collection($this->collection, $this->converter);
        $this->assertFalse($collection->insert(new Foo(null, 'foo'), []));
    }

    public function testInsert()
    {
        $this->converter->expects($this->once())->method('convertToDatabaseValue')->willReturn(['field' => 'foo']);
        $this->collection->expects($this->once())->method('insert')->with(['field' => 'foo'], [])->willReturn(['ok' => 1]);

        $collection = new Collection($this->collection, $this->converter);
        $collection->insert(new Foo(null, 'foo'), []);
    }

    public function testSaveFail()
    {
        $this->converter->expects($this->any())->method('convertToDatabaseValue')->willReturn(['field' => 'foo']);
        $this->collection->expects($this->any())->method('insert')->with(['field' => 'foo'], [])->willReturn(null);

        $collection = new Collection($this->collection, $this->converter);
        $this->assertFalse($collection->save(new Foo(null, 'foo'), []));
    }

    public function testSave()
    {
        $this->converter->expects($this->once())->method('convertToDatabaseValue')->willReturn(['field' => 'foo']);
        $this->collection->expects($this->once())->method('save')->with(['field' => 'foo'], [])->willReturn(['ok' => 1]);

        $collection = new Collection($this->collection, $this->converter);
        $collection->save(new Foo(null, 'foo'), []);
    }

    public function testSaveArray()
    {
        $this->converter->expects($this->never())->method('convertToDatabaseValue');
        $this->collection->expects($this->once())->method('save')->with(['field' => 'foo'], [])->willReturn(['ok' => 1]);

        $collection = new Collection($this->collection, $this->converter);
        $collection->save(['field' => 'foo'], []);
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
            [new Foo()],
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

    public function testFindById()
    {
        $id = new \MongoId();

        $this->collection->expects($this->once())->method('findOne')->with(['_id' => $id], [])->willReturn(['yada' => 'foo']);
        $this->converter->expects($this->once())->method('convertToPHPValue')->with(['yada' => 'foo']);

        $collection = new Collection($this->collection, $this->converter);
        $collection->findById($id);
    }

    public function testFindAndModify()
    {
        $this->collection->expects($this->once())->method('findAndModify')->with(['foo' => 'bar'], ['foo' => 'yada'])->willReturn(['foo' => 'yada']);
        $this->converter->expects($this->once())->method('convertToPHPValue')->with(['foo' => 'yada']);

        $collection = new Collection($this->collection, $this->converter);
        $collection->findAndModify(['foo' => 'bar'], ['foo' => 'yada']);
    }

    public function testAggregateFails()
    {
        $this->collection->expects($this->any())->method('aggregate')->willReturn(
            [
                'result' => [],
                'ok' => 0
            ]
        );

        $collection = new Collection($this->collection, $this->converter);
        $result = $collection->aggregate(['foo' => 'bar'], ['foo' => 'yada']);

        $this->assertFalse($result);
    }

    public function testAggregateWithoutClass()
    {
        $this->collection->expects($this->once())->method('aggregate')->with(['foo' => 'bar'], ['foo' => 'yada'])->willReturn(
            [
                'result' => [
                    ['_id' => ['tags' => 'good'], 'authors' => ['bob']],
                    ['_id' => ['tags' => 'fun'], 'authors' => ['bob']],
                ],
                'ok' => 1
            ]
        );
        $this->converter->expects($this->never())->method('convertToPHPValue');

        $collection = new Collection($this->collection, $this->converter);
        $collection->aggregate(['foo' => 'bar'], ['foo' => 'yada']);
    }

    public function testAggregateWithClass()
    {
        $this->collection->expects($this->once())->method('aggregate')->with(['foo' => 'bar'], ['foo' => 'yada'])->willReturn(
            [
                'result' => [
                    ['_id' => ['tags' => 'good'], 'authors' => ['bob']],
                    ['_id' => ['tags' => 'fun'], 'authors' => ['bob']],
                ],
                'ok' => 1
            ]
        );
        $this->converter->expects($this->exactly(2))->method('convertToPHPValue')->withConsecutive(
            [['_id' => ['tags' => 'good'], '_class' => '\stdClass', 'authors' => ['bob']]],
            [['_id' => ['tags' => 'fun'], '_class' => '\stdClass', 'authors' => ['bob']]]
        );

        $collection = new Collection($this->collection, $this->converter);
        $collection->aggregate(['foo' => 'bar'], ['foo' => 'yada'], '\stdClass');
    }

    public function testCount()
    {
        $this->collection->expects($this->once())->method('count')->with(['foo' => 'bar'], ['foo' => 'yada'])->willReturn(1);

        $collection = new Collection($this->collection, $this->converter);
        $collection->count(['foo' => 'bar'], ['foo' => 'yada']);
    }

    public function testDistinct()
    {
        $this->collection->expects($this->once())->method('distinct')->with(['foo'], ['foo' => 'yada'])->willReturn(['foo', 'bar']);

        $collection = new Collection($this->collection, $this->converter);
        $collection->distinct(['foo'], ['foo' => 'yada']);
    }

    public function testGroup()
    {
        $keys = ['foo' => 1];
        $initial = ['items' => []];
        $reduce = new \MongoCode("function (obj, prev) { prev.items.push(obj.name); }");

        $this->collection->expects($this->once())->method('group')->with($keys, $initial, $reduce)->willReturn(
            [
                ['category' => 'fruit', 'items' => ['apple', 'peach', 'banana']],
                ['category' => 'veggie', 'items' => ['corn', 'broccoli']]
            ]
        );

        $collection = new Collection($this->collection, $this->converter);
        $collection->group($keys, $initial, $reduce);
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
        $entity = new \stdClass();

        $collection = new Collection($this->collection, $this->converter);
        $collection->remove($entity);
    }

    /**
     * @expectedException \Stash\InvalidEntityException
     * @expectedExceptionMessage Entity identifier is empty
     */
    public function testEntityWithoutIdValue()
    {
        $entity = new Foo();

        $collection = new Collection($this->collection, $this->converter);
        $collection->remove($entity);
    }

    public function testRemoveEntity()
    {
        $id = new \MongoId();
        $entity = new Foo($id);

        $this->collection->expects($this->once())->method('remove')->with(['_id' => $id], []);

        $collection = new Collection($this->collection, $this->converter);
        $collection->remove($entity);
    }
}
