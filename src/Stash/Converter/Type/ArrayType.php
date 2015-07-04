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
use Stash\Fields;

/**
 * Numeric and associative array converter
 * Handles BSONs array and object types
 *
 * @package Stash
 */
final class ArrayType implements TypeInterface
{
    /**
     * Return type name
     *
     * @return string
     */
    public function getType()
    {
        return Fields::TYPE_ARRAY;
    }

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
