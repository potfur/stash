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
     * @param \MongoCursor               $cursor
     * @param DocumentConverterInterface $converter
     */
    public function __construct(\MongoCursor $cursor, DocumentConverterInterface $converter)
    {
        $this->cursor = $cursor;
        $this->converter = $converter;
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        return $this->converter->convertToPHPValue($this->cursor->current());
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
}
