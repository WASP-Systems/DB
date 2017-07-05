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

use DomainException;

use Wedeto\Util\Functions as WF;
use Wedeto\DB\DAO;

class Insert extends Query
{
    protected $primary_key;
    protected $fields;
    protected $table;
    protected $values;
    protected $on_duplicate = null;

    protected $inserted_id = null;

    public function __construct($table, $record, array $primary_key = [])
    {
        if (!($table instanceof SourceTableClause))
            $table = new SourceTableClause($table);

        if ($record instanceof DAO)
            $record = $record->getRecord();
        else
            $record = WF::to_array($record);

        $this->table = $table;
        $this->fields = array();
        $this->values = array();

        foreach ($record as $key => $value)
        {
            $this->fields[] = new FieldName($key);
            $value = $this->toExpression($value, true);
            $this->values[] = $value;
        }

        if (!empty($primary_key))
            $this->setPrimaryKey($primary_key);
    }

    public function updateOnDuplicateKey(...$index_fields)
    {
        $updates = array();
        foreach ($this->fields as $idx => $fld)
        {
            $name = $fld->getField();
            $value = $this->values[$idx];
            if (!in_array($name, $index_fields, true))
                $updates[] = new UpdateField($fld, $value);
        }
        $this->on_duplicate = new DuplicateKey($index_fields, $updates);
        return true;
    }

    public function setPrimaryKey(array $primary_key)
    {
        $this->primary_key = $primary_key;
        return $this;
    }

    public function getPrimaryKey()
    {
        return $this->primary_key;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function setInsertId($id)
    {
        if (is_scalar($id) && count($this->primary_key) === 1)
            foreach ($this->primary_key as $field => $def)
                $id = [$field => $id];

        $inserted_id = array();
        foreach ($this->primary_key as $field => $def)
        {
            if (isset($id[$field]))
                $inserted_id[$field] = $id[$field];
        }

        $this->inserted_id = $inserted_id;
        return $this;
    }

    public function getInsertId()
    {
        return $this->inserted_id;
    }

    public function getOnDuplicate()
    {
        return $this->on_duplicate;
    }

    public function toSQL(Parameters $params, bool $inner_clause)
    {
        $drv = $params->getDriver();
        $query = array("INSERT INTO");
        $query[] = $drv->toSQL($params, $this->getTable());

        $fields = $this->getFields();
        foreach ($fields as $key => $field)
            $fields[$key] = $drv->toSQL($params, $field);

        $query[] = '(' . implode(', ', $fields) . ')';
        $query[] = 'VALUES';

        $insert_values = $this->getValues();
        $values = [];
        foreach ($insert_values as $key => $value)
            $values[$key] = $drv->toSQL($params, $value);

        $query[] = '(' . implode(', ', $values) . ')';

        $dup = $this->getOnDuplicate();
        if ($dup)
            $query[] = $drv->toSQL($params, $dup);

        return implode(' ', $query);
    }

}
