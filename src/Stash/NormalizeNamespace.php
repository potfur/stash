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
 * Normalizes fully qualified class namespace
 *
 * @package Stash
 */
trait NormalizeNamespace
{
    /**
     * Normalize namespace
     *
     * @param string $namespace
     *
     * @return string
     */
    protected function normalizeNamespace($namespace)
    {
        return ltrim($namespace, '\\');
    }
}
