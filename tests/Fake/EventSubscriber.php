<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Fake;

use Stash\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EventSubscriber implements EventSubscriberInterface
{
    private $history = [];
    private static $events = [];

    public function __construct(array $events)
    {
        self::$events = $events;
    }

    public static function getSubscribedEvents()
    {
        return array_fill_keys(self::$events, ['store', 0]);
    }

    public function getHistory()
    {
        return $this->history;
    }

    public function store(Event $event = null, $eventName = null)
    {
        $this->history[] = [$eventName, $event->getPayload()];
    }
}

