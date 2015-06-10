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

class DateTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $field = new Date('foo');
        $this->assertEquals('foo', $field->getName());
    }

    public function testType()
    {
        $field = new Date('foo');
        $this->assertEquals(Fields::TYPE_DATE, $field->getType());
    }

    public function testGetElementType()
    {
        $field = new Date('foo');
        $this->assertNull($field->getElementType());
    }
}
