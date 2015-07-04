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

class StringTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testType()
    {
        $type = new StringType();
        $this->assertEquals('string', $type->getType());
    }

    /**
     * @dataProvider valueProvider
     */
    public function testConvertToDatabaseValue($value, $expected)
    {
        $type = new StringType();
        $this->assertSame($expected, $type->convertToDatabaseValue($value));
    }

    /**
     * @dataProvider valueProvider
     */
    public function testConvertToPHPValue($value, $expected)
    {
        $type = new StringType();
        $this->assertSame($expected, $type->convertToPHPValue($value));
    }

    public function valueProvider()
    {
        return [
            [null, ''],
            [1, '1'],
            [1.5, '1.5'],
            ['foo', 'foo'],
        ];
    }
}
