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

class IdTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function testConvertToDatabaseValue($value, $expected)
    {
        $type = new IdType();
        $this->assertEquals($expected, $type->convertToDatabaseValue($value));
    }

    /**
     * @dataProvider valueProvider
     */
    public function testConvertToPHPValue($value, $expected)
    {
        $type = new IdType();
        $this->assertEquals($expected, $type->convertToPHPValue($value));
    }

    public function valueProvider()
    {
        return [
            [null, null],
            ['49a7011a05c677b9a916612a', new \MongoId('49a7011a05c677b9a916612a')],
            [new \MongoId('49a7011a05c677b9a916612a'), new \MongoId('49a7011a05c677b9a916612a')],
        ];
    }
}
