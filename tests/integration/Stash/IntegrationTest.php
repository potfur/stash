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
use Stash\Converter\Converter;
use Stash\Converter\Type\ArrayType;
use Stash\Converter\Type\BooleanType;
use Stash\Converter\Type\DateType;
use Stash\Converter\Type\DecimalType;
use Stash\Converter\Type\DocumentType;
use Stash\Converter\Type\IdType;
use Stash\Converter\Type\IntegerType;
use Stash\Converter\Type\StringType;
use Stash\Model\Field\Reference;
use Stash\Model\Field\Scalar;
use Stash\Model\Field\Id;
use Stash\Model\Model;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \MongoClient
     */
    private $mongo;

    /**
     * @var ModelCollection
     */
    private $models;

    /**
     * @var DocumentConverter
     */
    private $converter;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Foo
     */
    private $entity;

    public function setUp()
    {
        $this->mongo = new \MongoClient();

        $this->models = new ModelCollection();

        $types = [
            new IdType(),
            new BooleanType(),
            new IntegerType(),
            new DecimalType(),
            new StringType(),
            new DateType(),
            new ArrayType(),
            new DocumentType()
        ];

        $converter = new Converter($types);
        $referencer = new ReferenceResolver($this->models);
        $proxyAdapter = new ProxyAdapter();

        $this->converter = new DocumentConverter($converter, $referencer, $this->models, $proxyAdapter);

        $this->connection = new Connection($this->mongo, $this->models, $this->converter);
        $this->connection->selectDB('test');

        $this->connection->getCollection('foo')->remove();

        $this->entity = new Foo(null, 'foo bar');
    }

    public function testFind()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->insert(
            [
                '_class' => '\Fake\Foo',
                'field' => 'foo'
            ]
        );

        $this->models->register(
            new Model(
                '\Fake\Foo',
                [
                    new Id(),
                    new Scalar('field', Fields::TYPE_STRING),
                ],
                'foo'
            )
        );

        $result = $this->connection->getCollection('foo')->find();

        foreach ($result as $element) {
            $this->assertInstanceOf('\Fake\Foo', $element);
            $this->assertEquals('foo', $element->field);
        }
    }

    public function testFindOne()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->insert(
            [
                '_class' => '\Fake\Foo',
                'field' => 'foo'
            ]
        );

        $this->models->register(
            new Model(
                '\Fake\Foo',
                [
                    new Id(),
                    new Scalar('field', Fields::TYPE_STRING),
                ],
                'foo'
            )
        );

        $result = $this->connection->getCollection('foo')->findOne();

        $this->assertInstanceOf('\Fake\Foo', $result);
        $this->assertEquals('foo', $result->field);
    }

    public function testFindById()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->insert(
            [
                '_id' => 1,
                '_class' => '\Fake\Foo',
                'field' => 'foo'
            ]
        );

        $this->models->register(
            new Model(
                '\Fake\Foo',
                [
                    new Id(),
                    new Scalar('field', Fields::TYPE_STRING),
                ],
                'foo'
            )
        );

        $result = $this->connection->getCollection('foo')->findById(1);

        foreach ($result as $element) {
            $this->assertInstanceOf('\Fake\Foo', $element);
            $this->assertEquals('foo', $element->field);
        }
    }

    public function testInsert()
    {
        $this->models->register(
            new Model(
                '\Fake\Foo',
                [
                    new Id(),
                    new Scalar('field', Fields::TYPE_STRING),
                ],
                'foo'
            )
        );

        $foo = $this->connection->getCollection('foo');
        $foo->insert($this->entity);
        $result = $foo->findOne();

        $this->assertInstanceOf('\Fake\Foo', $result);
        $this->assertEquals($this->entity->_id, $result->_id);
    }

    public function testSaveWhenInsertingEntity()
    {
        $this->models->register(
            new Model(
                '\Fake\Foo',
                [
                    new Id(),
                    new Scalar('field', Fields::TYPE_STRING),
                ],
                'foo'
            )
        );

        $foo = $this->connection->getCollection('foo');
        $foo->save($this->entity);
        $result = $foo->findOne();

        $this->assertInstanceOf('\Fake\Foo', $result);
        $this->assertEquals($this->entity->_id, $result->_id);
    }

    public function testSaveWhenUpdatingEntity()
    {
        $this->models->register(
            new Model(
                '\Fake\Foo',
                [
                    new Id(),
                    new Scalar('field', Fields::TYPE_STRING),
                ],
                'foo'
            )
        );

        $foo = $this->connection->getCollection('foo');
        $foo->save($this->entity);
        $foo->save($this->entity);
        $result = $foo->find();

        $this->assertEquals(1, $result->count());

        foreach ($result as $element) {
            $this->assertInstanceOf('\Fake\Foo', $element);
            $this->assertEquals($this->entity->_id, $element->_id);
        }
    }

    public function testReference()
    {
        $this->models->register(
            new Model(
                '\Fake\Foo',
                [
                    new Id(),
                    new Reference('field'),
                ],
                'foo'
            )
        );

        $foo = $this->connection->getCollection('foo');

        $entityA = new Foo(null, null);
        $foo->save($entityA);

        $entityB = new Foo(null, $entityA);
        $foo->save($entityB);

        $result = $foo->findById($entityB->_id);

        $this->assertInstanceOf('\Fake\Foo', $result);
        $this->assertEquals($entityB->_id, $result->_id);
    }

    public function testLoopedReference()
    {
        $this->models->register(
            new Model(
                '\Fake\Foo',
                [
                    new Id(),
                    new Reference('field'),
                ],
                'foo'
            )
        );

        $foo = $this->connection->getCollection('foo');

        $entityA = new Foo(null, null);
        $foo->save($entityA);

        $entityB = new Foo(null, $entityA);
        $foo->save($entityB);

        $entityA = $foo->findById($entityA->_id);
        $entityA->field = $entityB;
        $foo->save($entityA);

        $result = $foo->findById($entityB->_id);

        $this->assertInstanceOf('\Fake\Foo', $result);
        $this->assertEquals($entityB->_id, $result->_id);
    }

    public function testRemoveByCriteria()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->insert(
            [
                '_id' => 1,
                '_class' => '\Fake\Foo',
                'field' => 'foo'
            ]
        );

        $this->connection->getCollection('foo')->remove(['_id' => 1]);
        $result = $this->connection->getCollection('foo')->find()->count();

        $this->assertEquals(0, $result);
    }

    public function testRemoveEntity()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->insert(
            [
                '_id' => 1,
                '_class' => '\Fake\Foo',
                'field' => 'foo'
            ]
        );

        $entity = new Foo(1);

        $this->connection->getCollection('foo')->remove($entity);
        $result = $this->connection->getCollection('foo')->find()->count();

        $this->assertEquals(0, $result);
    }

    public function testAggregateRaw()
    {
        $this->markTestIncomplete();
    }

    public function testAggregateWithObject()
    {
        $this->markTestIncomplete();
    }

    public function testCount()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->batchInsert(
            [
                ['field' => 1],
                ['field' => 2],
                ['field' => 3],
                ['field' => 4]
            ]
        );

        $entity = new Foo(1);

        $this->connection->getCollection('foo')->remove($entity);
        $result = $this->connection->getCollection('foo')->count(['field' => ['$exists' => 1, '$gt' => 2]]);

        $this->assertEquals(2, $result);
    }

    public function testDistinct()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->batchInsert(
            [
                ['field' => 1],
                ['field' => 1],
                ['field' => 2],
                ['field' => 2]
            ]
        );

        $entity = new Foo(1);

        $this->connection->getCollection('foo')->remove($entity);
        $result = $this->connection->getCollection('foo')->distinct('field', []);

        sort($result);
        $this->assertEquals([1, 2], $result);
    }

    public function testGroup()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->batchInsert(
            [
                ['field' => 1],
                ['field' => 1],
                ['field' => 2],
                ['field' => 2]
            ]
        );

        $entity = new Foo(1);

        $this->connection->getCollection('foo')->remove($entity);
        $result = $this->connection->getCollection('foo')->group(
            [],
            ['count' => 0],
            new \MongoCode('function(elem, agg) { agg.count++ }'),
            []
        );

        $expected = [
            'retval' => [['count' => 4]],
            'count' => 4,
            'keys' => 1,
            'ok' => 1
        ];

        $this->assertEquals($expected, $result);
    }

}
