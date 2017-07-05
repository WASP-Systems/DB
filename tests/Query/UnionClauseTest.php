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

use Wedeto\DB\Query\Builder as Q;
use Wedeto\DB\Exception\QueryException;

require_once "ProvideMockDb.php";
use Wedeto\DB\MockDB;

/**
 * @covers Wedeto\DB\Query\UnionClause
 */
class UnionClauseTest extends TestCase
{
    public function testUnionClause()
    {
        $s = Q::select(
            Q::from('foo'),
            Q::where(
                Q::equals('a', true)
            )
        );

        $union = new UnionClause('ALL', $s);
        $this->assertEquals('ALL', $union->getType());
        $this->assertEquals($s, $union->getQuery());

        $union = new UnionClause('', $s);
        $this->assertEquals('DISTINCT', $union->getType());
        $this->assertEquals($s, $union->getQuery());

        $union = new UnionClause('DISTINCT', $s);
        $this->assertEquals('DISTINCT', $union->getType());
        $this->assertEquals($s, $union->getQuery());
    }

    public function testUnionClauseWithInvalidType()
    {
        $s = Q::select(
            Q::from('foo'),
            Q::where(
                Q::equals('a', true)
            )
        );

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Invalid UNION type');
        $union = new UnionClause('FOO', $s);
    }

    public function testToSQL()
    {
        $db = new MockDB();
        $drv = $db->getDriver();
        $params = new Parameters($drv);

        $s = Q::select(
            Q::from('foo'),
            Q::where(
                Q::equals('a', true)
            )
        );

        $union = new UnionClause('ALL', $s);

        $sql = $union->toSQL($params, false);
        $this->assertEquals(
            "UNION ALL (SELECT * FROM \"foo\" WHERE \"a\" = :c0)",
            $sql
        );

        $this->assertTrue($params->get('c0'));
    }
}
