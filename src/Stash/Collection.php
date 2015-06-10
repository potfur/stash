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
 * @method array aggregate(array $pipeline, array $options = [])
 * @method mixed batchInsert(array $a, array $options = [])
 * @method int count(array $query = [], array $options = [])
 * @method array createDBRef($document_or_id)
 * @method bool createIndex(array $keys, array $options = [])
 * @method array deleteIndex($keys)
 * @method array deleteIndexes()
 * @method array distinct($key, array $query)
 * @method array drop()
 * @method bool ensureIndex($key, array $options = [])
 * @method array getDBRef(array $ref)
 * @method array getIndexInfo()
 * @method string getName()
 * @method array getReadPreference()
 * @method bool getSlaveOkay()
 * @method array getWriteConcern()
 * @method array group($keys, array $initial, \MongoCode $reduce, array $options = [])
 * @method array parallelCollectionScan(int $num_cursors)
 * @method bool setReadPreference(string $read_preference, array $tags = [])
 * @method bool setSlaveOkay(bool $ok = true)
 * @method bool setWriteConcern(mixed $w, int $wtimeout = null)
 * @method string __toString()
 * @method bool|array update(array $criteria, array $new_object, array $options = [])
 * @method array validate(bool $scan_data = false)
 *
 * @package Stash
 */
final class Collection
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
     * Constructor
     *
     * @param \MongoCollection           $collection
     * @param DocumentConverterInterface $converter
     */
    public function __construct(\MongoCollection $collection, DocumentConverterInterface $converter)
    {
        $this->collection = $collection;
        $this->converter = $converter;
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
     */
    private function convertDocument($document)
    {
        if (is_object($document)) {
            $document = $this->converter->convertToDatabaseValue($document);
        }

        return $document;
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
        if (is_array($result) && isset($result['ok']) && $result['ok'] == 1) {
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
     *
     * @return null|object
     */
    public function findOne(array $query = [], array $fields = [])
    {
        $result = $this->collection->findOne($query, $fields);

        return $this->converter->convertToPHPValue($result);
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
     * @return \MongoId
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

    /**
     * Transfer method call to internal MongoCollection instance and returns its result
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->collection, $name], $arguments);
    }

    /**
     * Return property value from internal MongoCollection instance
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->collection->{$name};
    }

    /**
     * Set property value of internal MongoCollection instance
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->collection->{$name} = $value;
    }
}
