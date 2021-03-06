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

use Wedeto\DB\Exception\QueryException;
use Wedeto\Util\Functions as WF;

class UnionClause extends Expression
{
    protected static $valid_types = array(
        'ALL' => 'ALL',
        '' => 'DISTINCT',
        'DISTINCT' => 'DISTINCT'
    );

    protected $select;
    protected $type;
    protected $sub_scope_number = null;

    public function __construct(string $type, Select $query)
    {
        $this->setQuery($query);
        $this->setType($type);
    }

    public function setType(string $type)
    {
        if (!isset(self::$valid_types[$type]))
            throw new QueryException('Invalid UNION type: ' . WF::str($type));
        $this->type = self::$valid_types[$type];
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getQuery()
    {
        return $this->select;
    }

    public function setQuery(Select $query)
    {
        $this->select = $query;
        return $this;
    }

    /**
     * Write a UNION clause as SQL query synta
     * @param Parameters $params The query parameters: tables and placeholder values
     * @param bool $inner_clause Unused
     * @return string The generated SQL
     */
    public function toSQL(Parameters $params, bool $inner_clause)
    {
        $q = $this->getQuery();
        $t = $this->getType();
        
        $scope = $params->getSubScope($this->sub_scope_number);
        $this->sub_scope_number = $scope->getScopeID();
        return 'UNION ' . $t . ' (' . $params->getDriver()->toSQL($scope, $q) . ')';
    }

}

