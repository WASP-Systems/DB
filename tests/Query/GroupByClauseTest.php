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

use PHPUnit\Framework\TestCase;

use Wedeto\DB\Exception\QueryException;
use Wedeto\DB\Exception\InvalidTypeException;

/**
 * @covers Wedeto\DB\Query\GroupByClause
 */
class GroupByClauseTest extends TestCase
{
    public function testGroupBy()
    {
        $fld = "foo";
        $h = new HavingClause(new ComparisonOperator(">=", new SQLFunction("COUNT", "bar"), "5"));

        $gb = new GroupByClause($fld, $h);

        $gb_h = $gb->getHaving();
        $this->assertEquals($h, $gb_h);

        $groups = $gb->getGroups();
        $this->assertEquals(1, count($groups));
        $first = reset($groups);

        $this->assertInstanceOf(FieldName::class, $first);
    }

    public function testEmptyConstructorThrowsException()
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage("Specify at least one group by condition");
        $gb = new GroupByClause();
    }

    public function testInvalidConstructorArgumentThrowsException()
    {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage("Invalid parameter");
        $gb = new GroupByClause(new \StdClass);
    }
}
