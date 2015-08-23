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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dispatcher;

    public function setUp()
    {
        $this->collection = $this->getMockBuilder('\MongoCollection')->disableOriginalConstructor()->getMock();
        $this->collection->expects($this->any())->method('getName')->willReturn('stdclass');

        $model = $this->getMock('\Stash\ModelInterface');
        $model->expects($this->any())->method('getClass')->willReturn('stdClass');
        $model->expects($this->any())->method('getCollection')->willReturn('stdclass');

        $this->converter = $this->getMock('\Stash\DocumentConverterInterface');

        $this->dispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    public function testGetName()
    {
        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $this->assertEquals('stdclass', $collection->getName());
    }

    /**
     * @expectedException \Stash\InvalidEntityException
     * @expectedExceptionMessage Unable to persist, got "array" instead of entity instance
     */
    public function testInsertNonObject()
    {
        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $collection->insert([]);
    }

    public function testInsertFail()
    {
        $this->converter->expects($this->any())->method('convertToDatabaseValue')->willReturn(['field' => 'foo']);
        $this->collection->expects($this->any())->method('insert')->with(['field' => 'foo'], [])->willReturn(null);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(Events::PERSIST_BEFORE, $this->isInstanceOf('\Stash\Event'));

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $this->assertFalse($collection->insert(new Foo(null, 'foo'), []));
    }

    public function testInsert()
    {
        $this->converter->expects($this->once())->method('convertToDatabaseValue')->willReturn(['field' => 'foo']);
        $this->collection->expects($this->once())->method('insert')->with(['field' => 'foo'], [])->willReturn(['ok' => 1]);
        $this->dispatcher->expects($this->exactly(2))->method('dispatch')->withConsecutive(
            [Events::PERSIST_BEFORE, $this->isInstanceOf('\Stash\Event')],
            [Events::PERSIST_AFTER, $this->isInstanceOf('\Stash\Event')]
        );

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $collection->insert(new Foo(null, 'foo'), []);
    }

    /**
     * @expectedException \Stash\InvalidEntityException
     * @expectedExceptionMessage Unable to persist, got "array" instead of entity instance
     */
    public function testSaveNonObject()
    {
        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $collection->save([]);
    }

    public function testSaveFail()
    {
        $this->converter->expects($this->any())->method('convertToDatabaseValue')->willReturn(['field' => 'foo']);
        $this->collection->expects($this->any())->method('insert')->with(['field' => 'foo'], [])->willReturn(null);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(Events::PERSIST_BEFORE, $this->isInstanceOf('\Stash\Event'));

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $this->assertFalse($collection->save(new Foo(null, 'foo'), []));
    }

    public function testSave()
    {
        $this->converter->expects($this->once())->method('convertToDatabaseValue')->willReturn(['field' => 'foo']);
        $this->collection->expects($this->once())->method('save')->with(['field' => 'foo'], [])->willReturn(['ok' => 1]);
        $this->dispatcher->expects($this->exactly(2))->method('dispatch')->withConsecutive(
            [Events::PERSIST_BEFORE, $this->isInstanceOf('\Stash\Event')],
            [Events::PERSIST_AFTER, $this->isInstanceOf('\Stash\Event')]
        );

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $collection->save(new Foo(null, 'foo'), []);
    }

    /**
     * @dataProvider entityProvider
     */
    public function testInsertUpdatesIdentifier($entity)
    {
        $this->converter->expects($this->any())->method('convertToDatabaseValue')->willReturn(['_id' => new \MongoId()]);
        $this->collection->expects($this->any())->method('insert')->willReturn(['ok' => 1]);

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
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

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
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

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $collection->find(['foo' => 'bar']);
    }

    public function testFindOne()
    {
        $entity = new \stdClass();

        $this->collection->expects($this->once())->method('findOne')->with(['foo' => 'bar'], [])->willReturn(['yada' => 'foo']);
        $this->converter->expects($this->once())->method('convertToPHPValue')->with(['yada' => 'foo'])->willReturn($entity);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(Events::FIND_AFTER, $this->isInstanceOf('\Stash\Event'));

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $result = $collection->findOne(['foo' => 'bar']);

        $this->assertSame($entity, $result);
    }

    public function testNotFoundOne()
    {
        $this->collection->expects($this->once())->method('findOne')->with(['foo' => 'bar'], [])->willReturn(null);
        $this->converter->expects($this->never())->method('convertToPHPValue');
        $this->dispatcher->expects($this->never())->method('dispatch');

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $result = $collection->findOne(['foo' => 'bar']);

        $this->assertNull($result);
    }

    public function testFindById()
    {
        $entity = new \stdClass();
        $entity->_id = new \MongoId();

        $this->collection->expects($this->once())->method('findOne')->with(['_id' => $entity->_id], [])->willReturn(['yada' => 'foo']);
        $this->converter->expects($this->once())->method('convertToPHPValue')->with(['yada' => 'foo'])->willReturn($entity);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(Events::FIND_AFTER, $this->isInstanceOf('\Stash\Event'));

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $result = $collection->findById($entity->_id);

        $this->assertSame($entity, $result);
    }

    public function testNotFoundById()
    {
        $id = new \MongoId();

        $this->collection->expects($this->once())->method('findOne')->with(['_id' => $id], [])->willReturn(null);
        $this->converter->expects($this->never())->method('convertToPHPValue');
        $this->dispatcher->expects($this->never())->method('dispatch');

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $result = $collection->findById($id);

        $this->assertNull($result);
    }

    public function testFindAndModify()
    {
        $entity = new \stdClass();

        $this->collection->expects($this->once())->method('findAndModify')->with(['foo' => 'bar'], ['foo' => 'yada'])->willReturn(['foo' => 'yada']);
        $this->converter->expects($this->once())->method('convertToPHPValue')->with(['foo' => 'yada'])->willReturn($entity);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(Events::FIND_AFTER, $this->isInstanceOf('\Stash\Event'));

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $result = $collection->findAndModify(['foo' => 'bar'], ['foo' => 'yada']);

        $this->assertEquals($entity, $result);
    }

    public function testNotFoundAndModified()
    {
        $this->collection->expects($this->once())->method('findAndModify')->with(['foo' => 'bar'], ['foo' => 'yada'])->willReturn(null);
        $this->converter->expects($this->never())->method('convertToPHPValue');
        $this->dispatcher->expects($this->never())->method('dispatch');

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $result = $collection->findAndModify(['foo' => 'bar'], ['foo' => 'yada']);

        $this->assertNull($result);
    }

    public function testAggregateFails()
    {
        $this->collection->expects($this->any())->method('aggregate')->willReturn(
            [
                'result' => [],
                'ok' => 0
            ]
        );

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
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

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
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

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $collection->aggregate(['foo' => 'bar'], ['foo' => 'yada'], '\stdClass');
    }

    public function testCount()
    {
        $this->collection->expects($this->once())->method('count')->with(['foo' => 'bar'], ['foo' => 'yada'])->willReturn(1);

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $collection->count(['foo' => 'bar'], ['foo' => 'yada']);
    }

    public function testDistinct()
    {
        $this->collection->expects($this->once())->method('distinct')->with(['foo'], ['foo' => 'yada'])->willReturn(['foo', 'bar']);

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $collection->distinct(['foo'], ['foo' => 'yada']);
    }

    public function testGroup()
    {
        $keys = ['foo' => 1];
        $initial = ['items' => []];
        $reduce = new \MongoCode("function (obj, prev) { prev.items.push(obj.name); }");
        $expected = [
            'retval' => [
                ['category' => 'fruit', 'items' => ['apple', 'peach', 'banana']],
                ['category' => 'veggie', 'items' => ['corn', 'broccoli']]
            ],
            'count' => 2,
            'keys' => 2,
            'ok' => 1
        ];

        $this->collection->expects($this->once())->method('group')->with($keys, $initial, $reduce)->willReturn($expected);

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $result = $collection->group($keys, $initial, $reduce);

        $this->assertEquals($expected, $result);
    }

    public function testGroupError()
    {
        $keys = ['foo' => 1];
        $initial = ['items' => []];
        $reduce = new \MongoCode("function (obj, prev) { prev.items.push(obj.name); }");

        $this->collection->expects($this->once())->method('group')->with($keys, $initial, $reduce)->willReturn(
            [
                'retval' => [],
                'count' => 0,
                'keys' => 0,
                'ok' => 0
            ]
        );

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $result = $collection->group($keys, $initial, $reduce);

        $this->assertFalse($result);
    }

    public function testRemove()
    {
        $this->collection->expects($this->once())->method('remove')->with(['foo' => 'bar'], []);
        $this->dispatcher->expects($this->never())->method('dispatch');

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $collection->remove(['foo' => 'bar']);
    }

    /**
     * @expectedException \Stash\InvalidEntityException
     * @expectedExceptionMessage Entity does not have
     */
    public function testRemoveEntityWithoutField()
    {
        $entity = new \stdClass();

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $collection->remove($entity);
    }

    /**
     * @expectedException \Stash\InvalidEntityException
     * @expectedExceptionMessage Entity identifier is empty
     */
    public function testEntityWithoutIdValue()
    {
        $entity = new Foo();

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $collection->remove($entity);
    }

    public function testRemoveEntity()
    {
        $id = new \MongoId();
        $entity = new Foo($id);

        $this->collection->expects($this->once())->method('remove')->with(['_id' => $id], []);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(Events::REMOVE_BEFORE, $this->isInstanceOf('\Stash\Event'));

        $collection = new Collection($this->collection, $this->converter, $this->dispatcher);
        $collection->remove($entity);
    }
}
