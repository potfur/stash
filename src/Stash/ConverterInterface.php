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
 * Interface for type converters
 *
 * @package Stash
 */
interface ConverterInterface
{
    /**
     * Convert a value from its PHP representation
     *
     * @param mixed  $value The value to convert.
     * @param string $type
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, $type);

    /**
     * Convert a value from its database representation
     *
     * @param mixed  $value The value to convert.
     * @param string $type
     *
     * @return mixed
     */
    public function convertToPHPValue($value, $type);
}
