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
 * Mongo reference resolver
 *
 * @package Stash
 */
interface ReferenceResolverInterface
{
    /**
     * Link referencer with connection for resolving references
     *
     * @param Connection $connection
     */
    public function connect(Connection $connection);

    /**
     * Store reference in database format
     *
     * @param object $entity
     *
     * @return array
     */
    public function store($entity);

    /**
     * Resolve reference from database format
     *
     * @param array $reference
     *
     * @return null|object
     */
    public function resolve($reference);
}
