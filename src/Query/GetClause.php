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

class GetClause extends Clause
{
    protected $expression;
    protected $alias;

    public function __construct($exp, string $alias = "")
    {
        $this->expression = $this->toExpression($exp, false);
        $this->alias = $alias;
    }

    public function getExpression()
    {
        return $this->expression;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Write a select return clause as SQL query syntax
     * @param Parameters $params The query parameters: tables and placeholder values
     * @return string The generated SQL
     */
    public function toSQL(Parameters $params, bool $inner_clause)
    {
        $alias = $this->alias;

        $drv = $params->getDriver();
        $sql = $drv->toSQL($params, $this->expression, true);

        if (empty($this->alias))
            $this->alias = $params->generateAlias($this->expression);

        if (!empty($this->alias))
            return $sql . ' AS ' . $drv->identQuote($this->alias);

        return $sql;
    }
}
