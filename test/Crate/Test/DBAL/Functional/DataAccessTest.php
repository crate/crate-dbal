<?php
/**
 * Licensed to CRATE Technology GmbH("Crate") under one or more contributor
 * license agreements.  See the NOTICE file distributed with this work for
 * additional information regarding copyright ownership.  Crate licenses
 * this file to you under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.  You may
 * obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.  See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * However, if you have executed another commercial license agreement
 * with Crate these terms will supersede the license and you may use the
 * software solely pursuant to the terms of the relevant commercial agreement.
 */

namespace Crate\Test\DBAL\Functional;

use Crate\DBAL\Types\MapType;
use Crate\Test\DBAL\DBALFunctionalTest;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use PDO;


class DataAccessTest extends DBALFunctionalTest
{
    static private $generated = false;

    public function setUp() : void
    {
        parent::setUp();

        if (self::$generated === false) {
            self::$generated = true;
            /* @var $sm \Doctrine\DBAL\Schema\AbstractSchemaManager */
            $sm = $this->_conn->createSchemaManager();
            $table = new Table("fetch_table");
            $table->addColumn('test_int', 'integer');
            $table->addColumn('test_string', 'string');
            $table->addColumn('test_datetime', 'timestamp', array('notnull' => false));
            $table->addColumn('test_array', 'array', array('columnDefinition'=>'ARRAY(STRING)'));
            $platformOptions = array(
                'type'   => MapType::STRICT,
                'fields' => array(
                    // Those intentionally use DBAL types.
                    new Column('id',    Type::getType('integer'), array()),
                    new Column('name',  Type::getType('string'), array()),
                    new Column('value', Type::getType('float'), array()),
                ),
            );
            $table->addColumn('test_object', MapType::NAME,
                array('platformOptions'=>$platformOptions));
            $table->setPrimaryKey(array('test_int'));

            $sm->createTable($table);

            $this->_conn->insert('fetch_table', array(
                'test_int' => 1,
                'test_string' => 'foo',
                'test_datetime' => new \DateTime('2010-01-01 10:10:10'),
                'test_array' => array('foo','bar'),
                'test_object' => array('id'=>1, 'name'=>'foo', 'value'=>1.234,),
            ), array('integer','string','timestamp','array','map'));
            $this->refresh('fetch_table');
        }
    }

    public function tearDown() : void
    {
        if (self::$generated === true) {
            $this->_conn->createSchemaManager()->dropTable('fetch_table');
            self::$generated = false;
        }
    }

    public function testPrepareWithBindValue()
    {
        $sql = "SELECT test_int, test_string FROM fetch_table WHERE test_int = ? AND test_string = ?";
        $stmt = $this->_conn->prepare($sql);
        $this->assertInstanceOf('Doctrine\DBAL\Statement', $stmt);

        $stmt->bindValue(1, 1, PDO::PARAM_INT);
        $stmt->bindValue(2, 'foo', PDO::PARAM_STR);
        $result = $stmt->executeQuery();

        $row = $result->fetchAssociative();
        $row = array_change_key_case($row, \CASE_LOWER);
        $this->assertEquals(array('test_int' => 1, 'test_string' => 'foo'), $row);
    }

    public function testPrepareWithBindParam()
    {
        $paramInt = 1;
        $paramStr = 'foo';

        $sql = "SELECT test_int, test_string FROM fetch_table WHERE test_int = ? AND test_string = ?";
        $stmt = $this->_conn->prepare($sql);
        $this->assertInstanceOf('Doctrine\DBAL\Statement', $stmt);

        $stmt->bindValue(1, $paramInt, PDO::PARAM_INT);
        $stmt->bindValue(2, $paramStr, PDO::PARAM_STR);
        $result = $stmt->executeQuery();

        $row = $result->fetchAssociative();
        $row = array_change_key_case($row, \CASE_LOWER);
        $this->assertEquals(array('test_int' => 1, 'test_string' => 'foo'), $row);
    }

    public function testPrepareWithFetchAll()
    {
        $paramInt = 1;
        $paramStr = 'foo';

        $sql = "SELECT test_int, test_string, test_datetime, test_array, test_object FROM fetch_table WHERE test_int = ? AND test_string = ?";
        $stmt = $this->_conn->prepare($sql);
        $this->assertInstanceOf('Doctrine\DBAL\Statement', $stmt);

        $stmt->bindValue(1, $paramInt, PDO::PARAM_INT);
        $stmt->bindValue(2, $paramStr, PDO::PARAM_STR);
        $result = $stmt->executeQuery();

        $rows = $result->fetchAllAssociative();
        $rows[0] = array_change_key_case($rows[0], \CASE_LOWER);
        $this->assertEquals(array(
            'test_int' => 1,
            'test_string' => 'foo',
            'test_datetime' => 1262340610000,
            'test_array' => array('foo', 'bar'),
            'test_object' => array('id'=>1, 'name'=>'foo', 'value'=>1.234)
        ), $rows[0]);

        $this->assertEquals($this->_conn->convertToPHPValue($rows[0]['test_datetime'], 'timestamp'),
            new \DateTime('2010-01-01 10:10:10'));
        $this->assertEquals($this->_conn->convertToPHPValue($rows[0]['test_object'], 'map'),
            array('id'=>1, 'name'=>'foo', 'value'=>1.234));
        $this->assertEquals($this->_conn->convertToPHPValue($rows[0]['test_array'], 'array'),
            array('foo','bar'));
    }

    public function testPrepareWithFetchColumn()
    {
        $paramInt = 1;
        $paramStr = 'foo';

        $sql = "SELECT test_int FROM fetch_table WHERE test_int = ? AND test_string = ?";
        $stmt = $this->_conn->prepare($sql);
        $this->assertInstanceOf('Doctrine\DBAL\Statement', $stmt);

        $stmt->bindValue(1, $paramInt, PDO::PARAM_INT);
        $stmt->bindValue(2, $paramStr, PDO::PARAM_STR);
        $stmt->executeStatement();
        $column = $stmt->getWrappedStatement()->fetchColumn();

        $this->assertEquals(1, $column);
    }

    public function testPrepareWithIterator()
    {
        $paramInt = 1;
        $paramStr = 'foo';

        $sql = "SELECT test_int, test_string FROM fetch_table WHERE test_int = ? AND test_string = ?";
        $stmt = $this->_conn->prepare($sql);
        $this->assertInstanceOf('Doctrine\DBAL\Statement', $stmt);

        $stmt->bindValue(1, $paramInt, PDO::PARAM_INT);
        $stmt->bindValue(2, $paramStr, PDO::PARAM_STR);
        $result = $stmt->executeQuery();

        $rows = array();
        foreach ($result->iterateAssociative() as $row) {
            $rows[] = array_change_key_case($row, \CASE_LOWER);
        }

        $this->assertEquals(array('test_int' => 1, 'test_string' => 'foo'), $rows[0]);
    }

    public function testPrepareWithQuoted()
    {
        $table = 'fetch_table';
        $paramInt = 1;
        $paramStr = 'foo';

        $sql = "SELECT test_int, test_string FROM " . $this->_conn->quoteIdentifier($table) . " ".
               "WHERE test_int = " . $this->_conn->quote($paramInt, ParameterType::INTEGER) . " AND test_string = '" . $paramStr . "')";
        $stmt = $this->_conn->prepare($sql);
        $this->assertInstanceOf('Doctrine\DBAL\Statement', $stmt);
    }

    public function testPrepareWithExecuteParams()
    {
        $paramInt = 1;
        $paramStr = 'foo';

        $sql = "SELECT test_int, test_string FROM fetch_table WHERE test_int = ? AND test_string = ?";
        $stmt = $this->_conn->prepare($sql);
        $this->assertInstanceOf('Doctrine\DBAL\Statement', $stmt);
        $result = $stmt->executeQuery(array($paramInt, $paramStr));

        $row = $result->fetchAssociative();
        $this->assertTrue($row !== false);
        $row = array_change_key_case($row, \CASE_LOWER);
        $this->assertEquals(array('test_int' => 1, 'test_string' => 'foo'), $row);
    }

    public function testFetchAll()
    {
        $sql = "SELECT test_int, test_string FROM fetch_table WHERE test_int = ? AND test_string = ?";
        $data = $this->_conn->fetchAllAssociative($sql, array(1, 'foo'));

        $this->assertEquals(1, count($data));

        $row = $data[0];
        $this->assertEquals(2, count($row));

        $row = array_change_key_case($row, \CASE_LOWER);
        $this->assertEquals(1, $row['test_int']);
        $this->assertEquals('foo', $row['test_string']);
    }

    public function testFetchRow()
    {
        $sql = "SELECT test_int, test_string FROM fetch_table WHERE test_int = ? AND test_string = ?";
        $row = $this->_conn->fetchAssociative($sql, array(1, 'foo'));

        $this->assertTrue($row !== false);

        $row = array_change_key_case($row, \CASE_LOWER);

        $this->assertEquals(1, $row['test_int']);
        $this->assertEquals('foo', $row['test_string']);
    }

    public function testFetchArray()
    {
        $sql = "SELECT test_int, test_string FROM fetch_table WHERE test_int = ? AND test_string = ?";
        $row = $this->_conn->fetchNumeric($sql, array(1, 'foo'));

        $this->assertEquals(1, $row[0]);
        $this->assertEquals('foo', $row[1]);
    }

    public function testFetchColumn()
    {
        $sql = "SELECT test_int, test_string FROM fetch_table WHERE test_int = ? AND test_string = ?";
        $stmt = $this->_conn->prepare($sql);

        $stmt->executeQuery(array(1, 'foo'));
        $testInt = $stmt->getWrappedStatement()->fetchColumn(0);
        $this->assertEquals(1, $testInt);

        $stmt->executeQuery(array(1, 'foo'));
        $testString = $stmt->getWrappedStatement()->fetchColumn(1);
        $this->assertEquals('foo', $testString);
    }

    /**
     * @group DDC-697
     */
    public function testExecuteQueryBindDateTimeType()
    {
        $sql = 'SELECT count(*) AS c FROM fetch_table WHERE test_datetime = ?';
        $stmt = $this->_conn->executeQuery($sql,
            array(1 => new \DateTime('2010-01-01 10:10:10')),
            array(1 => Types::DATETIME_MUTABLE)
        );

        $this->assertEquals(1, $stmt->fetchOne());
    }

    /**
     * @group DDC-697
     */
    public function testExecuteUpdateBindDateTimeType()
    {
        $datetime = new \DateTime('2010-02-02 20:20:20');

        $sql = 'INSERT INTO fetch_table (test_int, test_string, test_datetime) VALUES (?, ?, ?)';
        $affectedRows = $this->_conn->executeStatement($sql,
            array(1 => 50,              2 => 'foo',             3 => $datetime),
            array(1 => PDO::PARAM_INT,  2 => PDO::PARAM_STR,    3 => Types::DATETIME_MUTABLE)
        );
        $this->assertEquals(1, $affectedRows);
        $this->refresh('fetch_table');

        $this->assertEquals(1, $this->_conn->executeQuery(
            'SELECT count(*) AS c FROM fetch_table WHERE test_datetime = ?',
            array(1 => $datetime),
            array(1 => Types::DATETIME_MUTABLE)
        )->fetchOne());
    }

    /**
     * @group DDC-697
     */
    public function testPrepareQueryBindValueDateTimeType()
    {
        $sql = 'SELECT count(*) AS c FROM fetch_table WHERE test_datetime = ?';
        $stmt = $this->_conn->prepare($sql);
        $stmt->bindValue(1, new \DateTime('2010-01-01 10:10:10'), Types::DATETIME_MUTABLE);
        $result = $stmt->executeQuery();

        $this->assertEquals(1, $result->fetchOne());
    }

    /**
     * @group DBAL-78
     */
    public function testNativeArrayListSupport()
    {
        for ($i = 100; $i < 110; $i++) {
            $this->_conn->insert('fetch_table', array('test_int' => $i, 'test_string' => 'foo' . $i, 'test_datetime' => '2010-01-01T10:10:10'));
        }
        $this->refresh('fetch_table');

        $stmt = $this->_conn->executeQuery('SELECT test_int FROM fetch_table WHERE test_int IN (?) ORDER BY test_int',
            array(array(100, 101, 102, 103, 104)), array(Connection::PARAM_INT_ARRAY));

        $data = $stmt->fetchAllNumeric();
        $this->assertEquals(5, count($data));
        $this->assertEquals(array(array(100), array(101), array(102), array(103), array(104)), $data);

        $stmt = $this->_conn->executeQuery('SELECT test_int FROM fetch_table WHERE test_string IN (?) ORDER BY test_int',
            array(array('foo100', 'foo101', 'foo102', 'foo103', 'foo104')), array(Connection::PARAM_STR_ARRAY));

        $data = $stmt->fetchAllNumeric();
        $this->assertEquals(5, count($data));
        $this->assertEquals(array(array(100), array(101), array(102), array(103), array(104)), $data);
    }

    /**
     * @group DDC-1014
     */
    public function testDateArithmetics()
    {
        $this->markTestSkipped('Date arithmetics not supported by CrateDB platform');

        $p = $this->_conn->getDatabasePlatform();
        $sql = 'SELECT ';
        $sql .= $p->getDateDiffExpression('test_datetime', $p->getCurrentTimestampSQL()) .' AS diff, ';
        $sql .= $p->getDateAddDaysExpression('test_datetime', 10) .' AS add_days, ';
        $sql .= $p->getDateSubDaysExpression('test_datetime', 10) .' AS sub_days, ';
        $sql .= $p->getDateAddMonthExpression('test_datetime', 2) .' AS add_month, ';
        $sql .= $p->getDateSubMonthExpression('test_datetime', 2) .' AS sub_month ';
        $sql .= 'FROM fetch_table';

        $row = $this->_conn->fetchAssoc($sql);
        $row = array_change_key_case($row, CASE_LOWER);

        $diff = floor( (strtotime('2010-01-01')-time()) / 3600 / 24);
        $this->assertEquals($diff, (int)$row['diff'], "Date difference should be approx. ".$diff." days.", 1);
        $this->assertEquals('2010-01-11', date('Y-m-d', strtotime($row['add_days'])), "Adding date should end up on 2010-01-11");
        $this->assertEquals('2009-12-22', date('Y-m-d', strtotime($row['sub_days'])), "Subtracting date should end up on 2009-12-22");
        $this->assertEquals('2010-03-01', date('Y-m-d', strtotime($row['add_month'])), "Adding month should end up on 2010-03-01");
        $this->assertEquals('2009-11-01', date('Y-m-d', strtotime($row['sub_month'])), "Adding month should end up on 2009-11-01");
    }

    public function testQuoteSQLInjection()
    {
        $this->expectException(DBALException::class);

        $sql = "SELECT * FROM fetch_table WHERE test_string = bar' OR '1'='1";
        $this->_conn->executeQuery($sql);
    }

    /**
     * @group DDC-1213
     */
    public function testBitComparisonExpressionSupport()
    {
        $this->markTestSkipped("Bit comparison expressions not supported by CrateDB");

        $this->_conn->executeStatement('DELETE FROM fetch_table');
        $platform = $this->_conn->getDatabasePlatform();
        $bitmap   = array();

        for ($i = 2; $i < 9; $i = $i + 2) {
            $bitmap[$i] = array(
                'bit_or'    => ($i | 2),
                'bit_and'   => ($i & 2)
            );
            $this->_conn->insert('fetch_table', array(
                'test_int'      => $i,
                'test_string'   => json_encode($bitmap[$i]),
                'test_datetime' => '2010-01-01T10:10:10'
            ));
        }

        $sql[]  = 'SELECT ';
        $sql[]  = 'test_int, ';
        $sql[]  = 'test_string, ';
        $sql[]  = $platform->getBitOrComparisonExpression('test_int', 2) . ' AS bit_or, ';
        $sql[]  = $platform->getBitAndComparisonExpression('test_int', 2) . ' AS bit_and ';
        $sql[]  = 'FROM fetch_table';

        $stmt   = $this->_conn->executeQuery(implode(PHP_EOL, $sql));
        $data   = $stmt->fetchAllAssociative();


        $this->assertEquals(4, count($data));
        $this->assertEquals(count($bitmap), count($data));
        foreach ($data as $row) {
            $row = array_change_key_case($row, CASE_LOWER);

            $this->assertArrayHasKey('test_int', $row);

            $id = $row['test_int'];

            $this->assertArrayHasKey($id, $bitmap);
            $this->assertArrayHasKey($id, $bitmap);

            $this->assertArrayHasKey('bit_or', $row);
            $this->assertArrayHasKey('bit_and', $row);

            $this->assertEquals($row['bit_or'], $bitmap[$id]['bit_or']);
            $this->assertEquals($row['bit_and'], $bitmap[$id]['bit_and']);
        }
    }

    public function testSetDefaultFetchMode()
    {
        $result = $this->_conn->executeQuery("SELECT * FROM fetch_table");
        $row = array_keys($result->fetchAllNumeric());
        $this->assertEquals(0, count( array_filter($row, function($v) { return ! is_numeric($v); })), "should be no non-numerical elements in the result.");
    }

    /**
     * @group DBAL-196
     */
    public function testFetchAllSupportFetchClass()
    {
        $this->markTestSkipped("PDO::FETCH_CLASS is not supported by the CrateDB PDO driver");

        $this->setupFixture();

        $sql    = "SELECT test_int, test_string, test_datetime FROM fetch_table";
        $stmt   = $this->_conn->prepare($sql);
        $result = $stmt->executeQuery();

        $results = $result->fetch(
            PDO::FETCH_CLASS,
            __NAMESPACE__.'\\MyFetchClass'
        );

        $this->assertEquals(1, count($results));
        $this->assertInstanceOf(__NAMESPACE__.'\\MyFetchClass', $results[0]);

        $this->assertEquals(1, $results[0]->test_int);
        $this->assertEquals('foo', $results[0]->test_string);
        $this->assertStringStartsWith('2010-01-01T10:10:10', $results[0]->test_datetime);
    }

    /**
     * @group DBAL-241
     */
    public function testFetchAllStyleColumn()
    {
        $sql = "DELETE FROM fetch_table";
        $this->_conn->executeStatement($sql);

        $this->_conn->insert('fetch_table', array('test_int' => 1, 'test_string' => 'foo'));
        $this->_conn->insert('fetch_table', array('test_int' => 10, 'test_string' => 'foo'));
        $this->refresh("fetch_table");

        $sql = "SELECT test_int FROM fetch_table ORDER BY test_int ASC";
        $rows = $this->_conn->query($sql)->fetchFirstColumn();

        $this->assertEquals(array(1, 10), $rows);
    }

    /**
     * @group DBAL-214
     */
    public function testSetFetchModeClassFetchAll()
    {
        $this->markTestSkipped("PDO::FETCH_CLASS is not supported by the CrateDB PDO driver");

        $this->setupFixture();

        $sql = "SELECT * FROM fetch_table";
        $stmt = $this->_conn->query($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, __NAMESPACE__ . '\\MyFetchClass', array());

        $results = $stmt->fetchAll();

        $this->assertEquals(1, count($results));
        $this->assertInstanceOf(__NAMESPACE__.'\\MyFetchClass', $results[0]);

        $this->assertEquals(1, $results[0]->test_int);
        $this->assertEquals('foo', $results[0]->test_string);
        $this->assertStringStartsWith('2010-01-01T10:10:10', $results[0]->test_datetime);
    }

    /**
     * @group DBAL-214
     */
    public function testSetFetchModeClassFetch()
    {
        $this->markTestSkipped("PDO::FETCH_CLASS is not supported by the CrateDB PDO driver");

        $this->setupFixture();

        $sql = "SELECT * FROM fetch_table";
        $stmt = $this->_conn->query($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, __NAMESPACE__ . '\\MyFetchClass', array());

        $results = array();
        while ($row = $stmt->fetch()) {
            $results[] = $row;
        }

        $this->assertEquals(1, count($results));
        $this->assertInstanceOf(__NAMESPACE__.'\\MyFetchClass', $results[0]);

        $this->assertEquals(1, $results[0]->test_int);
        $this->assertEquals('foo', $results[0]->test_string);
        $this->assertStringStartsWith('2010-01-01T10:10:10', $results[0]->test_datetime);
    }

    /**
     * @group DBAL-257
     */
    public function testEmptyFetchColumnReturnsFalse()
    {
        $this->_conn->executeStatement('DELETE FROM fetch_table');
        $this->refresh("fetch_table");
        $this->assertFalse($this->_conn->fetchOne('SELECT test_int FROM fetch_table'));
        $this->assertFalse($this->_conn->executeQuery('SELECT test_int FROM fetch_table')->fetchOne());
    }

    /**
     * @group DBAL-339
     */
    public function testSetFetchModeOnDbalStatement()
    {
        $sql = "SELECT test_int, test_string FROM fetch_table WHERE test_int = ? AND test_string = ?";
        $stmt = $this->_conn->executeQuery($sql, array(1, "foo"));

        foreach ($stmt->iterateNumeric() as $row) {
            $this->assertTrue(isset($row[0]));
            $this->assertTrue(isset($row[1]));
        }
    }

    private function setupFixture()
    {
        $this->_conn->executeStatement('DELETE FROM fetch_table');
        $this->_conn->insert('fetch_table', array(
            'test_int'      => 1,
            'test_string'   => 'foo',
            'test_datetime' => '2010-01-01T10:10:10'
        ));
    }

}
