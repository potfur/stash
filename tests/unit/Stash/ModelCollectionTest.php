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

    public function testGetByCollection()
    {
        $model = $this->getMock('\Stash\ModelInterface');
        $model->expects($this->any())->method('getClass')->willReturn('stdClass');
        $model->expects($this->any())->method('getCollection')->willReturn('stdclass');

        $collection = new ModelCollection([$model]);

        $this->assertEquals($model, $collection->getByCollection('stdclass'));
    }

    /**
     * @expectedException \Stash\Model\ModelException
     * @expectedExceptionMessage Model with collection "foo" not found
     */
    public function testGetUndefinedByCollection()
    {
        $model = $this->getMock('\Stash\ModelInterface');
        $model->expects($this->any())->method('getClass')->willReturn('stdClass');
        $model->expects($this->any())->method('getCollection')->willReturn('stdclass');

        $collection = new ModelCollection([$model]);

        $this->assertEquals($model, $collection->getByCollection('foo'));
    }
}
