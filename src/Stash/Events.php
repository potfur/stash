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
 * Class with event names
 *
 * @package Stash
 */
final class Events
{
    const FIND_AFTER = 'load.after';

    const PERSIST_BEFORE = 'persist.before';
    const PERSIST_AFTER = 'persist.after';

    const REMOVE_BEFORE = 'remove.before';
}
