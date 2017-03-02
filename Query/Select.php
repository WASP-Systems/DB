<?php
/*
This is part of WASP, the Web Application Software Platform.
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

namespace WASP\DB\Query;

use DomainException;

class Select extends Query
{
    protected $fields = array();
    protected $table;
    protected $joins = array();
    protected $where;
    protected $order;
    protected $limit;
    protected $offset;

    public static function countQuery(Select $query)
    {
        $cq = new Select;
        $cq->fields = array(new getClause(new FunctionExpression("COUNT"), null));
        $cq->table = $query->table;
        $cq->joins = $query->joins;
        $cq->where = $query->where;
        return $cq;
    }

    public function add(Clause $clause)
    {
        if ($clause instanceof WhereClause)
            $this->where = $clause;
        elseif ($clause instanceof TableClause)
            $this->table = $clause;
        elseif ($clause instanceof GetClause)
            $this->fields[] = $clause;
        elseif ($clause instanceof JoinClause)
            $this->joins[] = $clause;
        elseif ($clause instanceof OrderClause)
            $this->order = $clause;
        elseif ($clause instanceof LimitClause)
            $this->limit = $clause;
        elseif ($clause instanceof OffsetClause)
            $this->offset = $clause;
        else
            throw new DomainException("Unknown clause: " . get_class($clause));

        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getWhere()
    {
        return $this->where;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getLimit()
    {
        return $limit;
    }

    public function getOffset()
    {
        return $offset;
    }

    public function registerTables(Parameters $parameters)
    {
        foreach ($this->fields as $f)
            $f->registerTables($parameters);
        if ($this->table)
            $this->table->registerTables($parameters);
        foreach ($this->joins as $join)
            $join->registerTables($parameters);
        if ($this->where)
            $this->where->registerTables($parameters);
        if ($this->order)
            $this->order->registerTables($parameters);
    }

    public function toSQL(Parameters $parameters)
    {
        $this->registerTables($parameters);
        $query = array();

        $query[] = "SELECT";
        if (!empty($this->fields))
        {
            $parts = array();
            foreach ($this->fields as $field)
                $parts[] = $field->toSQL($parameters);
            $query[] = implode(", ", $parts);
        }
        else
            $query[] = "*";

        if ($this->table)
            $query[] = "FROM " . $this->table->toSQL($parameters);

        foreach ($this->joins as $join)
            $query[] = $join->toSQL($parameters);

        if ($this->where)
            $query[] = $this->where->toSQL($parameters);
        
        if ($this->order)
            $query[] = $this->order->toSQL($parameters);

        if ($this->limit)
            $query[] = $this->limit->toSQL($parameters);

        if ($this->offset)
            $query[] = $this->offset->toSQL($parameters);
        
        return implode(" ", $query);
    }
}