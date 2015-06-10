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

class Entity
{
    public $_id;

    public function __construct(\MongoId $id = null)
    {
        $this->_id = $id;
    }

}
