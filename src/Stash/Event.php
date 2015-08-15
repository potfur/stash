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

use Symfony\Component\EventDispatcher\Event as SfEvent;

/**
 * Event with entity as payload
 *
 * @package Stash
 */
final class Event extends SfEvent
{
    /**
     * @var object
     */
    private $payload;

    /**
     * Construct
     *
     * @param object $payload
     */
    public function __construct($payload)
    {
        $this->setPayload($payload);
    }

    /**
     * Return entity instance
     *
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set entity instance
     *
     * @param object $payload
     *
     * @throws InvalidEntityException
     */
    public function setPayload($payload)
    {
        if (!is_object($payload)) {
            throw new InvalidEntityException(sprintf('Expected entity instance, got %s', gettype($payload)));
        }

        $this->payload = $payload;
    }
}
