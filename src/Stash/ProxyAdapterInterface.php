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

/**
 * Interface for proxy adapters
 *
 * @package Stash
 */
interface ProxyAdapterInterface
{
    /**
     * Check if object is proxy
     *
     * @param object $object
     *
     * @return bool
     */
    public function isProxy($object);

    /**
     * Get wrapped instance
     *
     * @param object $object
     *
     * @return object
     */
    public function getWrappedValue($object);

    /**
     * Create proxy object for set class
     *
     * @param string   $className
     * @param callable $initializer
     *
     * @return mixed
     */
    public function createProxy($className, callable $initializer);
}
