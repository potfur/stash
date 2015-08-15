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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var DocumentConverterInterface
     */
    private $converter;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Constructor
     *
     * @param \MongoCollection           $collection
     * @param DocumentConverterInterface $converter
     * @param EventDispatcherInterface   $eventDispatcher
     */
    public function __construct(\MongoCollection $collection, DocumentConverterInterface $converter, EventDispatcherInterface $eventDispatcher)
    {
        $this->collection = $collection;
        $this->converter = $converter;
        $this->eventDispatcher = $eventDispatcher;
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
     * @param object $document
     * @param array  $options
     *
     * @return bool
     */
    public function insert($document, array $options = [])
    {
        return $this->persist([$this->collection, 'insert'], $document, $options);
    }

    /**
     * Save document
     *
     * @param object $document
     * @param array  $options
     *
     * @return bool
     */
    public function save($document, array $options = [])
    {
        return $this->persist([$this->collection, 'save'], $document, $options);
    }

    /**
     * Persist entity using mongo collection call
     *
     * @param callable $call
     * @param object   $document
     * @param array    $options
     *
     * @return bool
     * @throws InvalidEntityException
     */
    private function persist(callable $call, $document, array $options)
    {
        $this->assertEntityInstance($document);

        $this->eventDispatcher->dispatch(Events::PERSIST_BEFORE, new Event($document));
        $raw = $this->converter->convertToDatabaseValue($document);
        $result = $call($raw, $options);

        if (!$this->isOk($result)) {
            return false;
        }

        $this->setIdentifier($document, $raw);
        $this->eventDispatcher->dispatch(Events::PERSIST_AFTER, new Event($document));

        return true;
    }

    /**
     * Assert if passed document is an object
     *
     * @param mixed $document
     *
     * @throws InvalidEntityException
     */
    private function assertEntityInstance($document)
    {
        if (!is_object($document)) {
            throw new InvalidEntityException(sprintf('Unable to persist got "%s" instead of entity instance', gettype($document)));
        }
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

        return new Cursor($cursor, $this->converter, $this->eventDispatcher);
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

        return $this->createDocument($result);
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

        return $this->createDocument($result);
    }

    /**
     * Create entity from array
     *
     * @param null|array $document
     *
     * @return object
     */
    private function createDocument($document)
    {
        if (!$document) {
            return null;
        }

        $entity = $this->converter->convertToPHPValue($document);
        $this->eventDispatcher->dispatch(Events::FIND_AFTER, new Event($entity));

        return $entity;
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
    public function distinct($key, array $query = [])
    {
        return $this->collection->distinct($key, $query);
    }

    /**
     * Return an array of documents with computed results for each group of documents.
     *
     * @param array|object|\MongoCode $keys
     * @param array                   $initial
     * @param \MongoCode              $reduce
     * @param array                   $options
     *
     * @return array
     */
    public function group($keys, array $initial, \MongoCode $reduce, array $options = [])
    {
        $result = $this->collection->group($keys, $initial, $reduce, array_merge(['condition' => [], $options]));
        if (!$this->isOk($result)) {
            return false;
        }

        return $result;
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
            $this->eventDispatcher->dispatch(Events::REMOVE_BEFORE, new Event($criteria));
            $criteria = [Fields::KEY_ID => $this->getIdentifier($criteria)];
        }

        $result = $this->collection->remove($criteria, $options);

        return $result;
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
