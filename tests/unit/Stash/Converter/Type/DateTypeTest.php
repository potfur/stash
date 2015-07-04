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

class DateTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testType()
    {
        $type = new DateType();
        $this->assertEquals('date', $type->getType());
    }

    /**
     * @dataProvider phpValueProvider
     */
    public function testConvertToDatabaseValue($value, $expected)
    {
        $type = new DateType();
        $this->assertEquals($expected, $type->convertToDatabaseValue($value));
    }

    /**
     * @dataProvider databaseValueProvider
     */
    public function testConvertToPHPValue($value, $expected)
    {
        $type = new DateType();
        $this->assertEquals($expected, $type->convertToPHPValue($value));
    }

    public function phpValueProvider()
    {
        $date = date('Y-m-d H:i:s');
        $datetime = new \DateTime($date);
        $mongodate = new \MongoDate(strtotime($date));

        return [
            [null, null],
            [$datetime, $mongodate],
        ];
    }

    public function databaseValueProvider()
    {
        $date = date('Y-m-d H:i:s');
        $datetime = new \DateTime($date);
        $mongodate = new \MongoDate(strtotime($date));

        return [
            [null, null],
            [$mongodate, $datetime],
        ];
    }
}
