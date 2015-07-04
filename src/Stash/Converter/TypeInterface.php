<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash\Converter;

/**
 * Interface for type converters
 *
 * @package Stash
 */
interface TypeInterface
{
    /**
     * Return type name
     *
     * @return string
     */
    public function getType();

    /**
     * Convert a value from its PHP representation
     *
     * @param mixed $value The value to convert.
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value);

    /**
     * Convert a value from its database representation
     *
     * @param mixed $value The value to convert.
     *
     * @return mixed
     */
    public function convertToPHPValue($value);
}
