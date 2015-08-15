<?php

/*
* This file is part of the Stash package
*
*(c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Stash cursor decorator
 * Instead of plain arrays documents, returns entities
 *
 * @package Stash
 */
class Cursor implements \Iterator, \Countable
{
    /**
     * @var \MongoCursor
     */
    private $cursor;

    /**
     * @var DocumentConverterInterface
     */
    private $converter;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param \MongoCursor               $cursor
     * @param DocumentConverterInterface $converter
     * @param EventDispatcherInterface   $eventDispatcher
     */
    public function __construct(\MongoCursor $cursor, DocumentConverterInterface $converter, EventDispatcherInterface $eventDispatcher)
    {
        $this->cursor = $cursor;
        $this->converter = $converter;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        $document = $this->converter->convertToPHPValue($this->cursor->current());
        $this->eventDispatcher->dispatch(Events::FIND_AFTER, new Event($document));

        return $document;
    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        $this->cursor->next();
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key()
    {
        return $this->cursor->key();
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->cursor->valid();
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        $this->cursor->rewind();
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return $this->cursor->count();
    }

    /**
     * Limits the number of results returned
     *
     * @param int $num
     */
    public function limit($num)
    {
        $this->cursor->limit($num);
    }

    /**
     * Skips a number of results
     *
     * @param int $num
     */
    public function skip($num)
    {
        $this->cursor->skip($num);
    }

    /**
     * Sorts the results by given fields
     * ['fieldName' => order]
     *  -  1 for ascending
     *  - -1 for descending
     *
     * @param array $fields
     */
    public function sort(array $fields)
    {
        $this->cursor->sort($fields);
    }

    /**
     * Return an explanation of the query, often useful for optimization and debugging
     *
     * @return array
     */
    public function explain()
    {
        return $this->cursor->explain();
    }

    /**
     * Get the read preference for this query
     *
     * @return array
     */
    public function getReadPreference()
    {
        return $this->cursor->getReadPreference();
    }

    /**
     * Set the read preference for this query
     *
     * @param string $readPreference
     * @param array  $tags
     */
    public function setReadPreference($readPreference, array $tags = [])
    {
        $this->cursor->setReadPreference($readPreference, $tags);
    }

    /**
     * Gets information about the cursor's creation and iteration
     *
     * @return array
     */
    public function info()
    {
        return $this->cursor->info();
    }

    /**
     * If this query should fetch partial results from mongos if a shard is down
     *
     * @param bool $okay
     */
    public function partial($okay = true)
    {
        $this->cursor->partial($okay);
    }

    /**
     * Checks if there are results that have not yet been sent from the database
     *
     * @return bool
     */
    public function dead()
    {
        return $this->cursor->dead();
    }

    /**
     * Use snapshot mode for the query.
     * Snapshot mode ensures that a document will not be returned more than once because an intervening write operation results in a move of the document.
     * Documents inserted or deleted during the lifetime of the cursor may or may not be returned.
     */
    public function snapshot()
    {
        return $this->cursor->snapshot();
    }
}
