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
 * Integer converter
 *
 * @package Stash
 */
final class IntegerType implements TypeInterface
{
    /**
     * Return type name
     *
     * @return string
     */
    public function getType()
    {
        return Fields::TYPE_INTEGER;
    }

    /**
     * Convert a value from its PHP representation.
     *
     * @param int $value
     *
     * @return int
     */
    public function convertToDatabaseValue($value)
    {
        return (int) $value;
    }

    /**
     * Convert a value from its database representation.
     *
     * @param int $value
     *
     * @return int
     */
    public function convertToPHPValue($value)
    {
        return (int) $value;
    }
}
