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

class DecimalTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testType()
    {
        $type = new DecimalType();
        $this->assertEquals('decimal', $type->getType());
    }

    /**
     * @dataProvider valueProvider
     */
    public function testConvertToDatabaseValue($value, $expected)
    {
        $type = new DecimalType();
        $this->assertSame($expected, $type->convertToDatabaseValue($value));
    }

    /**
     * @dataProvider valueProvider
     */
    public function testConvertToPHPValue($value, $expected)
    {
        $type = new DecimalType();
        $this->assertSame($expected, $type->convertToPHPValue($value));
    }

    public function valueProvider()
    {
        return [
            [null, (float) 0],
            [1, (float) 1],
            [1.5, (float) 1.5],
            ['foo', (float) 0],
        ];
    }
}
