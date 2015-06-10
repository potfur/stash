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
 * Boolean converter
 *
 * @package Stash
 */
final class BooleanType implements TypeInterface
{
    /**
     * Convert a value from its PHP representation.
     *
     * @param bool $value
     *
     * @return bool
     */
    public function convertToDatabaseValue($value)
    {
        return (bool) $value;
    }

    /**
     * Convert a value from its database representation.
     *
     * @param bool $value
     *
     * @return bool
     */
    public function convertToPHPValue($value)
    {
        return (bool) $value;
    }
}
