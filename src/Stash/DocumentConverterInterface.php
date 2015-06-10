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
 * Document converter interface
 *
 * @package Stash
 */
interface DocumentConverterInterface
{
    /**
     * Convert from document instance into database representation
     *
     * @param object $document
     *
     * @return array
     */
    public function convertToDatabaseValue($document);

    /**
     * Convert database representation into document instance
     *
     * @param array $document
     *
     * @return object
     */
    public function convertToPHPValue(array $document);
}
