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
 * Numeric and associative array converter
 * Handles BSONs array and object types
 *
 * @package Stash
 */
final class ArrayType implements TypeInterface
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
        return (array) $value;
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
        return (array) $value;
    }
}
