<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace unit\Stash;


use Stash\ProxyAdapter;

class ProxyAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testIsProxy()
    {
        $object = $this->getMock('\ProxyManager\Proxy\VirtualProxyInterface');

        $adapter = new ProxyAdapter();
        $this->assertTrue($adapter->isProxy($object));
    }

    public function testIsNotProxy()
    {
        $object = new \stdClass();

        $adapter = new ProxyAdapter();
        $this->assertFalse($adapter->isProxy($object));
    }

    public function testGetWrappedValue()
    {
        $object = new \stdClass();

        $adapter = new ProxyAdapter();
        $this->assertSame($object, $adapter->getWrappedValue($object));
    }

    public function testGetWrappedValueFromInitializedProxy()
    {
        $object = new \stdClass();

        $proxy = $this->getMock('\ProxyManager\Proxy\VirtualProxyInterface');
        $proxy->expects($this->once())->method('isProxyInitialized')->willReturn(true);
        $proxy->expects($this->never())->method('initializeProxy');
        $proxy->expects($this->once())->method('getWrappedValueHolderValue')->willReturn($object);

        $adapter = new ProxyAdapter();
        $this->assertSame($object, $adapter->getWrappedValue($proxy));
    }

    public function testGetWrappedValueFromUninitializedProxy()
    {
        $object = new \stdClass();

        $proxy = $this->getMock('\ProxyManager\Proxy\VirtualProxyInterface');
        $proxy->expects($this->once())->method('isProxyInitialized')->willReturn(false);
        $proxy->expects($this->once())->method('initializeProxy');
        $proxy->expects($this->once())->method('getWrappedValueHolderValue')->willReturn($object);

        $adapter = new ProxyAdapter();
        $this->assertSame($object, $adapter->getWrappedValue($proxy));
    }

    public function testCreateProxy()
    {
        $object = new \stdClass();
        $initializer = function () use ($object) {
            return $object;
        };

        $adapter = new ProxyAdapter();
        $proxy = $adapter->createProxy('\stdClass', $initializer);

        $this->assertInstanceOf('\stdClass', $proxy);
        $this->assertInstanceOf('\ProxyManager\Proxy\VirtualProxyInterface', $proxy);

        $proxy->initializeProxy();
        $this->assertSame($object, $proxy->getWrappedValueHolderValue());
    }
}
