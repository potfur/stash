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
     * @dataProvider dateProvider
     */
    public function testConvertToDatabaseValue($value, $expected)
    {
        $type = new DateType();
        $this->assertEquals($expected, $type->convertToDatabaseValue($value));
    }

    /**
     * @dataProvider dateProvider
     */
    public function testConvertToPHPValue($expected, $value)
    {
        $type = new DateType();
        $this->assertEquals($expected, $type->convertToPHPValue($value));
    }

    public function dateProvider()
    {
        return [
            [null, null],
            [new \DateTime('2015-07-26 14:16:00', new \DateTimeZone('UTC')), new \MongoDate(strtotime('2015-07-26 14:16:00'))],
            [new \DateTime('2015-07-26 16:16:00', new \DateTimeZone('Europe/Berlin')), new \MongoDate(strtotime('2015-07-26 14:16:00'))],
        ];
    }
}
