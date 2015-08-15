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

class EventTest extends \PHPUnit_Framework_TestCase
{
    public function testPayload()
    {
        $payloadA = new \stdClass;
        $payloadB = new \stdClass;

        $event = new Event($payloadA);
        $this->assertSame($payloadA, $event->getPayload());

        $event->setPayload($payloadB);
        $this->assertSame($payloadB, $event->getPayload());
    }

    /**
     * @expectedException \Stash\InvalidEntityException
     * @expectedExceptionMessage Expected entity instance, got array
     */
    public function testPayloadWithNonObject()
    {
        new Event([]);
    }
}
