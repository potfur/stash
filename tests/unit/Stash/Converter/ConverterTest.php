<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash\Converter;

use Stash\Fields;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertToDatabaseValue()
    {
        $type = $this->getMock('\Stash\Converter\TypeInterface');
        $type->expects($this->any())->method('getType')->willReturn('testType');
        $type->expects($this->any())->method('convertToDatabaseValue')->willReturn('bar');

        $converter = new Converter([$type]);
        $this->assertEquals('bar', $converter->convertToDatabaseValue('foo', 'testType'));
    }

    /**
     * @expectedException \Stash\Converter\UnknownTypeException
     * @expectedExceptionMessage
     */
    public function testMissingConverterToDatabaseValue()
    {
        $converter = new Converter([]);
        $converter->convertToDatabaseValue('foo', 'foo');
    }

    public function testConvertToPHPValue()
    {
        $type = $this->getMock('\Stash\Converter\TypeInterface');
        $type->expects($this->any())->method('getType')->willReturn('testType');
        $type->expects($this->any())->method('convertToPHPValue')->willReturn('bar');

        $converter = new Converter([$type]);
        $this->assertEquals('bar', $converter->convertToPHPValue('foo', 'testType'));
    }

    /**
     * @expectedException \Stash\Converter\UnknownTypeException
     * @expectedExceptionMessage Unknown type converter
     */
    public function testMissingConverterToPHPValue()
    {
        $converter = new Converter([]);
        $converter->convertToPHPValue('foo', 'foo');
    }
}
