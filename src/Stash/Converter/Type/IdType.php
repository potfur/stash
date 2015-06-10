<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash\Converter\Type;

use Stash\Converter\TypeInterface;

/**
 * Identifier type
 * Handles Mongo id objects
 *
 * @package Stash
 */
final class IdType implements TypeInterface
{
    /**
     * Convert a value from its PHP representation.
     *
     * @param array $value
     *
     * @return array
     */
    public function convertToDatabaseValue($value)
    {
        return $this->convert($value);
    }

    /**
     * Convert a value from its database representation.
     *
     * @param array $value
     *
     * @return array
     */
    public function convertToPHPValue($value)
    {
        return $this->convert($value);
    }

    /**
     * Convert value to \MongoId
     *
     * @param mixed $value
     *
     * @return \MongoId
     */
    private function convert($value)
    {
        if ($value === null || $value instanceof \MongoId) {
            return $value;
        }

        return new \MongoId($value);
    }
}
