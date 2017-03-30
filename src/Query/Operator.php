<?php
/*
This is part of Wedeto, the WEb DEvelopment TOolkit.
It is published under the MIT Open Source License.

Copyright 2017, Egbert van der Wal

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace Wedeto\DB\Query;

use InvalidArgumentException;

use Wedeto\Util\Functions as WF;

class Operator extends Expression
{
    protected $lhs = null;
    protected $rhs;
    protected $operator;

    protected static $valid_operators = array();

    public function __construct($operator, $lhs, $rhs)
    {
        if (!in_array($operator, static::$valid_operators, true))
            throw new InvalidArgumentException("Invalid operator: " . WF::str($operator));

        if ($lhs !== null)
            $this->lhs = $this->toExpression($lhs, false);
        $this->rhs = $this->toExpression($rhs, true);
        $this->operator = $operator;
    }

    public function getLHS()
    {
        return $this->lhs;
    }

    public function getRHS()
    {
        return $this->rhs;
    }

    public function getOperator()
    {
        return $this->operator;
    }
}

