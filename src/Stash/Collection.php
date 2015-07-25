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
 * Mongo collection decorator
 * Works on entities instead of plain array documents
 *
 * @package Stash
 */
class Collection
{
    /**
     * @var \MongoCollection
     */
    private $collection;

    /**
     * @var ModelCollection
     */
    private $models;

    /**
     * @var DocumentConverterInterface
     */
    private $converter;

    /**
     * Constructor
     *
     * @param \MongoCollection           $collection
     * @param ModelCollection            $models
     * @param DocumentConverterInterface $converter
     */
    public function __construct(\MongoCollection $collection, ModelCollection $models, DocumentConverterInterface $converter)
    {
        $this->collection = $collection;
        $this->models = $models;
        $this->converter = $converter;
    }

    /**
     * Returns this collection's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->collection->getName();
    }

    /**
     * Insert document
     *
     * @param array|object $document
     * @param array        $options
     *
     * @return bool
     */
    public function insert($document, array $options = [])
    {
        $raw = $this->convertDocument($document);
        $result = $this->collection->insert($raw, $options);

        return $this->updateDocument($document, $raw, $result);
    }

    /**
     * Save (upsert) document
     *
     * @param array|object $document
     * @param array        $options
     *
     * @return bool
     */
    public function save($document, array $options = [])
    {
        $raw = $this->convertDocument($document);
        $result = $this->collection->save($raw, $options);

        return $this->updateDocument($document, $raw, $result);
    }

    /**
     * Convert single document or array of documents to database values
     *
     * @param array|object $document
     *
     * @return array
     * @throws InvalidEntityException
     */
    private function convertDocument($document)
    {
        if (!is_object($document)) {
            return $document;
        }

        $model = $this->models->getByInstance($document);
        if ($model->getCollection() !== $this->getName()) {
            throw new InvalidEntityException(sprintf('Entity of "%s" can not be saved in "%s"', $model->getClass(), $this->getName()));
        }

        $document = $this->converter->convertToDatabaseValue($document);

        return $document;
    }

    /**
     * Returns true if operation was executed successfully
     *
     * @param array $result
     *
     * @return bool
     */
    private function isOk($result)
    {
        return is_array($result) && isset($result['ok']) && $result['ok'] == 1;
    }

    /**
     * Update document with identifier if upsert was successful
     *
     * @param array|object $document
     * @param array        $raw
     * @param mixed        $result
     *
     * @return bool
     */
    private function updateDocument($document, array $raw, $result)
    {
        if ($this->isOk($result)) {
            $this->setIdentifier($document, $raw);

            return true;
        }

        return false;
    }

    /**
     * Query collection and return a cursor for result set
     *
     * @param array $query
     * @param array $fields
     *
     * @return Cursor
     */
    public function find(array $query = [], array $fields = [])
    {
        $cursor = $this->collection->find($query, $fields);

        return new Cursor($cursor, $this->converter);
    }

    /**
     * Query collection and return first matching result
     *
     * @param array $query
     * @param array $fields
     * @param array $options
     *
     * @return null|object
     */
    public function findOne(array $query = [], array $fields = [], array $options = [])
    {
        $result = $this->collection->findOne($query, $fields, $options);

        return $this->converter->convertToPHPValue($result);
    }

    /**
     * Query collection for entity with set id
     *
     * @param mixed $id
     * @param array $options
     *
     * @return null|object
     */
    public function findById($id, array $options = [])
    {
        return $this->findOne([Fields::KEY_ID => $id], [], $options);
    }

    /**
     * Update a document and return it
     *
     * @param array $query
     * @param array $update
     * @param array $fields
     * @param array $options
     *
     * @return null|object
     */
    public function findAndModify(array $query, array $update = [], array $fields = [], array $options = [])
    {
        $result = $this->collection->findAndModify($query, $update, $fields, $options);

        return $this->converter->convertToPHPValue($result);
    }

    /**
     * Perform an aggregation using the aggregation pipeline
     *
     * @param array       $pipeline
     * @param array       $options
     * @param null|string $className
     *
     * @return array|bool
     */
    public function aggregate(array $pipeline, array $options = [], $className = null)
    {
        $result = $this->collection->aggregate($pipeline, $options);
        if (!$this->isOk($result)) {
            return false;
        }

        if ($className === null) {
            return $result;
        }

        foreach ($result['result'] as &$document) {
            $document[Fields::KEY_CLASS] = $className;
            $document = $this->converter->convertToPHPValue($document);
            unset($document);
        }

        return $result['result'];
    }

    /**
     * Counts the number of documents in this collection
     *
     * @param array $query
     * @param array $options
     *
     * @return int
     */
    public function count(array $query, array $options = [])
    {
        return (int) $this->collection->count($query, $options);
    }

    /**
     * Retrieve a list of distinct values for the given key across a collection
     *
     * @param string $key
     * @param array  $query
     *
     * @return array|bool
     */
    public function distinct($key, array $query)
    {
        return $this->collection->distinct($key, $query);
    }

    /**
     * Return an array of documents with computed results for each group of documents.
     *
     * @param string|array|\MongoCode $keys
     * @param array                   $initial
     * @param \MongoCode              $reduce
     * @param array                   $options
     *
     * @return array
     */
    public function group($keys, array $initial, \MongoCode $reduce, array $options = [])
    {
        return $this->collection->group($keys, $initial, $reduce, $options);
    }

    /**
     * Remove documents matching criteria
     *
     * @param array|object $criteria
     * @param array        $options
     *
     * @return array
     * @throws InvalidEntityException
     */
    public function remove($criteria = [], array $options = [])
    {
        if (is_object($criteria)) {
            $criteria = [Fields::KEY_ID => $this->getIdentifier($criteria)];
        }

        return $this->collection->remove($criteria, $options);
    }

    /**
     * Set identifier to entity
     *
     * @param array|object $entityDocument
     * @param array        $arrayDocument
     */
    private function setIdentifier($entityDocument, array $arrayDocument)
    {
        if (is_array($entityDocument) || !isset($arrayDocument[Fields::KEY_ID])) {
            return;
        }

        $property = $this->getProperty($entityDocument, Fields::KEY_ID);
        if (!$property) {
            $entityDocument->{Fields::KEY_ID} = $arrayDocument[Fields::KEY_ID];

            return;
        }

        $property->setValue($entityDocument, $arrayDocument[Fields::KEY_ID]);
    }

    /**
     * Return entity identifier
     *
     * @param object $entity
     *
     * @return mixed
     * @throws InvalidEntityException
     */
    private function getIdentifier($entity)
    {
        $property = $this->getProperty($entity, Fields::KEY_ID);
        if (!$property) {
            throw new InvalidEntityException(sprintf('Entity does not have "%s" field', Fields::KEY_ID));
        }

        $id = $property->getValue($entity);
        if (empty($id)) {
            throw new InvalidEntityException('Entity identifier is empty');
        }

        return $id;
    }

    /**
     * Return property reflection
     *
     * @param string|object $class
     * @param string        $name
     *
     * @return null|\ReflectionProperty
     */
    private function getProperty($class, $name)
    {
        $reflection = new \ReflectionClass($class);
        if (!$reflection->hasProperty($name)) {
            return null;
        }

        $prop = $reflection->getProperty($name);
        $prop->setAccessible(true);

        return $prop;
    }
}
