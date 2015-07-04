<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash\Model;

use Stash\FieldInterface;
use Stash\ModelInterface;
use Stash\NormalizeNamespace;

/**
 * Entity model representation
 *
 * @package Stash
 */
final class Model implements ModelInterface
{
    use NormalizeNamespace;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var string
     */
    private $collection;

    /**
     * @var FieldInterface[]
     */
    private $fields = [];

    /**
     * Constructor
     *
     * @param string           $entity
     * @param FieldInterface[] $fields
     * @param null|string      $collection
     */
    public function __construct($entity, array $fields = [], $collection = null)
    {
        $this->entity = $this->normalizeNamespace($entity);
        $this->collection = $collection;

        foreach ($fields as $field) {
            $this->addField($field);
        }
    }

    /**
     * Add field to model
     *
     * @param FieldInterface $field
     */
    public function addField(FieldInterface $field)
    {
        $this->fields[$field->getName()] = $field;
    }

    /**
     * Return entity class name
     *
     * @return string
     */
    public function getClass()
    {
        return $this->entity;
    }

    /**
     * Return collection name or null if model describes sub-document
     *
     * @return null|string
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Return true if field with such name exists in model
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasField($name)
    {
        return array_key_exists($name, $this->fields);
    }

    /**
     * Return field description for requested name
     *
     * @param string $name
     *
     * @return FieldInterface
     * @throws ModelException
     */
    public function getField($name)
    {
        if (!$this->hasField($name)) {
            throw new ModelException(sprintf('Unable to resolve field definition for name "%s"', $name));
        }

        return $this->fields[$name];
    }

    /**
     * Return array with all field definitions
     *
     * @return FieldInterface[]
     */
    public function getFields()
    {
        return $this->fields;
    }
}
