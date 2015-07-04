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
 * Class with field type constants
 *
 * @package Stash
 */
final class Fields
{
    const KEY_ID = '_id';
    const KEY_CLASS = '_class';

    const TYPE_ID = 'id';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_INTEGER = 'integer';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_STRING = 'string';
    const TYPE_DATE = 'date';
    const TYPE_ARRAY = 'array';
    const TYPE_DOCUMENT = 'document';
    const TYPE_REFERENCE = 'reference';
}
