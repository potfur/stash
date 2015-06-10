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
 * @method \MongoCursor addOption($key, $value)
 * @method \MongoCursor awaitData($wait = true)
 * @method \MongoCursor batchSize($batchSize)
 * @method int count($foundOnly = false)
 * @method bool dead()
 * @method array explain()
 * @method \MongoCursor fields(array $f)
 * @method array getNext()
 * @method array getReadPreference()
 * @method bool hasNext()
 * @method \MongoCursor hint($index)
 * @method \MongoCursor immortal($liveForever = true)
 * @method array info()
 * @method \MongoCursor limit($num)
 * @method \MongoCursor maxTimeMS($ms)
 * @method \MongoCursor partial($okay = true)
 * @method void reset()
 * @method \MongoCursor setFlag($flag, bool $set = true)
 * @method \MongoCursor setReadPreference($read_preference, array $tags = [])
 * @method \MongoCursor skip($num)
 * @method \MongoCursor slaveOkay($okay = true)
 * @method \MongoCursor snapshot()
 * @method \MongoCursor sort($fields)
 * @method \MongoCursor tailable($tail = true)
 * @method \MongoCursor timeout($ms)
 *
 * @package Stash
 */
class Cursor implements \Iterator
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
     * Transfer method call to internal MongoCursor instance and returns its result
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->cursor, $name], $arguments);
    }
}
