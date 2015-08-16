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
     * @var ReferenceResolverInterface
     */
    private $referencer;

    /**
     * @var ModelCollection
     */
    private $models;

    /**
     * @var ProxyAdapterInterface
     */
    private $proxyAdapter;

    /**
     * @var bool
     */
    private $graceful;

    /**
     * Constructor
     *
     * @param ConverterInterface         $converter
     * @param ReferenceResolverInterface $referencer
     * @param ModelCollection            $models
     * @param ProxyAdapterInterface      $proxyAdapter
     */
    public function __construct(ConverterInterface $converter, ReferenceResolverInterface $referencer, ModelCollection $models, ProxyAdapterInterface $proxyAdapter)
    {
        $this->converter = $converter;
        $this->referencer = $referencer;
        $this->models = $models;
        $this->proxyAdapter = $proxyAdapter;
    }

    /**
     * Link converter with connection for resolving references
     *
     * @param Connection $connection
     */
    public function connect(Connection $connection)
    {
        $this->referencer->connect($connection);
    }

    /**
     * Set graceful mode
     * If true, will try to convert unknown documents to stdClass objects
     *
     * @param bool $graceful
     */
    public function setGraceful($graceful)
    {
        $this->graceful = (bool) $graceful;
    }

    /**
     * Convert from document instance into database representation
     *
     * @param object $document
     *
     * @return array
     * @throws InvalidEntityException
     */
    public function convertToDatabaseValue($document)
    {
        if (!is_object($document)) {
            throw new InvalidEntityException(sprintf('Entity must be an object, got "%s"', gettype($document)));
        }

        return $this->convertDocumentToDatabaseValue($this->proxyAdapter->getWrappedValue($document));
    }

    /**
     * Convert entity to document array
     *
     * @param object $document
     *
     * @return array
     */
    private function convertDocumentToDatabaseValue($document)
    {
        $model = $this->models->getByInstance($document);
        $result = $this->converter->convertToDatabaseValue($document, Fields::TYPE_DOCUMENT);

        $result = array_filter(
            $this->convertDocument($result, [$this, 'convertFieldToDatabaseValue'], $model),
            function ($value) {
                return $value !== null;
            }
        );

        return $result;
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
            $value = $this->converter->convertToDatabaseValue($value, Fields::TYPE_ARRAY);
            return $this->convertArray($value, [$this, __FUNCTION__], $field);
        }

        if ($type === Fields::TYPE_REFERENCE) {
            return $this->referencer->store($value);
        }

        if ($type === Fields::TYPE_DOCUMENT) {
            return $this->convertDocumentToDatabaseValue($value);
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
            if ($this->graceful) {
                return $this->convertUnknownObject($document);
            }

            throw new IncompleteDocumentException(sprintf('Incomplete entity document, missing "%s"', Fields::KEY_CLASS));
        }

        return $this->proxyAdapter->createProxy(
            $this->models->getByClass($document[Fields::KEY_CLASS])->getClass(),
            function () use ($document) {
                return $this->convertDocumentToPHPValue($document);
            }
        );
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
     * Convert document array to entity
     *
     * @param array $document
     *
     * @return object
     */
    private function convertDocumentToPHPValue(array $document)
    {
        $model = $this->models->getByClass($document[Fields::KEY_CLASS]);
        $document = $this->convertDocument($document, [$this, 'convertFieldToPHPValue'], $model);

        return $this->converter->convertToPHPValue($document, Fields::TYPE_DOCUMENT);
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
            $value = $this->converter->convertToPHPValue($value, Fields::TYPE_ARRAY);
            return $this->convertArray($value, [$this, __FUNCTION__], $field);
        }

        if ($type === Fields::TYPE_REFERENCE) {
            return $this->referencer->resolve($value);
        }

        if ($type === Fields::TYPE_DOCUMENT) {
            return $this->convertDocumentToPHPValue($value);
        }

        return $this->converter->convertToPHPValue($value, $type);
    }

    /**
     * Convert document elements using method
     *
     * @param array          $document
     * @param callable       $method
     * @param ModelInterface $model
     *
     * @return array
     */
    private function convertDocument(array $document, callable $method, ModelInterface $model)
    {
        array_walk(
            $document,
            function (&$value, $fieldName) use ($method, $model) {
                if ($model->hasField($fieldName)) {
                    $field = $model->getField($fieldName);
                    $value = $method($value, $field, $field->getType());
                }
            }
        );

        return $document;
    }

    /**
     * Convert array using method
     *
     * @param array          $array
     * @param callable       $method
     * @param FieldInterface $field
     *
     * @return array
     */
    private function convertArray(array $array, callable $method, FieldInterface $field)
    {
        array_walk(
            $array,
            function (&$value) use ($method, $field) {
                $value = $method($value, $field, $field->getElementType());
            }
        );

        return $array;
    }
}
