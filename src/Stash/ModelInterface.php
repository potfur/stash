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
 * Model interface
 *
 * @package Stash
 */
interface ModelInterface
{
    /**
     * Return entity class name
     *
     * @return string
     */
    public function getClass();

    /**
     * Return true if field with such name exists in model
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasField($name);

    /**
     * Return field description for requested name
     *
     * @param string $name
     *
     * @return FieldInterface
     */
    public function getField($name);

    /**
     * Return array with all field definitions
     *
     * @return FieldInterface[]
     */
    public function getFields();
}
