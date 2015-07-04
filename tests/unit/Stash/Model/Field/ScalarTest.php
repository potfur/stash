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

class ScalarTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $field = new Scalar('foo', 'integer');
        $this->assertEquals('foo', $field->getName());
    }

    /**
     * @expectedException \Stash\Model\ModelException
     * @expectedExceptionMessage Invalid type for scalar field, got "foo"
     */
    public function testInvalidType()
    {
        new Scalar('foo', 'foo');
    }

    /**
     * @dataProvider typeProvider
     */
    public function testType($type)
    {
        $field = new Scalar('foo', $type);
        $this->assertEquals($type, $field->getType());
    }

    public function typeProvider()
    {
        return [
            [Fields::TYPE_BOOLEAN],
            [Fields::TYPE_INTEGER],
            [Fields::TYPE_DECIMAL],
            [Fields::TYPE_STRING],
        ];
    }

    public function testGetElementType()
    {
        $field = new Scalar('foo', 'string');
        $this->assertNull($field->getElementType());
    }
}
