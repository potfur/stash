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
use Fake\Bar;
use Fake\Yada;

class CollectionTest extends IntegrationTestCase
{
    /**
     * @var Foo
     */
    private $entity;

    public function setUp()
    {
        parent::setUp();
        $this->connection->getCollection('foo')->remove();

        $this->entity = new Foo(
            [
                '_id' => null,
                'int' => 1,
                'str' => 'foo',
                'bool' => true,
                'date' => new \DateTime(),
                'array' => ['foo' => 1, 'bar' => 2],
                'yadas' => [
                    'foo' => new Yada(['yada' => 1]),
                    'bar' => new Yada(['yada' => 2])
                ],
                'object' => new Bar(['foo' => 'foo', 'bar' => 'bar'])
            ]
        );
    }

    public function testInsert()
    {
        $foo = $this->connection->getCollection('foo');
        $foo->insert($this->entity);
        $result = $foo->findOne();

        $this->assertEquals($this->entity, $result);
    }

    public function testSaveWhenInsertingEntity()
    {
        $foo = $this->connection->getCollection('foo');
        $foo->save($this->entity);
        $result = $foo->findOne();

        $this->assertEquals($this->entity, $result);
    }

    public function testSaveWhenUpdatingEntity()
    {
        $foo = $this->connection->getCollection('foo');
        $foo->save($this->entity);
        $foo->save($this->entity);
        $result = $foo->find();

        $this->assertEquals(1, $result->count());
        foreach ($result as $element) {
            $this->assertEquals($this->entity, $element);
        }
    }
}
