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
use Stash\Model\ModelException;

/**
 * Scalar field
 *
 * @package Stash
 */
final class Scalar implements FieldInterface
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

        $this->assertType($type);
        $this->type = (string) $type;
    }

    /**
     * Assert if type is scalar
     *
     * @param string $type
     *
     * @throws ModelException
     */
    private function assertType($type)
    {
        $types = [Fields::TYPE_BOOLEAN, Fields::TYPE_INTEGER, Fields::TYPE_DOUBLE, Fields::TYPE_STRING];
        if (!in_array($type, $types)) {
            throw new ModelException(sprintf('Invalid type for scalar field, got "%s"', $type));
        }
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
        return $this->type;
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
