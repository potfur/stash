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

class Foo
{
    public $_id;
    public $field;

    public function __construct($id = null, $field = null)
    {
        $this->_id = $id;
        $this->field = $field;
    }
}
