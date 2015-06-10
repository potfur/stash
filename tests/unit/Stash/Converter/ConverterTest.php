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
    /**
     * @dataProvider phpValueProvider
     */
    public function testConvertToDatabaseValue($type, $value, $expected)
    {
        $converter = new Converter(['\stdClass']);
        $this->assertEquals($expected, $converter->convertToDatabaseValue($value, $type));
    }

    public function phpValueProvider()
    {
        $date = date('Y-m-d H:i:s');
        $phpDate = new \DateTime($date);
        $mongoDate = new \MongoDate(strtotime($date));

        return [
            [Fields::TYPE_ARRAY, ['foo', 'bar'], ['foo', 'bar']],
            [Fields::TYPE_BOOLEAN, false, false],
            [Fields::TYPE_BOOLEAN, true, true],
            [Fields::TYPE_DATE, null, null],
            [Fields::TYPE_DATE, $phpDate, $mongoDate],
            [Fields::TYPE_DOUBLE, 1.5, (float) 1.5],
            [Fields::TYPE_ID, '49a7011a05c677b9a916612a', new \MongoId('49a7011a05c677b9a916612a')],
            [Fields::TYPE_ID, new \MongoId('49a7011a05c677b9a916612a'), new \MongoId('49a7011a05c677b9a916612a')],
            [Fields::TYPE_INTEGER, 1, (int) 1],
            [Fields::TYPE_STRING, 'foo', 'foo'],
        ];
    }

    /**
     * @expectedException \Stash\Converter\UnknownTypeException
     * @expectedExceptionMessage
     */
    public function testMissingConverterToDatabaseValue()
    {
        $converter = new Converter();
        $converter->convertToDatabaseValue('foo', 'foo');
    }

    /**
     * @dataProvider mongoValueProvider
     */
    public function testConvertToPHPValue($type, $value, $expected)
    {
        $converter = new Converter(['\stdClass']);
        $this->assertEquals($expected, $converter->convertToPHPValue($value, $type));
    }

    public function mongoValueProvider()
    {
        $date = date('Y-m-d H:i:s');
        $phpDate = new \DateTime($date);
        $mongoDate = new \MongoDate(strtotime($date));

        return [
            [Fields::TYPE_ARRAY, ['foo', 'bar'], ['foo', 'bar']],
            [Fields::TYPE_BOOLEAN, false, false],
            [Fields::TYPE_BOOLEAN, true, true],
            [Fields::TYPE_DATE, null, null],
            [Fields::TYPE_DATE, $mongoDate, $phpDate],
            [Fields::TYPE_DOUBLE, 1.5, (float) 1.5],
            [Fields::TYPE_ID, '49a7011a05c677b9a916612a', new \MongoId('49a7011a05c677b9a916612a')],
            [Fields::TYPE_ID, new \MongoId('49a7011a05c677b9a916612a'), new \MongoId('49a7011a05c677b9a916612a')],
            [Fields::TYPE_INTEGER, 1, (int) 1],
            [Fields::TYPE_STRING, 'foo', 'foo'],
        ];
    }

    /**
     * @expectedException \Stash\Converter\UnknownTypeException
     * @expectedExceptionMessage Unknown type converter
     */
    public function testMissingConverterToPHPValue()
    {
        $converter = new Converter();
        $converter->convertToPHPValue('foo', 'foo');
    }
}
