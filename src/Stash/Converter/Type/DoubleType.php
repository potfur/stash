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
 * Double converter
 *
 * @package Stash
 */
final class DoubleType implements TypeInterface
{
    /**
     * Convert a value from its PHP representation.
     *
     * @param float $value
     *
     * @return float
     */
    public function convertToDatabaseValue($value)
    {
        return (float) $value;
    }

    /**
     * Convert a value from its database representation.
     *
     * @param float $value
     *
     * @return float
     */
    public function convertToPHPValue($value)
    {
        return (float) $value;
    }
}
