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
 * Class Field
 *
 * @package Stash
 */
final class ArrayOf implements FieldInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $name
     * @param string $type
     */
    public function __construct($name, $type)
    {
        $this->name = (string) $name;
        $this->type = (string) $type;
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
        return Fields::TYPE_ARRAY;
    }

    /**
     * Return element type for fields that contain sub elements
     *
     * @return string|null
     */
    public function getElementType()
    {
        return $this->type;
    }
}
