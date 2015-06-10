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
 * Interface implemented by all model field definitions
 *
 * @package Stash
 */
interface FieldInterface
{
    /**
     * Return field name
     *
     * @return string
     */
    public function getName();

    /**
     * Return field type
     *
     * @return string
     */
    public function getType();

    /**
     * Return element type for fields that contain sub elements
     *
     * @return string|null
     */
    public function getElementType();
}
