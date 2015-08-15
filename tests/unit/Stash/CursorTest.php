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

use Fake\Foo;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CursorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \MongoCursor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cursor;

    /**
     * @var DocumentConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dispatcher;

    public function setUp()
    {
        $this->cursor = $this->getMockBuilder('\MongoCursor')->disableOriginalConstructor()->getMock();

        $this->converter = $this->getMock('\Stash\DocumentConverterInterface');

        $this->dispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    public function testIterator()
    {
        $this->cursor->expects($this->any())->method('current')->willReturnOnConsecutiveCalls(
            ['field' => 'foo'],
            ['field' => 'bar']
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
                return new Foo(null, $data['field']);
            }
        );

        $this->dispatcher->expects($this->exactly(2))->method('dispatch')->withConsecutive(
            [Events::FIND_AFTER, $this->isInstanceOf('\Stash\Event')],
            [Events::FIND_AFTER, $this->isInstanceOf('\Stash\Event')]
        );

        $cursor = new Cursor($this->cursor, $this->converter, $this->dispatcher);

        $result = [];
        foreach ($cursor as $key => $value) {
            $result[$key] = $value;
        }

        $expected = [
            0 => new Foo(null, 'foo'),
            1 => new Foo(null, 'bar')
        ];

        $this->assertEquals($expected, $result);
    }

    public function testCount()
    {
        $this->cursor->expects($this->once())->method('count')->willReturn(1);

        $cursor = new Cursor($this->cursor, $this->converter, $this->dispatcher);
        $result = $cursor->count();

        $this->assertEquals(1, $result);
    }

    public function testLimit()
    {
        $this->cursor->expects($this->once())->method('limit')->with(10);

        $cursor = new Cursor($this->cursor, $this->converter, $this->dispatcher);
        $cursor->limit(10);
    }

    public function testSkip()
    {
        $this->cursor->expects($this->once())->method('skip')->with(10);

        $cursor = new Cursor($this->cursor, $this->converter, $this->dispatcher);
        $cursor->skip(10);
    }

    public function testSort()
    {
        $sorting = ['foo' => 1, 'bar' => -1];

        $this->cursor->expects($this->once())->method('sort')->with($sorting);

        $cursor = new Cursor($this->cursor, $this->converter, $this->dispatcher);
        $cursor->sort($sorting);
    }

    public function testExplain()
    {
        $this->cursor->expects($this->once())->method('explain')->willReturn(['explain data']);

        $cursor = new Cursor($this->cursor, $this->converter, $this->dispatcher);
        $result = $cursor->explain();

        $this->assertInternalType('array', $result);
    }

    public function testReadPreference()
    {
        $expected = [
            'type' => 'secondary',
            'tagsets' => [
                0 => [
                    'dc' => 'east',
                    'use' => 'reporting',
                ],
                1 => [
                    'dc' => 'west',
                ],
                2 => []
            ]
        ];

        $readPreference = \MongoClient::RP_SECONDARY;
        $readPreferenceArgs = [
            ['dc' => 'east', 'use' => 'reporting'],
            ['dc' => 'west'],
            [],
        ];

        $this->cursor->expects($this->once())->method('setReadPreference')->with($readPreference, $readPreferenceArgs);
        $this->cursor->expects($this->once())->method('getReadPreference')->willReturn($expected);

        $cursor = new Cursor($this->cursor, $this->converter, $this->dispatcher);
        $cursor->setReadPreference($readPreference, $readPreferenceArgs);

        $result = $cursor->getReadPreference();
        $this->assertEquals($expected, $result);
    }

    public function testInfo()
    {
        $this->cursor->expects($this->once())->method('info')->willReturn(['info data']);

        $cursor = new Cursor($this->cursor, $this->converter, $this->dispatcher);
        $result = $cursor->info();

        $this->assertInternalType('array', $result);
    }

    public function testPartial()
    {
        $this->cursor->expects($this->once())->method('partial')->willReturn(true);

        $cursor = new Cursor($this->cursor, $this->converter, $this->dispatcher);
        $cursor->partial(true);
    }

    public function testDead()
    {
        $this->cursor->expects($this->once())->method('dead')->willReturn(false);

        $cursor = new Cursor($this->cursor, $this->converter, $this->dispatcher);
        $result = $cursor->dead();

        $this->assertFalse($result);
    }

    public function testSnapshot()
    {
        $this->cursor->expects($this->once())->method('snapshot');

        $cursor = new Cursor($this->cursor, $this->converter, $this->dispatcher);
        $cursor->snapshot();
    }
}
