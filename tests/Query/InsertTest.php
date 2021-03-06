<?php
/*
This is part of Wedeto, the WEb DEvelopment TOolkit.
It is published under the MIT Open Source License.

Copyright 2017-2018, Egbert van der Wal

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

use Wedeto\DB\Exception\OutOfRangeException;
use Wedeto\DB\Exception\InvalidTypeException;
use Wedeto\DB\DB;

use Wedeto\DB\Query\Builder as QB;

/**
 * @covers Wedeto\DB\Query\Insert
 */
class InsertTest extends TestCase
{
    public function testInsert()
    {
        $table = "test_table";
        $record = array('foo' => 'bar', 'baz' => 5);

        $i = new Insert($table, $record);

        $fields = $i->getFields();
        $this->assertEquals(2, count($fields));
        $first = $fields['foo'];
        $second = $fields['baz'];
        $this->assertEquals("foo", $first->getField());
        $this->assertEquals("baz", $second->getField());

        $values = $i->getValues();
        $this->assertEquals(2, count($values));
        $first = $values['foo'];
        $second = $values['baz'];
        $this->assertInstanceOf(ConstantValue::class, $first);
        $this->assertEquals('bar', $first->getValue());
        $this->assertInstanceOf(ConstantValue::class, $second);
        $this->assertEquals(5, $second->getValue());
        
        $t = $i->getTable();
        $this->assertInstanceOf(TableClause::class, $t);
        $this->assertEquals($table, $t->getTable());
    }

    public function testInsertId()
    {
        $table = "test_table";
        $record = array('foo' => 'bar');

        $i = new Insert($table, $record, ['id' => 'id']);
        $expected = 5;
        $i->setInsertId($expected);
        $this->assertEquals(['id' => $expected], $i->getInsertId());
    }

    public function testInsertWithOnDuplicate()
    {
        $table = "test_table";
        $record = array('foo' => 'bar', 'baz' => 3);

        $i = new Insert($table, $record);
        $i->updateOnDuplicateKey("foo");

        $dk = $i->getOnDuplicate();
        $this->assertInstanceOf(DuplicateKey::class, $dk);

        $f = $dk->getConflictingFields();
        $this->assertEquals(1, count($f));
        $first = reset($f);
        $this->assertEquals('foo', $first->getField());

        $updates = $dk->getUpdates();
        $this->assertEquals(1, count($updates));
        $update = reset($updates);
        $this->assertInstanceOf(UpdateField::class, $update);
        $f = $update->getField();
        $this->assertEquals("baz", $f->getField());
        $v = $update->getValue();
        $this->assertInstanceOf(ConstantValue::class, $v);
        $this->assertEquals(3, $v->getValue());
    }

    public function testInsertUsingModel()
    {
        $mock = $this->prophesize(\Wedeto\DB\Model::class);
        $mock->getRecord()->willReturn(['foo' => 'bar', 'baz' => 3]);
        $model = $mock->reveal();

        $table = "test_table";

        $i = new Insert($table, $model);

        $fields = $i->getFields();
        $this->assertEquals(2, count($fields));
        $this->assertInstanceOf(FieldName::class, $fields['foo']);
        $this->assertEquals("foo", $fields['foo']->getField());
        $this->assertInstanceOf(FieldName::class, $fields['baz']);
        $this->assertEquals("baz", $fields['baz']->getField());

        $values = $i->getValues();
        $this->assertEquals(2, count($values));
        $this->assertInstanceOf(ConstantValue::class, $values['foo']);
        $this->assertEquals("bar", $values['foo']->getValue());
        $this->assertInstanceOf(ConstantValue::class, $values['baz']);
        $this->assertEquals(3, $values['baz']->getValue());
    }

    public function testReplaceValues()
    {
        $vals = ['foo' => 'bar', 'baz' => 3];
        $table = "test_table";

        $i = new Insert($table, $vals);
        
        $curVals = $i->getValues();
        $this->assertEquals(2, count($curVals));

        $this->assertTrue(isset($curVals['foo']));
        $this->assertInstanceOf(ConstantValue::class, $curVals['foo']);
        $this->assertEquals('bar', $curVals['foo']->getValue());

        $this->assertTrue(isset($curVals['baz']));
        $this->assertInstanceOf(ConstantValue::class, $curVals['baz']);
        $this->assertEquals(3, $curVals['baz']->getValue());


        $newVals = ['foo' => 'foobar', 'baz' => 9];
        $i->replaceValues($newVals);

        $curVals = $i->getValues();
        $this->assertEquals(2, count($curVals));

        $this->assertTrue(isset($curVals['foo']));
        $this->assertInstanceOf(ConstantValue::class, $curVals['foo']);
        $this->assertSame($i->getValue('foo'), $curVals['foo']);
        $this->assertEquals('foobar', $curVals['foo']->getValue());

        $this->assertTrue(isset($curVals['baz']));
        $this->assertInstanceOf(ConstantValue::class, $curVals['baz']);
        $this->assertSame($i->getValue('baz'), $curVals['baz']);
        $this->assertEquals(9, $curVals['baz']->getValue());
    }

    public function testReplaceValuesWithInvalidField()
    {
        $vals = ['foo' => 'bar', 'baz' => 3];
        $table = "test_table";

        $i = new Insert($table, $vals);
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("Invalid field: foobar");
        $i->replaceValues(['foobar' => 99]);
    }

    public function testReplaceValueWithExpressionValue()
    {
        $vals = [
            'foo' => new ConstantValue('bar'),
            'baz' => QB::add(3, 5)
        ];
        $table = "test_table";

        $i = new Insert($table, $vals);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage("Not a constant value: baz");
        $i->replaceValue('baz', 8);
    }

    public function testGetValueWithInvalidField()
    {
        $vals = ['foo' => 'bar', 'baz' => 3];
        $table = "test_table";

        $i = new Insert($table, $vals);
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("Invalid field: foobar");
        $i->getValue('foobar');
    }

    public function testInsertWithIDField()
    {
        $table = "test_table";
        $record = ['foo' => 'bar', 'baz' => 3];
        
        $i = new Insert($table, $record, ["id"]);

        $this->assertEquals(['id'], $i->getPrimaryKey());
    }

    public function testToSQL()
    {
        $mocker = $this->prophesize(DB::class);
        $db = $mocker->reveal();
        $mocker->getDriver()->willReturn(new \Wedeto\DB\Driver\PGSQL($db));
        $drv = $db->getDriver();

        $params = new Parameters($drv);
        $ins = new Insert(
            QB::into("test_table"),
            ['foo' => 'bar']
        );

        $sql = $ins->toSQL($params, false);

        $this->assertEquals(
            'INSERT INTO "test_table" ("foo") VALUES (:c0)',
            $sql
        );

        $params = new Parameters($drv);
        $ins = new Insert(
            QB::into("test_table"),
            ['foo' => 'bar', 'uniqueval' => 3]
        );

        $ins->updateOnDuplicateKey('uniqueval');

        $sql = $ins->toSQL($params, false);

        $this->assertEquals(
            'INSERT INTO "test_table" ("foo", "uniqueval") VALUES (:c0, :c1) ON CONFLICT ("uniqueval") DO UPDATE SET "foo" = :c0',
            $sql
        );
    }
}
