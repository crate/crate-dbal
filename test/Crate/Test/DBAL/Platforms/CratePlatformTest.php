<?php /** @noinspection PhpUnhandledExceptionInspection */

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

namespace Crate\Test\DBAL\Platforms;

use Crate\DBAL\Driver\PDOCrate\Driver;
use Crate\DBAL\Platforms\CratePlatform;
use Crate\DBAL\Types\ArrayType;
use Crate\DBAL\Types\MapType;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Events;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Types\Type;
use Doctrine\Tests\DBAL\Platforms\AbstractPlatformTestCase;

class CratePlatformTest extends AbstractPlatformTestCase {

    private const CRATE_TEST_VERSION = "4.0.0";

    public function createPlatform()
    {
        $driver = new Driver();
        return $driver->createDatabasePlatformForVersion(self::CRATE_TEST_VERSION);
    }

    public function getGenerateTableSql()
    {
        return 'CREATE TABLE test (id INTEGER NOT NULL, test TEXT, PRIMARY KEY(id))';
    }

    public function getGenerateTableWithMultiColumnUniqueIndexSql()
    {
    }

    public function getGenerateTableWithMultiColumnIndexSql()
    {
        return array(
                'CREATE TABLE test (foo TEXT, bar TEXT, ' .
                'INDEX IDX_D87F7E0C8C73652176FF8CAA USING FULLTEXT (foo, bar))'
        );
    }

    public function getGenerateIndexSql()
    {
        $this->markTestSkipped('Platform does not support CREATE INDEX.');
    }

    public function getGenerateUniqueIndexSql()
    {
        $this->markTestSkipped('Platform does not support CREATE UNIQUE INDEX.');
    }

    public function testGeneratesForeignKeyCreationSql()
    {
        $this->markTestSkipped('Platform does not support FOREIGN KEY constraints.');
    }

    public function getGenerateForeignKeySql()
    {
        $this->markTestSkipped('Platform does not support ADD FOREIGN KEY.');
    }

    /**
     * @param mixed[] $column
     *
     * @group DBAL-1082
     * @dataProvider getGeneratesDecimalTypeDeclarationSQL
     */
    public function testGeneratesDecimalTypeDeclarationSQL(array $column, $expectedSql)
    {
        $this->markTestSkipped('Platform does not support any decleration of datatype DECIMAL.');
    }

    public function getGenerateAlterTableSql()
    {
        return array(
            'ALTER TABLE mytable ADD quota INTEGER',
        );
    }

    public function testAlterTableChangeQuotedColumn()
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getQuotedColumnInPrimaryKeySQL()
    {
        return array(
            'CREATE TABLE "quoted" ("create" TEXT NOT NULL, PRIMARY KEY("create"))',
        );
    }

    protected function getQuotedColumnInIndexSQL()
    {
        return array(
            'CREATE TABLE "quoted" ("create" TEXT, ' .
            'INDEX IDX_22660D028FD6E0FB USING FULLTEXT ("create")' .
            ')'
        );
    }

    protected function getQuotedNameInIndexSQL()
    {
        return array(
            'CREATE TABLE test (column1 TEXT, INDEX key USING FULLTEXT (column1))'
        );
    }

    /**
     * @group DBAL-374
     */
    public function testQuotedColumnInForeignKeyPropagation()
    {
        $this->markTestSkipped('Platform does not support ADD FOREIGN KEY.');
    }

    protected function getQuotedColumnInForeignKeySQL() {}

    protected function getQuotesReservedKeywordInUniqueConstraintDeclarationSQL()
    {
        return 'CONSTRAINT "select" UNIQUE (foo)';
    }

    protected function getQuotesReservedKeywordInIndexDeclarationSQL()
    {
        return 'INDEX "select" USING FULLTEXT (foo)';
    }

    /**
     * @group DBAL-835
     */
    public function testQuotesAlterTableRenameColumn()
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getQuotedAlterTableRenameColumnSQL() {}

    /**
     * @group DBAL-835
     */
    public function testQuotesAlterTableChangeColumnLength()
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getQuotedAlterTableChangeColumnLengthSQL() {}

    /**
     * @group DBAL-807
     */
    public function testQuotesAlterTableRenameIndexInSchema()
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getCommentOnColumnSQL()
    {
        return array(
            "COMMENT ON COLUMN foo.bar IS 'comment'",
            "COMMENT ON COLUMN \"Foo\".\"BAR\" IS 'comment'",
            "COMMENT ON COLUMN \"select\".\"from\" IS 'comment'",
        );
    }

    /**
     * @group DBAL-1010
     */
    public function testGeneratesAlterTableRenameColumnSQL()
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    public function getAlterTableRenameColumnSQL() {}

    /**
     * @group DBAL-1016
     */
    public function testQuotesTableIdentifiersInAlterTableSQL()
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getQuotesTableIdentifiersInAlterTableSQL() {}

    /**
     * @group DBAL-1062
     */
    public function testGeneratesAlterTableRenameIndexUsedByForeignKeySQL()
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getGeneratesAlterTableRenameIndexUsedByForeignKeySQL() {}

    /**
     * @group DBAL-1090
     */
    public function testAlterStringToFixedString()
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getAlterStringToFixedStringSQL() {}

    public function testGenerateSubstrExpression()
    {
        $this->assertEquals($this->platform->getSubstringExpression('col', 0), "SUBSTR(col, 0)");
        $this->assertEquals($this->platform->getSubstringExpression('col', 1, 2), "SUBSTR(col, 1, 2)");
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Operation 'Crate\DBAL\Platforms\CratePlatform::getNowExpression' is not supported by platform.
     */
    public function testGenerateNowExpression()
    {
        $this->platform->getNowExpression();
    }

    public function testGenerateRegexExpression()
    {
        $this->assertEquals($this->platform->getRegexpExpression(), "LIKE");
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Operation 'Crate\DBAL\Platforms\CratePlatform::getDateDiffExpression' is not supported by platform.
     */
    public function testGenerateDateDiffExpression()
    {
        $this->platform->getDateDiffExpression('2014-10-10 10:10:10', '2014-10-20 20:20:20');
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Operation 'Crate\DBAL\Platforms\CratePlatform::getCreateDatabaseSQL' is not supported by platform.
     */
    public function testCreateDatabases()
    {
        $this->platform->getCreateDatabaseSQL('foo');
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Operation 'Crate\DBAL\Platforms\CratePlatform::getListDatabasesSQL' is not supported by platform.
     */
    public function testListDatabases()
    {
        $this->platform->getListDatabasesSQL();
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Operation 'Crate\DBAL\Platforms\CratePlatform::getDropDatabaseSQL' is not supported by platform.
     */
    public function testDropDatabases()
    {
        $this->platform->getDropDatabaseSQL('foo');
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Operation 'Crate\DBAL\Platforms\CratePlatform::getBlobTypeDeclarationSQL' is not supported by platform.
     */
    public function testGenerateBlobTypeGeneration()
    {
        $this->platform->getBlobTypeDeclarationSQL(array());
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     */
    public function testTruncateTableSQL()
    {
        $this->platform->getTruncateTableSQL('foo');
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     */
    public function testReadLockSQL()
    {
        $this->platform->getReadLockSQL();
    }

    public function testConvertBooleans()
    {
        $this->assertEquals($this->platform->convertBooleans(false), 'false');
        $this->assertEquals($this->platform->convertBooleans(true), 'true');

        $this->assertEquals($this->platform->convertBooleans(0), 'false');
        $this->assertEquals($this->platform->convertBooleans(1), 'true');

        $this->assertEquals($this->platform->convertBooleans(array(true, 1, false, 0)),
            array('true', 'true', 'false', 'false'));
    }

    public function testSQLResultCasting()
    {
        $this->assertEquals($this->platform->getSQLResultCasing("LoWeRcAsE"), 'lowercase');
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage No columns specified for table foo
     */
    public function testGenerateTableSqlWithoutColumns()
    {
        $table = new Table("foo");
        $this->assertEquals($this->platform->getCreateTableSQL($table)[0],
            'CREATE TABLE foo');
    }

    public function testGenerateTableSql()
    {
        $table = new Table("foo");
        $table->addColumn('col_bool', 'boolean', array('notnull' => false));
        $table->addColumn('col_int', 'integer', array('notnull' => false));
        $table->addColumn('col_float', 'float', array('notnull' => false));
        $table->addColumn('col_timestamp', 'timestamp', array('notnull' => false));
        $table->addColumn('col_datetimetz', 'datetimetz', array('notnull' => false));
        $table->addColumn('col_datetime', 'datetime', array('notnull' => false));
        $table->addColumn('col_date', 'date', array('notnull' => false));
        $table->addColumn('col_time', 'time', array('notnull' => false));
        $table->addColumn('col_array', 'array', array('notnull' => false));
        $table->addColumn('col_object', 'map', array('notnull' => false));
        $this->assertEquals($this->platform->getCreateTableSQL($table)[0],
            'CREATE TABLE foo (col_bool BOOLEAN, col_int INTEGER, col_float DOUBLE PRECISION, col_timestamp TIMESTAMP, col_datetimetz TIMESTAMP, col_datetime TIMESTAMP, col_date TIMESTAMP, col_time TIMESTAMP, col_array ARRAY ( TEXT ), col_object OBJECT ( dynamic ))');
    }

    public function testUnsupportedUniqueIndexConstraint()
    {
        $this->expectException(DBALException::class);
        $this->expectExceptionMessage("Unique constraints are not supported. Use `primary key` instead");

        $table = new Table("foo");
        $table->addColumn("unique_string", "string");
        $table->addUniqueIndex(array("unique_string"));
        $this->platform->getCreateTableSQL($table);
    }

    public function testUniqueConstraintInCustomSchemaOptions()
    {
        $this->expectException(DBALException::class);
        $this->expectExceptionMessage("Unique constraints are not supported. Use `primary key` instead");

        $table = new Table("foo");
        $table->addColumn("unique_string", "string")->setCustomSchemaOption("unique", true);
        $this->platform->getCreateTableSQL($table);
    }

    public function testGeneratesTableAlterationSql()
    {
        $expectedSql = $this->getGenerateAlterTableSql();

        $tableDiff = new TableDiff('mytable');
        $tableDiff->addedColumns['quota'] = new \Doctrine\DBAL\Schema\Column('quota', \Doctrine\DBAL\Types\Type::getType('integer'), array('notnull' => false));

        $sql = $this->platform->getAlterTableSQL($tableDiff);

        $this->assertEquals($expectedSql, $sql);
    }

    public function testGetAlterTableSqlDispatchEvent()
    {
        $events = array(
            'onSchemaAlterTableAddColumn'
        );

        $listenerMock = $this->getMockBuilder('GetAlterTableSqlDispatchEvenListener')
                ->setMethods($events)
                ->getMock();
        $listenerMock
            ->expects($this->once())
            ->method('onSchemaAlterTableAddColumn');

        $eventManager = new EventManager();
        $events = array(
            Events::onSchemaAlterTableAddColumn,
        );
        $eventManager->addEventListener($events, $listenerMock);

        $this->platform->setEventManager($eventManager);

        $tableDiff = new TableDiff('mytable');
        $tableDiff->addedColumns['added'] = new \Doctrine\DBAL\Schema\Column('added', \Doctrine\DBAL\Types\Type::getType('integer'), array());

        $this->platform->getAlterTableSQL($tableDiff);
    }

    public function testGenerateTableWithMultiColumnUniqueIndex()
    {
        $table = new Table('test');
        $table->addColumn('foo', 'string', array('notnull' => false, 'length' => 255));
        $table->addColumn('bar', 'string', array('notnull' => false, 'length' => 255));
        $table->addUniqueIndex(array("foo", "bar"));

        $this->expectException(DBALException::class);
        $this->expectExceptionMessage('Operation \'Unique constraints are not supported. Use `primary key` instead\' is not supported by platform.');

        $this->platform->getCreateTableSQL($table);
    }

    public function testGenerateTableWithMultiColumnIndex()
    {
        $table = new Table('test');
        $table->addColumn('foo', 'string', array('notnull' => false, 'length' => 255));
        $table->addColumn('bar', 'string', array('notnull' => false, 'length' => 255));
        $table->addIndex(array("foo", "bar"));

        $sql = $this->platform->getCreateTableSQL($table);
        $this->assertEquals($this->getGenerateTableWithMultiColumnIndexSql(), $sql);
    }

    /**
     * @param Column $column
     */
    private function getSQLDeclaration($column)
    {
        $p = $this->platform;
        return $p->getColumnDeclarationSQL($column->getName(), CratePlatform::prepareColumnData($p, $column));
    }

    public function testGenerateObjectSQLDeclaration()
    {

        $column = new Column('obj', Type::getType(MapType::NAME), array('notnull' => false));
        $this->assertEquals($this->getSQLDeclaration($column), 'obj OBJECT ( dynamic )');

        $column = new Column('obj', Type::getType(MapType::NAME),
            array('platformOptions'=>array('type'=>MapType::STRICT), 'notnull' => false));
        $this->assertEquals($this->getSQLDeclaration($column), 'obj OBJECT ( strict )');

        $column = new Column('obj', Type::getType(MapType::NAME),
            array('platformOptions'=>array('type'=>MapType::IGNORED, 'fields'=>array()), 'notnull' => false));
        $this->assertEquals($this->getSQLDeclaration($column), 'obj OBJECT ( ignored )');

        $column = new Column('obj', Type::getType(MapType::NAME),
            array('platformOptions'=>array(
                'type'=>MapType::STRICT,
                'fields'=>array(
                    new Column('num', Type::getType(Type::INTEGER), array('notnull' => false)),
                    new Column('text', Type::getType(Type::STRING), array('notnull' => false)),
                    new Column('arr', Type::getType(ArrayType::NAME), array('notnull' => false)),
                    new Column('obj', Type::getType(MapType::NAME), array('notnull' => false)),
                ),
            ),
            'notnull' => false));
        $this->assertEquals($this->getSQLDeclaration($column), 'obj OBJECT ( strict ) AS ( num INTEGER, text TEXT, arr ARRAY ( TEXT ), obj OBJECT ( dynamic ) )');

    }

    public function testGenerateArraySQLDeclaration()
    {
        $column = new Column('arr', Type::getType(ArrayType::NAME), array('notnull' => false));
        $this->assertEquals($this->getSQLDeclaration($column), 'arr ARRAY ( TEXT )');

        $column = new Column('arr', Type::getType(ArrayType::NAME),
            array('platformOptions'=> array('type'=>Type::INTEGER), 'notnull' => false));
        $this->assertEquals($this->getSQLDeclaration($column), 'arr ARRAY ( INTEGER )');

    }

    public function testPlatformSupport() {
        $this->assertFalse($this->platform->supportsSequences());
        $this->assertFalse($this->platform->supportsSchemas());
        $this->assertTrue($this->platform->supportsIdentityColumns());
        $this->assertFalse($this->platform->supportsIndexes());
        $this->assertFalse($this->platform->supportsCommentOnStatement());
        $this->assertFalse($this->platform->supportsForeignKeyConstraints());
        $this->assertFalse($this->platform->supportsForeignKeyOnUpdate());
        $this->assertFalse($this->platform->supportsViews());
        $this->assertFalse($this->platform->prefersSequences());
    }

    /**
     * @return string
     */
    protected function getQuotesReservedKeywordInTruncateTableSQL()
    {
        $this->markTestSkipped('Platform does not support TRUNCATE TABLE.');
    }

    /**
     * @inheritDoc
     */
    public function testGetDefaultValueDeclarationSQLDateTime() : void
    {
        $this->markTestSkipped('Platform does not support DateTime types, use Timestamp instead.');
    }

    /**
     * @inheritDoc
     */
    public function testGetDefaultValueDeclarationSQLForDateType() : void
    {
        $this->markTestSkipped('Platform does not support Date types, use Timestamp instead.');
    }
}
