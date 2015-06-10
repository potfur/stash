<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash\Model\Field;

use Stash\Fields;

class ArrayOfTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $field = new ArrayOf('foo', 'integer');
        $this->assertEquals('foo', $field->getName());
    }

    public function testType()
    {
        $field = new ArrayOf('foo', 'string');
        $this->assertEquals(Fields::TYPE_ARRAY, $field->getType());
    }

    /**
     * @dataProvider elementTypeProvider
     */
    public function testGetElementType($type)
    {
        $field = new ArrayOf('foo', $type);
        $this->assertEquals($type, $field->getElementType());
    }

    public function elementTypeProvider()
    {
        return [
            [Fields::TYPE_ID],
            [Fields::TYPE_BOOLEAN],
            [Fields::TYPE_INTEGER],
            [Fields::TYPE_DOUBLE],
            [Fields::TYPE_STRING],
            [Fields::TYPE_DATE],
            [Fields::TYPE_ARRAY],
            [Fields::TYPE_DOCUMENT],
        ];
    }
}
