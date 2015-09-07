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
use ProxyManager\Proxy\VirtualProxyInterface;
use Stash\Converter\Converter;
use Stash\Converter\Type\ArrayType;
use Stash\Converter\Type\BooleanType;
use Stash\Converter\Type\DateType;
use Stash\Converter\Type\DecimalType;
use Stash\Converter\Type\DocumentType;
use Stash\Converter\Type\IdType;
use Stash\Converter\Type\IntegerType;
use Stash\Converter\Type\StringType;
use Stash\Model\Field\ArrayOf;
use Stash\Model\Field\Reference;
use Stash\Model\Field\Scalar;
use Stash\Model\Field\Id;
use Stash\Model\Model;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
    private $stash;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

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
        $proxyAdapter = new ProxyAdapter(new \ProxyManager\Factory\LazyLoadingValueHolderFactory());

        $this->converter = new DocumentConverter($converter, $referencer, $this->models, $proxyAdapter);

        $subscriber = new \Fake\EventSubscriber(
            [
                \Stash\Events::FIND_AFTER,
                \Stash\Events::PERSIST_BEFORE,
                \Stash\Events::PERSIST_AFTER,
                \Stash\Events::REMOVE_BEFORE
            ]
        );

        $this->dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
        $this->dispatcher->addSubscriber($subscriber);

        $this->stash = new Connection($this->mongo, $this->converter, $this->dispatcher);
        $this->stash->selectDB('test');

        $this->stash->getCollection('foo')->remove();

        $this->entity = new Foo(null, 'foo bar');
    }

    public function testFind()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->insert(
            [
                '_class' => \Fake\Foo::class,
                'field' => 'foo'
            ]
        );

        $this->models->register(
            new Model(
                \Fake\Foo::class,
                [
                    new Id(),
                    new Scalar('field', Fields::TYPE_STRING),
                ],
                'foo'
            )
        );

        $result = $this->stash->getCollection('foo')->find();

        foreach ($result as $element) {
            $this->assertInstanceOf(\Fake\Foo::class, $element);
            $this->assertEquals('foo', $element->field);
        }
    }

    public function testFindOne()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->insert(
            [
                '_class' => \Fake\Foo::class,
                'field' => 'foo'
            ]
        );

        $this->models->register(
            new Model(
                \Fake\Foo::class,
                [
                    new Id(),
                    new Scalar('field', Fields::TYPE_STRING),
                ],
                'foo'
            )
        );

        $result = $this->stash->getCollection('foo')->findOne();

        $this->assertInstanceOf(\Fake\Foo::class, $result);
        $this->assertEquals('foo', $result->field);
    }

    public function testFindById()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->insert(
            [
                '_id' => 1,
                '_class' => \Fake\Foo::class,
                'field' => 'foo'
            ]
        );

        $this->models->register(
            new Model(
                \Fake\Foo::class,
                [
                    new Id(),
                    new Scalar('field', Fields::TYPE_STRING),
                ],
                'foo'
            )
        );

        $result = $this->stash->getCollection('foo')->findById(1);

        foreach ($result as $element) {
            $this->assertInstanceOf(\Fake\Foo::class, $element);
            $this->assertEquals('foo', $element->field);
        }
    }

    public function testInsert()
    {
        $this->models->register(
            new Model(
                \Fake\Foo::class,
                [
                    new Id(),
                    new Scalar('field', Fields::TYPE_STRING),
                ],
                'foo'
            )
        );

        $foo = $this->stash->getCollection('foo');
        $foo->insert($this->entity);
        $result = $foo->findOne();

        $this->assertInstanceOf(\Fake\Foo::class, $result);
        $this->assertEquals($this->entity->_id, $result->_id);
    }

    public function testSaveWhenInsertingEntity()
    {
        $this->models->register(
            new Model(
                \Fake\Foo::class,
                [
                    new Id(),
                    new Scalar('field', Fields::TYPE_STRING),
                ],
                'foo'
            )
        );

        $foo = $this->stash->getCollection('foo');
        $foo->save($this->entity);
        $result = $foo->findOne();

        $this->assertInstanceOf(\Fake\Foo::class, $result);
        $this->assertEquals($this->entity->_id, $result->_id);
    }

    public function testSaveWhenUpdatingEntity()
    {
        $this->models->register(
            new Model(
                \Fake\Foo::class,
                [
                    new Id(),
                    new Scalar('field', Fields::TYPE_STRING),
                ],
                'foo'
            )
        );

        $foo = $this->stash->getCollection('foo');
        $foo->save($this->entity);
        $foo->save($this->entity);
        $result = $foo->find();

        $this->assertEquals(1, $result->count());

        foreach ($result as $element) {
            $this->assertInstanceOf(\Fake\Foo::class, $element);
            $this->assertEquals($this->entity->_id, $element->_id);
        }
    }

    public function testReference()
    {
        $this->models->register(
            new Model(
                \Fake\Foo::class,
                [
                    new Id(),
                    new Reference('field'),
                ],
                'foo'
            )
        );

        $foo = $this->stash->getCollection('foo');

        $entityA = new Foo(null, null);
        $foo->save($entityA);

        $entityB = new Foo(null, $entityA);
        $foo->save($entityB);

        $result = $foo->findById($entityB->_id);

        $this->assertInstanceOf(\Fake\Foo::class, $result);
        $this->assertEquals($entityB->_id, $result->_id);
    }

    public function testLoopedReference()
    {
        $this->models->register(
            new Model(
                \Fake\Foo::class,
                [
                    new Id(),
                    new Reference('field'),
                ],
                'foo'
            )
        );

        $foo = $this->stash->getCollection('foo');

        $entityA = new Foo(null, null);
        $foo->save($entityA);

        $entityB = new Foo(null, $entityA);
        $foo->save($entityB);

        $entityA = $foo->findById($entityA->_id);
        $entityA->field = $entityB;
        $foo->save($entityA);

        $result = $foo->findById($entityB->_id);

        $this->assertInstanceOf(\Fake\Foo::class, $result);
        $this->assertEquals($entityB->_id, $result->_id);
    }

    public function testRemoveByCriteria()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->insert(
            [
                '_id' => 1,
                '_class' => \Fake\Foo::class,
                'field' => 'foo'
            ]
        );

        $this->stash->getCollection('foo')->remove(['_id' => 1]);
        $result = $this->stash->getCollection('foo')->find()->count();

        $this->assertEquals(0, $result);
    }

    public function testRemoveEntity()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->insert(
            [
                '_id' => 1,
                '_class' => \Fake\Foo::class,
                'field' => 'foo'
            ]
        );

        $entity = new Foo(1);

        $this->stash->getCollection('foo')->remove($entity);
        $result = $this->stash->getCollection('foo')->find()->count();

        $this->assertEquals(0, $result);
    }

    public function testAggregateRaw()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->insert(
            [
                'title' => 'aggregation',
                'field' => 'bob',
                'tag' => ['fun', 'good', 'fun'],
                'comments' => [
                    [
                        'field' => 'joe',
                        'text' => 'this is cool',
                    ],
                    [
                        'field' => 'sam',
                        'text' => 'this is bad',
                    ],
                ],
                'other' => [
                    'foo' => 5,
                ]
            ]
        );

        $pipeline = [
            [
                '$project' => [
                    'field' => 1,
                    'tag' => 1,
                ]
            ],
            ['$unwind' => '$tag'],
            [
                '$group' => [
                    '_id' => ['tag' => '$tag'],
                    'field' => ['$addToSet' => '$field'],
                ],
            ],
        ];

        $expected = [
            [
                '_id' => ['tag' => 'good'],
                'field' => ['bob']
            ],
            [
                '_id' => ['tag' => 'fun'],
                'field' => ["bob"]
            ]
        ];

        $results = $this->stash->getCollection('foo')->aggregate($pipeline, [], null);

        $this->assertEquals($expected, $results);
    }

    public function testAggregateWithObject()
    {
        $this->mongo->selectDB('test')->selectCollection('foo')->insert(
            [
                'title' => 'aggregation',
                'field' => 'bob',
                'tag' => ['fun', 'good', 'fun'],
                'comments' => [
                    [
                        'field' => 'joe',
                        'text' => 'this is cool',
                    ],
                    [
                        'field' => 'sam',
                        'text' => 'this is bad',
                    ],
                ],
                'other' => [
                    'foo' => 5,
                ]
            ]
        );

        $pipeline = [
            [
                '$project' => [
                    'field' => 1,
                    'tag' => 1,
                ]
            ],
            ['$unwind' => '$tag'],
            [
                '$group' => [
                    '_id' => ['tag' => '$tag'],
                    'field' => ['$addToSet' => '$field'],
                ],
            ],
        ];

        $expected = [
            new Foo(['tag' => 'good'], ['bob']),
            new Foo(['tag' => 'fun'], ["bob"])
        ];

        $this->models->register(
            new Model(
                \Fake\Foo::class,
                [
                    new ArrayOf('_id', Fields::TYPE_STRING),
                    new ArrayOf('field', Fields::TYPE_STRING),
                ],
                'foo'
            )
        );

        $results = $this->stash->getCollection('foo')->aggregate($pipeline, [], Foo::class);

        array_walk($results, function(VirtualProxyInterface &$result) {
            $result->initializeProxy();
            $result = $result->getWrappedValueHolderValue();
        });

        $this->assertEquals($expected, $results);
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

        $this->stash->getCollection('foo')->remove($entity);
        $result = $this->stash->getCollection('foo')->count(['field' => ['$exists' => 1, '$gt' => 2]]);

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

        $this->stash->getCollection('foo')->remove($entity);
        $result = $this->stash->getCollection('foo')->distinct('field', []);

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

        $this->stash->getCollection('foo')->remove($entity);
        $result = $this->stash->getCollection('foo')->group(
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
