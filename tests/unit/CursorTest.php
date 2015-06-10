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

use Fake\Yada;

class CursorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \MongoCursor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cursor;

    /**
     * @var ConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;

    /**
     * @var ModelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    public function setUp()
    {
        $this->cursor = $this->getMockBuilder('\MongoCursor')->disableOriginalConstructor()->getMock();
        $this->converter = $this->getMock('\Stash\DocumentConverterInterface');
        $this->model = $this->getMock('\Stash\ModelInterface');
    }

    public function testIterator()
    {
        $this->cursor->expects($this->any())->method('current')->willReturnOnConsecutiveCalls(
            ['yada' => 'foo'],
            ['yada' => 'bar']
        );
        $this->cursor->expects($this->any())->method('key')->willReturnOnConsecutiveCalls(
            0,
            1
        );
        $this->cursor->expects($this->any())->method('valid')->willReturnOnConsecutiveCalls(
            true,
            true,
            false
        );

        $this->converter->expects($this->any())->method('convertToPHPValue')->willReturnCallback(
            function ($data) {
                return new Yada($data);
            }
        );

        $cursor = new Cursor($this->cursor, $this->converter, $this->model);

        $result = [];
        foreach ($cursor as $key => $value) {
            $result[$key] = $value;
        }

        $expected = [
            0 => new Yada(['yada' => 'foo']),
            1 => new Yada(['yada' => 'bar'])
        ];

        $this->assertEquals($expected, $result);
    }

    public function testCall()
    {
        $this->cursor->expects($this->once())->method('explain')->with();

        $cursor = new Cursor($this->cursor, $this->converter, $this->model);
        $cursor->explain();
    }
}
