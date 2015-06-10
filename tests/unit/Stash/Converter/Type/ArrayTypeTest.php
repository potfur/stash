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

class ArrayTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function testConvertToDatabaseValue($value, $expected)
    {
        $type = new ArrayType();
        $this->assertSame($expected, $type->convertToDatabaseValue($value));
    }

    /**
     * @dataProvider valueProvider
     */
    public function testConvertToPHPValue($value, $expected)
    {
        $type = new ArrayType();
        $this->assertSame($expected, $type->convertToPHPValue($value));
    }

    public function valueProvider()
    {
        return [
            [null, []],
            [1, [1]],
            [1.5, [1.5]],
            ['foo', ['foo']],
            [['foo', 'bar'], ['foo', 'bar']],
        ];
    }
}
