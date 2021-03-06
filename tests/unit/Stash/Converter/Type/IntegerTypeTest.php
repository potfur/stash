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

class IntegerTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testType()
    {
        $type = new IntegerType();
        $this->assertEquals('integer', $type->getType());
    }

    /**
     * @dataProvider valueProvider
     */
    public function testConvertToDatabaseValue($value, $expected)
    {
        $type = new IntegerType();
        $this->assertSame($expected, $type->convertToDatabaseValue($value));
    }

    /**
     * @dataProvider valueProvider
     */
    public function testConvertToPHPValue($value, $expected)
    {
        $type = new IntegerType();
        $this->assertSame($expected, $type->convertToPHPValue($value));
    }

    public function valueProvider()
    {
        return [
            [null, (int) 0],
            [1, (int) 1],
            [1.5, (int) 1],
            ['foo', (int) 0],
        ];
    }
}
