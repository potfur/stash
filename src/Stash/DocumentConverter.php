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
 * Converter for documents (entities)
 *
 * @package Stash
 */
final class DocumentConverter implements DocumentConverterInterface
{
    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var ModelCollection
     */
    private $models;

    /**
     * Constructor
     *
     * @param ConverterInterface $converter
     * @param ModelCollection    $models
     */
    public function __construct(ConverterInterface $converter, ModelCollection $models)
    {
        $this->converter = $converter;
        $this->models = $models;
    }

    /**
     * Convert from document instance into database representation
     *
     * @param object $document
     *
     * @return array
     */
    public function convertToDatabaseValue($document)
    {
        $model = $this->models->getByInstance($document);
        $result = $this->converter->convertToDatabaseValue($document, Fields::TYPE_DOCUMENT);

        foreach ($result as $fieldName => $value) {
            if ($model->hasField($fieldName)) {
                $field = $model->getField($fieldName);
                $result[$fieldName] = $this->convertFieldToDatabaseValue($value, $field, $field->getType());
            }

            if ($value === null) {
                unset($result[$fieldName]);
            }
        }

        return $result;
    }

    /**
     * Convert array elements to their database representation
     *
     * @param array          $array
     * @param FieldInterface $field
     *
     * @return array
     */
    private function convertArrayToDatabaseValue(array $array, FieldInterface $field)
    {
        $array = $this->converter->convertToDatabaseValue($array, Fields::TYPE_ARRAY);

        foreach ($array as &$value) {
            $value = $this->convertFieldToDatabaseValue($value, $field, $field->getElementType());
            unset($value);
        }

        return $array;
    }

    /**
     * Convert field to its database representation
     *
     * @param mixed          $value
     * @param FieldInterface $field
     * @param string         $type
     *
     * @return mixed
     */
    private function convertFieldToDatabaseValue($value, FieldInterface $field, $type)
    {
        if ($type === Fields::TYPE_ARRAY) {
            return $this->convertArrayToDatabaseValue($value, $field);
        }

        if ($type === Fields::TYPE_DOCUMENT) {
            return $this->convertToDatabaseValue($value);
        }

        return $this->converter->convertToDatabaseValue($value, $type);
    }

    /**
     * Convert database representation into document instance
     *
     * @param array $document
     *
     * @return object
     * @throws IncompleteDocumentException
     */
    public function convertToPHPValue(array $document)
    {
        if (!isset($document[Fields::KEY_CLASS])) {
            return $this->convertUnknownObject($document);
        }

        $model = $this->models->getByClass($document[Fields::KEY_CLASS]);

        foreach ($document as $fieldName => $value) {
            if ($model->hasField($fieldName)) {
                $field = $model->getField($fieldName);
                $document[$fieldName] = $this->convertFieldToPHPValue($value, $field, $field->getType());
            }
        }

        return $this->converter->convertToPHPValue($document, Fields::TYPE_DOCUMENT);
    }

    /**
     * Convert document to entity without class information
     *
     * @param array $document
     *
     * @return object
     */
    private function convertUnknownObject(array $document)
    {
        array_walk_recursive(
            $document,
            function (&$value) {
                if ($value instanceof \MongoDate) {
                    $value = $this->converter->convertToPHPValue($value, Fields::TYPE_DATE);
                }
            }
        );

        return $this->converter->convertToPHPValue($document, Fields::TYPE_DOCUMENT);
    }

    /**
     * Convert array of scalars to its PHP representation
     *
     * @param array          $array
     * @param FieldInterface $field
     *
     * @return array
     */
    private function convertArrayToPHPValue(array $array, FieldInterface $field)
    {
        $array = $this->converter->convertToPHPValue($array, Fields::TYPE_ARRAY);

        foreach ($array as &$value) {
            $value = $this->convertFieldToPHPValue($value, $field, $field->getElementType());
            unset($value);
        }

        return $array;
    }

    /**
     * Convert field to its PHP representation
     *
     * @param mixed          $value
     * @param FieldInterface $field
     * @param string         $type
     *
     * @return mixed
     */
    private function convertFieldToPHPValue($value, FieldInterface $field, $type)
    {
        if ($type === Fields::TYPE_ARRAY) {
            return $this->convertArrayToPHPValue($value, $field);
        }

        if ($type === Fields::TYPE_DOCUMENT) {
            return $this->convertToPHPValue($value);
        }

        return $this->converter->convertToPHPValue($value, $type);
    }
}
