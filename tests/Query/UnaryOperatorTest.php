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

/**
 * @covers Wedeto\DB\Query\UnaryOperator
 */
class UnaryOperatorTest extends TestCase
{
    public function testUnaryOperator()
    {
        $expr = Builder::equals(Builder::field('foo'), Builder::variable('value'));
        $a = new MockTestUnaryOperatorOperator('foo', $expr);
        
        $this->assertEquals('foo', $a->getOperator());
        
        $lhs = $a->getLHS();
        $rhs = $a->getRHS();

        $this->assertNull($lhs);
        $this->assertEquals('foo', $a->getOperator());
        $this->assertEquals($expr, $rhs);

    }
}

class MockTestUnaryOperatorOperator extends UnaryOperator
{
    protected static $valid_operators = ['foo'];
}
