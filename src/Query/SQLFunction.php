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

use Wedeto\Util\Functions as WF;

class SQLFunction extends Expression
{
    protected $func;
    protected $arguments = array();

    public function __construct(string $func, ...$args)
    {
        $this->func = $func;
        $args = WF::flatten_array($args);
        foreach ($args as $arg)
            $this->addArgument($arg);
    }

    public function addArgument($argument)
    {
        $this->arguments[] = $this->toExpression($argument, false);
        return $this;
    }

    public function getFunction()
    {
        return $this->func;
    }

    public function getArguments()
    {
        return $this->arguments; 
    }

    /**
     * Write a function as SQL query syntax
     * @param Parameters $params The query parameters: tables and placeholder values
     * @param SQLFunction $expression The function to write
     * @return string The generated SQL
     */
    public function toSQL(Parameters $params, bool $inner_clause)
    {
        $func = $this->getFunction();
        $arguments = $this->getArguments();
        
        $args = array();
        foreach ($arguments as $arg)
            $args[] = $params->getDriver()->toSQL($params, $arg, false);

        return $func . '(' . implode(', ', $args) . ')';
    }
}
