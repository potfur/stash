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

class DocumentConverterTest extends IntegrationTestCase
{

    public function testConvertToDatabaseValue()
    {
        $id = new \MongoId();
        $phpDate = new \DateTime();
        $mongoDate = new \MongoDate($phpDate->getTimestamp(), 0);

        $entity = new Foo(
            [
                '_id' => $id,
                'int' => 1,
                'str' => 'foo',
                'bool' => true,
                'date' => $phpDate,
                'array' => ['foo' => 1, 'bar' => 2],
                'yadas' => [
                    'foo' => new Yada(['yada' => 1]),
                    'bar' => new Yada(['yada' => 2])
                ],
                'object' => new Bar(['foo' => 'foo', 'bar' => 'bar'])
            ]
        );

        $expected = [
            '_id' => $id,
            '_class' => 'Fake\Foo',
            'int' => 1,
            'str' => 'foo',
            'bool' => true,
            'date' => $mongoDate,
            'array' => ['foo' => 1, 'bar' => 2],
            'yadas' => [
                'foo' => [
                    '_class' => 'Fake\Yada',
                    'yada' => 1
                ],
                'bar' => [
                    '_class' => 'Fake\Yada',
                    'yada' => 2
                ]
            ],
            'object' => [
                '_class' => 'Fake\Bar',
                'foo' => 'foo',
                'bar' => 'bar'
            ]
        ];

        $result = $this->converter->convertToDatabaseValue($entity);

        $this->assertEquals($expected, $result);
    }

    public function testConvertToPHPValue()
    {
        $id = new \MongoId();
        $phpDate = new \DateTime();
        $mongoDate = new \MongoDate($phpDate->getTimestamp(), 0);

        $expected = new Foo(
            [
                '_id' => $id,
                'int' => 1,
                'str' => 'foo',
                'bool' => true,
                'date' => $phpDate,
                'array' => ['foo' => 1, 'bar' => 2],
                'yadas' => [
                    'foo' => new Yada(['yada' => 1]),
                    'bar' => new Yada(['yada' => 2])
                ],
                'object' => new Bar(['foo' => 'foo', 'bar' => 'bar'])
            ]
        );

        $entity = [
            '_id' => $id,
            '_class' => 'Fake\Foo',
            'int' => 1,
            'str' => 'foo',
            'bool' => true,
            'date' => $mongoDate,
            'array' => ['foo' => 1, 'bar' => 2],
            'yadas' => [
                'foo' => [
                    '_class' => 'Fake\Yada',
                    'yada' => 1
                ],
                'bar' => [
                    '_class' => 'Fake\Yada',
                    'yada' => 2
                ]
            ],
            'object' => [
                '_class' => 'Fake\Bar',
                'foo' => 'foo',
                'bar' => 'bar'
            ]
        ];

        $result = $this->converter->convertToPHPValue($entity);

        $this->assertEquals($expected, $result);
    }

    public function testConvertToPHPWithoutClassData()
    {
        $id = new \MongoId();
        $phpDate = new \DateTime();
        $mongoDate = new \MongoDate($phpDate->getTimestamp(), 0);

        $document = [
            '_id' => $id,
            'foo' => true,
            'bar' => $mongoDate,
        ];

        $expected = new \stdClass();
        $expected->_id = $id;
        $expected->foo = true;
        $expected->bar = $phpDate;

        $result = $this->converter->convertToPHPValue($document);
        $this->assertEquals($expected, $result);
    }
}
