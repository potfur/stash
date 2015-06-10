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

class IdTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $field = new Id();
        $this->assertEquals('_id', $field->getName());
    }

    public function testType()
    {
        $field = new Id();
        $this->assertEquals(Fields::TYPE_ID, $field->getType());
    }

    public function testGetElementType()
    {
        $field = new Id();
        $this->assertNull($field->getElementType());
    }
}
