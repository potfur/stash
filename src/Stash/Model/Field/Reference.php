<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash\Model\Field;

use Stash\FieldInterface;
use Stash\Fields;

/**
 * Reference field
 *
 * @package Stash
 */
final class Reference implements FieldInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Return field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return field type
     *
     * @return string
     */
    public function getType()
    {
        return Fields::TYPE_REFERENCE;
    }

    /**
     * Return element type for fields that contain sub elements
     *
     * @return string|null
     */
    public function getElementType()
    {
        return null;
    }
}
