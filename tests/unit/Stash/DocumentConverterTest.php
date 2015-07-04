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
use Stash\Model\Field\ArrayOf;
use Stash\Model\Field\Document;
use Stash\Model\Field\Id;
use Stash\Model\Field\Reference;
use Stash\Model\Field\Scalar;
use Stash\Model\Model;


class DocumentConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;

    /**
     * @var ReferenceResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $referencer;

    /**
     * @var ModelCollection
     */
    private $models;

    public function setUp()
    {
        $this->converter = $this->getMock('\Stash\ConverterInterface');
        $this->referencer = $this->getMock('\Stash\ReferenceResolverInterface');
        $this->models = new ModelCollection();
    }

    public function testConnect()
    {
        $connection = $this->getMockBuilder('\Stash\Connection')->disableOriginalConstructor()->getMock();

        $this->referencer->expects($this->once())->method('connect')->with($connection);

        $converter = new DocumentConverter($this->converter, $this->referencer, $this->models);
        $converter->connect($connection);
    }

    public function testConvertEntityWithSimpleToDatabaseValue()
    {
        $entity = new Foo(null, 'foo');
        $model = new Model('\Fake\Foo', [new Id(), new Scalar('field', Fields::TYPE_STRING)]);
        $this->models->register($model);

        $this->converter->expects($this->exactly(2))->method('convertToDatabaseValue')->willReturnMap(
            [
                [$entity, Fields::TYPE_DOCUMENT, ['_class' => 'Fake\Foo', 'field' => 'foo']],
                ['foo', Fields::TYPE_STRING, 'foo']
            ]
        );

        $converter = new DocumentConverter($this->converter, $this->referencer, $this->models);
        $result = $converter->convertToDatabaseValue($entity);

        $this->assertEquals(['_class' => 'Fake\Foo', 'field' => 'foo'], $result);
    }

    public function testConvertEntityWithArrayToDatabaseValue()
    {
        $entity = new Foo(null, [1]);
        $model = new Model('\Fake\Foo', [new Id(), new ArrayOf('field', Fields::TYPE_INTEGER)]);
        $this->models->register($model);

        $this->converter->expects($this->exactly(3))->method('convertToDatabaseValue')->willReturnMap(
            [
                [$entity, Fields::TYPE_DOCUMENT, ['_class' => 'Fake\Foo', 'field' => [1]]],
                [[1], Fields::TYPE_ARRAY, [1]],
                [1, Fields::TYPE_INTEGER, '1'],
            ]
        );

        $converter = new DocumentConverter($this->converter, $this->referencer, $this->models);
        $result = $converter->convertToDatabaseValue($entity);

        $this->assertEquals(['_class' => 'Fake\Foo', 'field' => ['1']], $result);
    }

    public function testConvertEntityWithSubDocumentToDatabaseValue()
    {
        $subEntity = new \stdClass();
        $entity = new Foo(null, $subEntity);
        $model = new Model('\Fake\Foo', [new Id(), new Document('field')]);
        $this->models->register($model);

        $model = new Model('\stdClass', []);
        $this->models->register($model);

        $this->converter->expects($this->exactly(2))->method('convertToDatabaseValue')->willReturnMap(
            [
                [$entity, Fields::TYPE_DOCUMENT, ['_class' => 'Fake\Foo', 'field' => $subEntity]],
                [$subEntity, Fields::TYPE_DOCUMENT, ['_class' => 'stdClass']],
            ]
        );

        $converter = new DocumentConverter($this->converter, $this->referencer, $this->models);
        $result = $converter->convertToDatabaseValue($entity);

        $this->assertEquals(['_class' => 'Fake\Foo', 'field' => ['_class' => 'stdClass']], $result);
    }

    public function testConvertEntityWithReferenceToDatabaseValue()
    {
        $entity = new Foo(null, null);
        $model = new Model('\Fake\Foo', [new Id(), new Reference('field')]);
        $this->models->register($model);

        $this->converter->expects($this->once())->method('convertToDatabaseValue')->willReturnMap(
            [
                [$entity, Fields::TYPE_DOCUMENT, ['_class' => 'Fake\Foo', 'field' => null]]
            ]
        );

        $this->referencer->expects($this->once())->method('store')->willReturn(null);

        $converter = new DocumentConverter($this->converter, $this->referencer, $this->models);
        $result = $converter->convertToDatabaseValue($entity);

        $this->assertEquals(['_class' => 'Fake\Foo'], $result);
    }

    public function testConvertToPHPValue()
    {
        $entity = new Foo(null, 'foo');
        $model = new Model('\Fake\Foo', [new Id(), new Scalar('field', Fields::TYPE_STRING)]);
        $this->models->register($model);

        $this->converter->expects($this->exactly(2))->method('convertToPHPValue')->willReturnMap(
            [
                ['foo', Fields::TYPE_STRING, 'foo'],
                [['_class' => 'Fake\Foo', 'field' => 'foo'], Fields::TYPE_DOCUMENT, $entity]
            ]
        );

        $converter = new DocumentConverter($this->converter, $this->referencer, $this->models);
        $result = $converter->convertToPHPValue(['_class' => 'Fake\Foo', 'field' => 'foo']);

        $this->assertEquals($entity, $result);
    }

    public function testConvertEntityWithArrayToPHPValue()
    {
        $entity = new Foo(null, [1]);
        $model = new Model('\Fake\Foo', [new Id(), new ArrayOf('field', Fields::TYPE_INTEGER)]);
        $this->models->register($model);

        $this->converter->expects($this->exactly(3))->method('convertToPHPValue')->willReturnMap(
            [
                [1, Fields::TYPE_INTEGER, 1],
                [[1], Fields::TYPE_ARRAY, [1]],
                [['_class' => 'Fake\Foo', 'field' => [1]], Fields::TYPE_DOCUMENT, $entity]
            ]
        );

        $converter = new DocumentConverter($this->converter, $this->referencer, $this->models);
        $result = $converter->convertToPHPValue(['_class' => 'Fake\Foo', 'field' => [1]]);

        $this->assertEquals($entity, $result);
    }

    public function testConvertEntityWithSubDocumentToPHPValue()
    {
        $subEntity = new \stdClass();
        $entity = new Foo(null, $subEntity);
        $model = new Model('\Fake\Foo', [new Id(), new Document('field')]);
        $this->models->register($model);

        $model = new Model('\stdClass', []);
        $this->models->register($model);

        $this->converter->expects($this->exactly(2))->method('convertToPHPValue')->willReturnMap(
            [
                [['_class' => 'stdClass'], Fields::TYPE_DOCUMENT, $subEntity],
                [['_class' => 'Fake\Foo', 'field' => $subEntity], Fields::TYPE_DOCUMENT, $entity],
            ]
        );

        $converter = new DocumentConverter($this->converter, $this->referencer, $this->models);
        $result = $converter->convertToPHPValue(['_class' => 'Fake\Foo', 'field' => ['_class' => 'stdClass']]);

        $this->assertEquals($entity, $result);
    }

    public function testConvertEntityWithReferenceToPHPValue()
    {
        $entity = new Foo(null, null);
        $model = new Model('\Fake\Foo', [new Id(), new Reference('field')]);
        $this->models->register($model);

        $this->converter->expects($this->once())->method('convertToPHPValue')->willReturnMap(
            [
                [['_class' => 'Fake\Foo', 'field' => null], Fields::TYPE_DOCUMENT, $entity]
            ]
        );

        $this->referencer->expects($this->once())->method('resolve')->willReturn(null);

        $converter = new DocumentConverter($this->converter, $this->referencer, $this->models);
        $result = $converter->convertToPHPValue(['_class' => 'Fake\Foo', 'field' => null]);

        $this->assertEquals($entity, $result);
    }

    public function testConvertDocumentWithoutClassName()
    {
        $date = date('Y-m-d H:i:s');
        $datetime = new \DateTime($date);
        $mongodate = new \MongoDate(strtotime($date));

        $entity = new \stdClass();
        $entity->field = $datetime;

        $this->converter->expects($this->exactly(2))->method('convertToPHPValue')->willReturnMap(
            [
                [$mongodate, Fields::TYPE_DATE, $datetime],
                [['field' => $datetime], Fields::TYPE_DOCUMENT, $entity]
            ]
        );

        $converter = new DocumentConverter($this->converter, $this->referencer, $this->models);
        $result = $converter->convertToPHPValue(['field' => $mongodate]);

        $this->assertEquals($entity, $result);
    }
}
