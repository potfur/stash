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

    /**
     * Initializes connection and clears test database
     *
     * @param Model $model
     */
    private function initialize(Model $model)
    {
        $this->models = new ModelCollection([$model]);

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

        $this->converter = new DocumentConverter($converter, $referencer, $this->models);

        $this->connection = new Connection(new \MongoClient(), $this->models, $this->converter);
        $this->connection->selectDB('test');

        $this->connection->getCollection('foo')->remove();

        $this->entity = new Foo(null, 'foo bar');
    }

    public function testInsert()
    {
        $this->initialize(
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

        $this->assertEquals($this->entity, $result);
    }

    public function testSaveWhenInsertingEntity()
    {
        $this->initialize(
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

        $this->assertEquals($this->entity, $result);
    }

    public function testSaveWhenUpdatingEntity()
    {
        $this->initialize(
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
            $this->assertEquals($this->entity, $element);
        }
    }

    public function testReference()
    {
        $this->initialize(
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
        $this->initialize(
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
}
