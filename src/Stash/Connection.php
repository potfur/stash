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
 * Class representing connection to database
 *
 * @package Stash
 */
class Connection
{
    /**
     * @var \MongoClient
     */
    private $client;

    /**
     * @var \MongoDB
     */
    private $database;

    /**
     * @var DocumentConverterInterface
     */
    private $converter;

    /**
     * @var Collection[]
     */
    private $buffer;

    /**
     * Constructor
     *
     * @param \MongoClient               $client
     * @param DocumentConverterInterface $converter
     */
    public function __construct(\MongoClient $client, DocumentConverterInterface $converter)
    {
        $this->client = $client;
        $this->converter = $converter;

        $this->converter->connect($this);
    }

    /**
     * Select/connect to database
     *
     * @param string $database
     */
    public function selectDB($database)
    {
        $this->database = $this->client->selectDB($database);
    }

    /**
     * Returns collection instance
     *
     * @param string $collection
     *
     * @return Collection
     */
    public function getCollection($collection)
    {
        if (isset($this->buffer[$collection])) {
            return $this->buffer[$collection];
        }

        return $this->buffer[$collection] = new Collection(
            $this->database->selectCollection($collection),
            $this->converter
        );
    }
}
