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

        foreach ($result as $field => $value) {
            if ($model->hasField($field)) {
                $result[$field] = $this->convertFieldToDatabaseValue($value, $model->getField($field));
            }

            if ($value === null) {
                unset($result[$field]);
            }
        }

        return $result;
    }

    /**
     * Convert document field to its database representation
     *
     * @param mixed          $value
     * @param FieldInterface $field
     *
     * @return mixed
     */
    private function convertFieldToDatabaseValue($value, FieldInterface $field)
    {
        if ($field->getType() === Fields::TYPE_ARRAY) {
            return $this->convertArrayToDatabaseValue($value, $field);
        }

        if ($field->getType() === Fields::TYPE_DOCUMENT) {
            return $this->convertToDatabaseValue($value);
        }

        return $this->converter->convertToDatabaseValue($value, $field->getType());
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
            if ($field->getElementType() === Fields::TYPE_DOCUMENT) {
                $value = $this->convertToDatabaseValue($value);
            } else {
                $value = $this->converter->convertToDatabaseValue($value, $field->getElementType());
            }

            unset($value);
        }

        return $array;
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

        foreach ($document as $field => $value) {
            if ($model->hasField($field)) {
                $document[$field] = $this->convertFieldToPHPValue($value, $model->getField($field));
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
     * Convert document field to its PHP representation
     *
     * @param mixed          $value
     * @param FieldInterface $field
     *
     * @return mixed
     */
    private function convertFieldToPHPValue($value, FieldInterface $field)
    {
        if ($field->getType() === Fields::TYPE_ARRAY) {
            return $this->convertArrayToPHPValue($value, $field);
        }

        if ($field->getType() === Fields::TYPE_DOCUMENT) {
            return $this->convertToPHPValue($value);
        }

        return $this->converter->convertToPHPValue($value, $field->getType());
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
            if ($field->getElementType() === Fields::TYPE_DOCUMENT) {
                $value = $this->convertToPHPValue($value);
            } else {
                $value = $this->converter->convertToPHPValue($value, $field->getElementType());
            }

            unset($value);
        }

        return $array;
    }
}
