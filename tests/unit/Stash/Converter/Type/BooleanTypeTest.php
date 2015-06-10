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

class BooleanTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function testConvertToDatabaseValue($value, $expected)
    {
        $type = new BooleanType();
        $this->assertSame($expected, $type->convertToDatabaseValue($value));
    }

    /**
     * @dataProvider valueProvider
     */
    public function testConvertToPHPValue($value, $expected)
    {
        $type = new BooleanType();
        $this->assertSame($expected, $type->convertToPHPValue($value));
    }

    public function valueProvider()
    {
        return [
            [null, false],
            [0, false],
            [false, false],
            [1, true],
            [1.5, true],
            ['foo', true],
        ];
    }
}
