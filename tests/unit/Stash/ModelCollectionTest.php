<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash;


class ModelCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testAddModel()
    {
        $modelA = $this->getMock('\Stash\ModelInterface');
        $modelA->expects($this->any())->method('getClass')->willReturn('ClassA');

        $modelB = $this->getMock('\Stash\ModelInterface');
        $modelB->expects($this->any())->method('getClass')->willReturn('ClassB');

        $collection = new ModelCollection([$modelA]);
        $collection->register($modelB);

        $this->assertEquals(['ClassA' => $modelA, 'ClassB' => $modelB], $collection->getModels());
    }

    public function testGetClasses()
    {
        $modelA = $this->getMock('\Stash\ModelInterface');
        $modelA->expects($this->any())->method('getClass')->willReturn('ClassA');

        $modelB = $this->getMock('\Stash\ModelInterface');
        $modelB->expects($this->any())->method('getClass')->willReturn('ClassB');

        $collection = new ModelCollection([$modelA, $modelB]);

        $this->assertEquals(['ClassA', 'ClassB'], $collection->getClasses());
    }

    /**
     * @expectedException \Stash\Model\ModelException
     * @expectedExceptionMessage Model for "stdClass" not found
     */
    public function testGetUndefinedByClass()
    {
        $collection = new ModelCollection();
        $collection->getByClass('\stdClass');
    }

    /**
     * @dataProvider classNameProvider
     */
    public function testGetByClass($className)
    {
        $model = $this->getMock('\Stash\ModelInterface');
        $model->expects($this->any())->method('getClass')->willReturn('stdClass');

        $collection = new ModelCollection([$model]);

        $this->assertEquals($model, $collection->getByClass($className));
    }

    public function classNameProvider()
    {
        return [
            ['\stdClass'],
            ['stdClass']
        ];
    }

    /**
     * @expectedException \Stash\Model\ModelException
     * @expectedExceptionMessage Argument must be instance of object, got "string"
     */
    public function testGetByInstanceWithInvalidArgument()
    {
        $collection = new ModelCollection();
        $collection->getByInstance('\stdClass');
    }

    /**
     * @expectedException \Stash\Model\ModelException
     * @expectedExceptionMessage Model for "stdClass" not found
     */
    public function testGetUndefinedByInstance()
    {
        $collection = new ModelCollection();
        $collection->getByInstance(new \stdClass());
    }

    public function testGetByInstance()
    {
        $model = $this->getMock('\Stash\ModelInterface');
        $model->expects($this->any())->method('getClass')->willReturn('stdClass');

        $collection = new ModelCollection([$model]);

        $this->assertEquals($model, $collection->getByInstance(new \stdClass()));
    }
}
