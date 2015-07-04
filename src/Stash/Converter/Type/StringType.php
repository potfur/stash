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
 * String converter
 * Handles everything that is/can be represented as string
 *
 * @package Stash
 */
final class StringType implements TypeInterface
{
    /**
     * Return type name
     *
     * @return string
     */
    public function getType()
    {
        return Fields::TYPE_STRING;
    }

    /**
     * Convert a value from its PHP representation.
     *
     * @param string $value
     *
     * @return string
     */
    public function convertToDatabaseValue($value)
    {
        return (string) $value;
    }

    /**
     * Convert a value from its database representation.
     *
     * @param string $value
     *
     * @return string
     */
    public function convertToPHPValue($value)
    {
        return (string) $value;
    }
}
