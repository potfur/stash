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

        $factory = $this->getMockBuilder('\ProxyManager\Factory\LazyLoadingValueHolderFactory')->disableOriginalConstructor()->getMock();
        $adapter = new ProxyAdapter($factory);

        $this->assertTrue($adapter->isProxy($object));
    }

    public function testIsNotProxy()
    {
        $object = new \stdClass();

        $factory = $this->getMockBuilder('\ProxyManager\Factory\LazyLoadingValueHolderFactory')->disableOriginalConstructor()->getMock();
        $adapter = new ProxyAdapter($factory);

        $this->assertFalse($adapter->isProxy($object));
    }

    public function testGetWrappedValue()
    {
        $object = new \stdClass();

        $factory = $this->getMockBuilder('\ProxyManager\Factory\LazyLoadingValueHolderFactory')->disableOriginalConstructor()->getMock();
        $adapter = new ProxyAdapter($factory);

        $this->assertSame($object, $adapter->getWrappedValue($object));
    }

    public function testGetWrappedValueFromInitializedProxy()
    {
        $object = new \stdClass();

        $proxy = $this->getMock('\ProxyManager\Proxy\VirtualProxyInterface');
        $proxy->expects($this->once())->method('isProxyInitialized')->willReturn(true);
        $proxy->expects($this->never())->method('initializeProxy');
        $proxy->expects($this->once())->method('getWrappedValueHolderValue')->willReturn($object);

        $factory = $this->getMockBuilder('\ProxyManager\Factory\LazyLoadingValueHolderFactory')->disableOriginalConstructor()->getMock();
        $adapter = new ProxyAdapter($factory);

        $this->assertSame($object, $adapter->getWrappedValue($proxy));
    }

    public function testGetWrappedValueFromUninitializedProxy()
    {
        $object = new \stdClass();

        $proxy = $this->getMock('\ProxyManager\Proxy\VirtualProxyInterface');
        $proxy->expects($this->once())->method('isProxyInitialized')->willReturn(false);
        $proxy->expects($this->once())->method('initializeProxy');
        $proxy->expects($this->once())->method('getWrappedValueHolderValue')->willReturn($object);

        $factory = $this->getMockBuilder('\ProxyManager\Factory\LazyLoadingValueHolderFactory')->disableOriginalConstructor()->getMock();
        $adapter = new ProxyAdapter($factory);

        $this->assertSame($object, $adapter->getWrappedValue($proxy));
    }

    public function testCreateProxy()
    {
        $className = '\stdClass';
        $initializer = function () { };

        $proxy = $this->getMock('\ProxyManager\Proxy\VirtualProxyInterface');
        $proxy->expects($this->any())->method('initializeProxy')->willReturn(true);

        $factory = $this->getMockBuilder('\ProxyManager\Factory\LazyLoadingValueHolderFactory')->disableOriginalConstructor()->getMock();
        $factory->expects($this->once())->method('createProxy')->with($className, $this->isInstanceOf('\Closure'))->willReturn($proxy);

        $adapter = new ProxyAdapter($factory);
        $result = $adapter->createProxy($className, $initializer);

        $this->assertInstanceOf('\ProxyManager\Proxy\VirtualProxyInterface', $result);
    }
}
