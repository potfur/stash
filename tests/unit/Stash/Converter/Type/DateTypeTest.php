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
    public function setUp()
    {
        date_default_timezone_set('Europe/Berlin');
    }

    public function testType()
    {
        $type = new DateType();
        $this->assertEquals('date', $type->getType());
    }

    /**
     * @dataProvider dateProvider
     */
    public function testConvertToDatabaseValue(\DateTime $value = null, \MongoDate $expected = null)
    {
        $type = new DateType();
        $this->assertEquals($expected, $type->convertToDatabaseValue($value));
    }

    /**
     * @dataProvider dateProvider
     */
    public function testConvertToPHPValue(\DateTime $expected = null, \MongoDate $value = null)
    {
        $timezone = $expected ? $expected->getTimezone() : null;

        $type = new DateType($timezone);
        $this->assertEquals($expected, $type->convertToPHPValue($value));
    }

    public function dateProvider()
    {
        $utc = new \DateTime('2015-07-26 14:16:00', new \DateTimeZone('UTC'));
        $berlin = new \DateTime('2015-07-26 16:16:00', new \DateTimeZone('Europe/Berlin'));

        $mongo = new \MongoDate($utc->getTimestamp(), 0);

        return [
            [null, null],
            [$utc, $mongo],
            [$berlin, $mongo],
        ];
    }
}
