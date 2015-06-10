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

use Fake\Bar;
use Fake\Foo;
use Fake\Yada;

class Fake
{
    public $foo;
}

class DocumentConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModelCollection
     */
    private $models;

    /**
     * @var ConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;

    public function setUp()
    {
        $this->models = new ModelCollection();
        $this->models->register(
            $this->buildModel(
                '\Fake\Foo',
                [
                    '_id' => Fields::TYPE_ID,
                    'int' => Fields::TYPE_INTEGER,
                    'str' => Fields::TYPE_STRING,
                    'bool' => Fields::TYPE_BOOLEAN,
                    'date' => Fields::TYPE_DATE,
                    'array' => Fields::TYPE_INTEGER . '[]',
                    'yadas' => Fields::TYPE_DOCUMENT . '[]',
                    'object' => Fields::TYPE_DOCUMENT,
                    'missing' => Fields::TYPE_STRING // for testing undefined properties
                ]
            )
        );

        $this->models->register(
            $this->buildModel(
                '\Fake\Bar',
                [
                    'foo' => Fields::TYPE_STRING,
                    'bar' => Fields::TYPE_STRING
                ]
            )
        );

        $this->models->register(
            $this->buildModel(
                '\Fake\Yada',
                [
                    'yada' => Fields::TYPE_STRING
                ]
            )
        );

        $this->converter = $this->getMock('\Stash\ConverterInterface');
    }

    /**
     * @param string $class
     * @param array  $fields
     *
     * @return ModelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function buildModel($class, array $fields)
    {
        $map = [];
        foreach ($fields as $name => $type) {
            $fieldMock = $this->getMock('\Stash\FieldInterface', ['getName', 'getType', 'getElementType']);
            $fieldMock->expects($this->any())->method('getName')->willReturn($name);
            $fieldMock->expects($this->any())->method('getType')->willReturn(strpos($type, '[]') !== false ? Fields::TYPE_ARRAY : trim($type, '[]'));
            $fieldMock->expects($this->any())->method('getElementType')->willReturn(trim($type, '[]'));

            $fields[$name] = $fieldMock;
            $map[] = [$name, $fieldMock];
        }

        $model = $this->getMock('\Stash\ModelInterface');
        $model->expects($this->any())->method('getClass')->willReturn(trim($class, '\\'));
        $model->expects($this->any())->method('hasField')->willReturnCallback(function ($name) use ($fields) { return isset($fields[$name]); });
        $model->expects($this->any())->method('getField')->willReturnMap($map);

        return $model;
    }

    public function testConvertToDatabaseValue()
    {
        $id = new \MongoId();
        $date = new \DateTime();

        $data = [
            '_id' => $id,
            '_class' => 'Fake\Foo',
            'int' => null,
            'str' => 'foo',
            'bool' => true,
            'date' => $date,
            'array' => ['foo' => 1, 'bar' => 2],
            'yadas' => [
                'foo' => new Yada(['yada' => '1']),
                'bar' => new Yada(['yada' => '2'])
            ],
            'object' => new Bar(['foo' => 'foo', 'bar' => 'bar'])
        ];
        $entity = new Foo($data);

        $document = [
            '_id' => $id,
            '_class' => 'Fake\Foo',
            'str' => 'foo',
            'bool' => true,
            'date' => new \MongoDate($date->getTimestamp()),
            'array' => ['foo' => 1, 'bar' => 2],
            'yadas' => [
                'foo' => [
                    '_class' => 'Fake\Yada',
                    'yada' => '1'
                ],
                'bar' => [
                    '_class' => 'Fake\Yada',
                    'yada' => '2'
                ]
            ],
            'object' => [
                '_class' => 'Fake\Bar',
                'foo' => 'foo',
                'bar' => 'bar'
            ]
        ];

        $this->converter->expects($this->any())->method('convertToDatabaseValue')->willReturnMap(
            [
                [$entity, Fields::TYPE_DOCUMENT, $data],
                [$id, Fields::TYPE_ID, $id],
                [1, Fields::TYPE_INTEGER, 1],
                ['foo', Fields::TYPE_INTEGER, 'foo'],
                [true, Fields::TYPE_BOOLEAN, true],
                [$data['date'], Fields::TYPE_DATE, $document['date']],

                [$data['array'], Fields::TYPE_ARRAY, $document['array']],
                [1, Fields::TYPE_INTEGER, 1],
                [2, Fields::TYPE_INTEGER, 2],

                [$data['yadas'], Fields::TYPE_ARRAY, $data['yadas']],

                [$data['yadas']['foo'], Fields::TYPE_DOCUMENT, $document['yadas']['foo']],
                ['1', Fields::TYPE_STRING, '1'],

                [$data['yadas']['bar'], Fields::TYPE_DOCUMENT, $document['yadas']['bar']],
                ['2', Fields::TYPE_STRING, '2'],

                [$data['object'], Fields::TYPE_DOCUMENT, $document['object']],
                ['foo', Fields::TYPE_STRING, 'foo'],
                ['bar', Fields::TYPE_STRING, 'bar']
            ]
        );

        $converter = new DocumentConverter($this->converter, $this->models);
        $result = $converter->convertToDatabaseValue($entity);

        $this->assertEquals($document, $result);
    }

    public function testConvertToPHPValue()
    {
        $id = new \MongoId();
        $date = new \DateTime();

        $data = [
            '_id' => $id,
            '_class' => 'Fake\Foo',
            'str' => 'foo',
            'bool' => true,
            'date' => $date,
            'array' => ['foo' => 1, 'bar' => 2],
            'yadas' => [
                'foo' => new Yada(['yada' => '1']),
                'bar' => new Yada(['yada' => '2'])
            ],
            'object' => new Bar(['foo' => 'foo', 'bar' => 'bar'])
        ];
        $entity = new Foo($data);

        $document = [
            '_id' => $id,
            '_class' => 'Fake\Foo',
            'str' => 'foo',
            'bool' => true,
            'date' => new \MongoDate($date->getTimestamp()),
            'array' => ['foo' => 1, 'bar' => 2],
            'yadas' => [
                'foo' => [
                    '_class' => 'Fake\Yada',
                    'yada' => '1'
                ],
                'bar' => [
                    '_class' => 'Fake\Yada',
                    'yada' => '2'
                ]
            ],
            'object' => [
                '_class' => 'Fake\Bar',
                'foo' => 'foo',
                'bar' => 'bar'
            ]
        ];

        $this->converter->expects($this->any())->method('convertToPHPValue')->willReturnMap(
            [
                [$data, Fields::TYPE_DOCUMENT, $entity],
                [$id, Fields::TYPE_ID, $id],
                [1, Fields::TYPE_INTEGER, 1],
                ['foo', Fields::TYPE_INTEGER, 'foo'],
                [true, Fields::TYPE_BOOLEAN, true],
                [$document['date'], Fields::TYPE_DATE, $data['date']],

                [$document['array'], Fields::TYPE_ARRAY, $document['array']],
                [1, Fields::TYPE_INTEGER, 1],
                [2, Fields::TYPE_INTEGER, 2],

                [$document['yadas'], Fields::TYPE_ARRAY, $document['yadas']],

                [$document['yadas']['foo'], Fields::TYPE_DOCUMENT, $data['yadas']['foo']],
                ['1', Fields::TYPE_STRING, '1'],

                [$document['yadas']['bar'], Fields::TYPE_DOCUMENT, $data['yadas']['bar']],
                ['2', Fields::TYPE_STRING, '2'],

                [$document['object'], Fields::TYPE_DOCUMENT, $data['object']],
                ['foo', Fields::TYPE_STRING, 'foo'],
                ['bar', Fields::TYPE_STRING, 'bar']
            ]
        );

        $converter = new DocumentConverter($this->converter, $this->models);
        $result = $converter->convertToPHPValue($document);

        $this->assertEquals($entity, $result);
    }

    public function testConvertToPHPValueWithoutClass()
    {
        $id = new \MongoId();
        $date = new \DateTime();

        $entity = new \stdClass();
        $entity->_id = $id;
        $entity->_class = 'Fake\Foo';
        $entity->str = 'foo';
        $entity->bool = true;
        $entity->date = $date;
        $entity->array = ['foo' => 1, 'bar' => 2];
        $entity->yadas = [
            'foo' => [
                '_class' => 'Fake\Yada',
                'yada' => '1'
            ],
            'bar' => [
                '_class' => 'Fake\Yada',
                'yada' => '2'
            ]
        ];
        $entity->object = [
            '_class' => 'Fake\Bar',
            'foo' => 'foo',
            'bar' => 'bar'
        ];

        $data = [
            '_id' => $id,
            'str' => 'foo',
            'bool' => true,
            'date' => $date,
            'array' => ['foo' => 1, 'bar' => 2],
            'yadas' => [
                'foo' => [
                    '_class' => 'Fake\Yada',
                    'yada' => '1'
                ],
                'bar' => [
                    '_class' => 'Fake\Yada',
                    'yada' => '2'
                ]
            ],
            'object' => [
                '_class' => 'Fake\Bar',
                'foo' => 'foo',
                'bar' => 'bar'
            ]
        ];

        $document = [
            '_id' => $id,
            'str' => 'foo',
            'bool' => true,
            'date' => new \MongoDate($date->getTimestamp()),
            'array' => ['foo' => 1, 'bar' => 2],
            'yadas' => [
                'foo' => [
                    '_class' => 'Fake\Yada',
                    'yada' => '1'
                ],
                'bar' => [
                    '_class' => 'Fake\Yada',
                    'yada' => '2'
                ]
            ],
            'object' => [
                '_class' => 'Fake\Bar',
                'foo' => 'foo',
                'bar' => 'bar'
            ]
        ];

        $this->converter->expects($this->any())->method('convertToPHPValue')->willReturnMap(
            [
                [$document['date'], Fields::TYPE_DATE, $entity->date],
                [$data, Fields::TYPE_DOCUMENT, $entity],
            ]
        );

        $converter = new DocumentConverter($this->converter, $this->models);
        $result = $converter->convertToPHPValue($document);

        $this->assertEquals($entity, $result);
    }
}
