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
 * Document converter
 * Handles documents and sub documents
 *
 * @package Stash
 */
final class DocumentType implements TypeInterface
{
    /**
     * Return type name
     *
     * @return string
     */
    public function getType()
    {
        return Fields::TYPE_DOCUMENT;
    }

    /**
     * Convert a value from its PHP representation.
     *
     * @param object $value
     *
     * @return array
     */
    public function convertToDatabaseValue($value)
    {
        if ($value === null) {
            return null;
        }

        $reflection = new \ReflectionClass($value);

        $result = [
            Fields::KEY_ID => null,
            Fields::KEY_CLASS => $reflection->name
        ];

        foreach ($reflection->getProperties() as $prop) {
            $prop->setAccessible(true);
            $result[$prop->getName()] = $prop->getValue($value);
        }

        return array_filter(
            $result,
            function ($value) {
                return $value !== null;
            }
        );
    }

    /**
     * Convert a value from its database representation.
     *
     * @param array $value
     *
     * @return array
     */
    public function convertToPHPValue($value)
    {
        if ($value === null) {
            return null;
        }

        $reflection = new \ReflectionClass($this->getClass($value));
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($value as $field => $val) {
            if ($field === Fields::KEY_CLASS) {
                continue;
            }

            $this->setValue($reflection, $instance, $field, $val);
        }

        return $instance;
    }

    /**
     * Return document class
     *
     * @param mixed $document
     *
     * @return string
     */
    private function getClass($document)
    {
        if (is_array($document) && isset($document[Fields::KEY_CLASS])) {
            return $document[Fields::KEY_CLASS];
        }

        return '\stdClass';
    }

    /**
     * Assign value to property of instance
     *
     * @param \ReflectionClass $reflection
     * @param object           $instance
     * @param string           $field
     * @param mixed            $val
     */
    private function setValue(\ReflectionClass $reflection, $instance, $field, $val)
    {
        if (!$reflection->hasProperty($field)) {
            $instance->{$field} = $val;

            return;
        }

        $prop = $reflection->getProperty($field);
        $prop->setAccessible(true);
        $prop->setValue($instance, $val);
    }
}
