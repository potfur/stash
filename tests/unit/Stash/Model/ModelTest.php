<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash\Model;


class ModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Stash\FieldInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $field;

    public function setUp()
    {
        $this->field = $this->getMock('\Stash\FieldInterface');
    }

    public function testFields()
    {
        $this->field->expects($this->any())->method('getName')->willReturn('foo');

        $model = new Model('\stdClass', []);
        $model->addField($this->field);

        $this->assertEquals(['foo' => $this->field], $model->getFields());
    }

    public function testHasField()
    {
        $this->field->expects($this->any())->method('getName')->willReturn('foo');

        $model = new Model('\stdClass', [$this->field]);
        $this->assertTrue($model->hasField('foo'));
    }

    /**
     * @expectedException \Stash\Model\ModelException
     * @expectedExceptionMessage Unable to resolve field definition for name "foo"
     */
    public function testGetUndefinedField()
    {
        $model = new Model('\stdClass');
        $model->getField('foo');
    }

    public function testGetField()
    {
        $this->field->expects($this->any())->method('getName')->willReturn('foo');

        $model = new Model('\stdClass', [$this->field]);
        $this->assertEquals($this->field, $model->getField('foo'));
    }

    public function testClass()
    {
        $model = new Model('\stdClass');
        $this->assertEquals('stdClass', $model->getClass());
    }

    public function testCollection()
    {
        $model = new Model('\stdClass', [], 'stdclass');
        $this->assertEquals('stdclass', $model->getCollection());
    }
}
