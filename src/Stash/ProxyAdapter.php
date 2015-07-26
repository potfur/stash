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

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\VirtualProxyInterface;

/**
 * Proxy adapter
 *
 * @package Stash
 */
final class ProxyAdapter implements ProxyAdapterInterface
{
    /**
     * @var LazyLoadingValueHolderFactory
     */
    private $factory;

    /**
     * Constructor
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration = null)
    {
        $this->factory = new LazyLoadingValueHolderFactory($configuration);
    }

    /**
     * Check if object is proxy
     *
     * @param object $object
     *
     * @return bool
     */
    public function isProxy($object)
    {
        return $object instanceof VirtualProxyInterface;
    }

    /**
     * Get wrapped instance
     *
     * @param object $object
     *
     * @return object
     */
    public function getWrappedValue($object)
    {
        if (!$this->isProxy($object)) {
            return $object;
        }

        if (!$object->isProxyInitialized()) {
            $object->initializeProxy();
        }

        return $object->getWrappedValueHolderValue();
    }

    /**
     * Create proxy object for set class
     *
     * @param string   $className
     * @param callable $initializer
     *
     * @return VirtualProxyInterface
     */
    public function createProxy($className, callable $initializer)
    {
        return $this->factory->createProxy(
            $className,
            function (& $wrappedObject, VirtualProxyInterface $proxy) use ($initializer) {
                $proxy->setProxyInitializer(null);
                $wrappedObject = $initializer();

                return true;
            }
        );
    }

}
