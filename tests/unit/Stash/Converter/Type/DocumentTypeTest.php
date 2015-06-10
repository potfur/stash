<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash\Converter\Type;

class FakeObject
{
    public $foo;
    protected $bar;
    private $yada;

    public function __construct($foo, $bar, $yada)
    {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->yada = $yada;
    }
}

class DocumentTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertNullToDatabaseValue()
    {
        $converter = new DocumentType('\Stash\Converter\Type\FakeObject');
        $this->assertNull($converter->convertToDatabaseValue(null));
    }

    public function testConvertNullToPHPValue()
    {
        $converter = new DocumentType('\Stash\Converter\Type\FakeObject');
        $this->assertNull($converter->convertToPHPValue(null));
    }

    public function testConvertToDatabaseValue()
    {
        $value = new FakeObject('foo', 'bar', 'yada');

        $expected = [
            '_class' => 'Stash\Converter\Type\FakeObject',
            'foo' => 'foo',
            'bar' => 'bar',
            'yada' => 'yada',
        ];

        $converter = new DocumentType();
        $this->assertEquals($expected, $converter->convertToDatabaseValue($value));
    }

    public function testConvertToPHPValue()
    {
        $value = [
            '_class' => 'Stash\Converter\Type\FakeObject',
            'foo' => 'foo',
            'bar' => 'bar',
            'yada' => 'yada',
        ];

        $expected = new FakeObject('foo', 'bar', 'yada');

        $converter = new DocumentType();
        $this->assertEquals($expected, $converter->convertToPHPValue($value));
    }

    public function testConvertWithoutClass()
    {
        $value = [
            'foo' => 'foo',
            'bar' => 'bar',
            'yada' => 'yada',
        ];

        $expected = new \stdClass();
        $expected->foo = 'foo';
        $expected->bar = 'bar';
        $expected->yada = 'yada';

        $converter = new DocumentType();
        $this->assertEquals($expected, $converter->convertToPHPValue($value));
    }
}
