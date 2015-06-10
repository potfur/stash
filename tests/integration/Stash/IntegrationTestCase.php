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

use Stash\Converter\Converter;
use Stash\Model\Field\ArrayOf;
use Stash\Model\Field\Date;
use Stash\Model\Field\Document;
use Stash\Model\Field\Scalar;
use Stash\Model\Field\Id;
use Stash\Model\Model;

abstract class IntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModelCollection
     */
    protected $models;

    /**
     * @var DocumentConverter
     */
    protected $converter;

    /**
     * @var Connection
     */
    protected $connection;

    public function setUp()
    {
        $this->models = new ModelCollection(
            [
                'foo' => new Model(
                    '\Fake\Foo',
                    [
                        new Id(),
                        new Scalar('int', Fields::TYPE_INTEGER),
                        new Scalar('str', Fields::TYPE_STRING),
                        new Scalar('bool', Fields::TYPE_BOOLEAN),
                        new Date('date', Fields::TYPE_DATE),
                        new ArrayOf('array', Fields::TYPE_INTEGER),
                        new ArrayOf('yadas', Fields::TYPE_DOCUMENT),
                        new Document('object'),
                    ]
                ),
                new Model(
                    '\Fake\Bar',
                    [
                        new Scalar('foo', Fields::TYPE_STRING),
                        new Scalar('bar', Fields::TYPE_STRING),
                    ]
                ),
                new Model(
                    '\Fake\Yada',
                    [
                        new Scalar('yada', Fields::TYPE_STRING),
                    ]
                ),
            ]
        );

        $this->converter = new DocumentConverter(new Converter($this->models->getClasses()), $this->models);

        $this->connection = new Connection(new \MongoClient(), $this->converter);
        $this->connection->selectDB('test');
    }
}
