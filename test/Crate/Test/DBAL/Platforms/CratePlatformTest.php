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
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Tests\Platforms\AbstractPlatformTestCase;

class CratePlatformTest extends AbstractPlatformTestCase {

    private const CRATE_TEST_VERSION = "4.0.0";

    public function createPlatform() : AbstractPlatform
    {
        $driver = new Driver();
        return $driver->createDatabasePlatformForVersion(self::CRATE_TEST_VERSION);
    }

    public function getGenerateTableSql() : string
    {
        return 'CREATE TABLE test (id INTEGER, test TEXT, PRIMARY KEY(id))';
    }

    public function getGenerateTableWithMultiColumnUniqueIndexSql() : array
    {
    }

    public function getGenerateTableWithMultiColumnIndexSql()
    {
        return array(
            'CREATE TABLE test (foo TEXT, bar TEXT, ' .
            'INDEX IDX_D87F7E0C8C73652176FF8CAA USING FULLTEXT (foo, bar))'
        );
    }

    public function getGenerateIndexSql() : string
    {
        $this->markTestSkipped('Platform does not support CREATE INDEX.');
    }

    public function getGenerateUniqueIndexSql() : string
    {
        $this->markTestSkipped('Platform does not support CREATE UNIQUE INDEX.');
    }

    public function testGeneratesForeignKeyCreationSql() : void
    {
        $this->markTestSkipped('Platform does not support FOREIGN KEY constraints.');
    }

    public function getGenerateForeignKeySql() : string
    {
        $this->markTestSkipped('Platform does not support ADD FOREIGN KEY.');
    }

    /**
     * @param mixed[] $column
     *
     * @group DBAL-1082
     * @dataProvider getGeneratesDecimalTypeDeclarationSQL
     */
    public function testGeneratesDecimalTypeDeclarationSQL(array $column, $expectedSql) : void
    {
        $this->markTestSkipped('Platform does not support any decleration of datatype DECIMAL.');
    }

    public function getGenerateAlterTableSql() : array
    {
        return array(
            'ALTER TABLE mytable ADD quota INTEGER',
        );
    }

    public function testAlterTableChangeQuotedColumn() : void
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getQuotedColumnInPrimaryKeySQL() : array
    {
        return array(
            'CREATE TABLE "quoted" ("create" TEXT, PRIMARY KEY("create"))',
        );
    }

    protected function getQuotedColumnInIndexSQL() : array
    {
        return array(
            'CREATE TABLE "quoted" ("create" TEXT, ' .
            'INDEX IDX_22660D028FD6E0FB USING FULLTEXT ("create")' .
            ')'
        );
    }

    protected function getQuotedNameInIndexSQL() : array
    {
        return array(
            'CREATE TABLE test (column1 TEXT, INDEX key USING FULLTEXT (column1))'
        );
    }

    /**
     * @group DBAL-374
     */
    public function testQuotedColumnInForeignKeyPropagation() : void
    {
        $this->markTestSkipped('Platform does not support ADD FOREIGN KEY.');
    }

    protected function getQuotedColumnInForeignKeySQL() : array {}

    protected function getQuotesReservedKeywordInUniqueConstraintDeclarationSQL() : string
    {
        return 'CONSTRAINT "select" UNIQUE (foo)';
    }

    protected function getQuotesReservedKeywordInIndexDeclarationSQL() : string
    {
        return 'INDEX "select" USING FULLTEXT (foo)';
    }

    /**
     * @group DBAL-835
     */
    public function testQuotesAlterTableRenameColumn() : void
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getQuotedAlterTableRenameColumnSQL() : array {}

    /**
     * @group DBAL-835
     */
    public function testQuotesAlterTableChangeColumnLength() : void
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getQuotedAlterTableChangeColumnLengthSQL() : array {}

    /**
     * @group DBAL-807
     */
    public function testQuotesAlterTableRenameIndexInSchema() : void
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getCommentOnColumnSQL() : array
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
    public function testGeneratesAlterTableRenameColumnSQL() : void
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    public function getAlterTableRenameColumnSQL() : array {}

    /**
     * @group DBAL-1016
     */
    public function testQuotesTableIdentifiersInAlterTableSQL() : void
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getQuotesTableIdentifiersInAlterTableSQL() : array {}

    /**
     * @group DBAL-1062
     */
    public function testGeneratesAlterTableRenameIndexUsedByForeignKeySQL() : void
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getGeneratesAlterTableRenameIndexUsedByForeignKeySQL() : array {}

    /**
     * @group DBAL-1090
     */
    public function testAlterStringToFixedString() : void
    {
        $this->markTestSkipped('Platform does not support ALTER TABLE.');
    }

    protected function getAlterStringToFixedStringSQL() : array {}

    public function testGenerateSubstrExpression()
    {
        $this->assertEquals($this->platform->getSubstringExpression('col', 0), "SUBSTR(col, 0)");
        $this->assertEquals($this->platform->getSubstringExpression('col', 1, 2), "SUBSTR(col, 1, 2)");
    }

    public function testGenerateNowExpression()
    {
        $this->expectException(DBALException::class);
        $this->expectExceptionMessage('Operation \'Crate\DBAL\Platforms\CratePlatform::getNowExpression\' is not supported by platform.');
        $this->platform->getNowExpression();
    }

    public function testGenerateRegexExpression()
    {
        $this->assertEquals($this->platform->getRegexpExpression(), "LIKE");
    }

    public function testGenerateDateDiffExpression()
    {
        $this->expectException(DBALException::class);
        $this->expectExceptionMessage('Operation \'Crate\DBAL\Platforms\CratePlatform::getDateDiffExpression\' is not supported by platform.');

        $this->platform->getDateDiffExpression('2014-10-10 10:10:10', '2014-10-20 20:20:20');
    }

    public function testCreateDatabases()
    {
        $this->expectException(DBALException::class);
        $this->expectExceptionMessage('Operation \'Crate\DBAL\Platforms\CratePlatform::getCreateDatabaseSQL\' is not supported by platform.');

        $this->platform->getCreateDatabaseSQL('foo');
    }

    public function testListDatabases()
    {
        $this->expectException(DBALException::class);
        $this->expectExceptionMessage('Operation \'Crate\DBAL\Platforms\CratePlatform::getListDatabasesSQL\' is not supported by platform.');

        $this->platform->getListDatabasesSQL();
    }

    public function testDropDatabases()
    {
        $this->expectException(DBALException::class);
        $this->expectExceptionMessage('Operation \'Crate\DBAL\Platforms\CratePlatform::getDropDatabaseSQL\' is not supported by platform.');

        $this->platform->getDropDatabaseSQL('foo');
    }

    public function testGenerateBlobTypeGeneration()
    {
        $this->expectException(DBALException::class);
        $this->expectExceptionMessage('Operation \'Crate\DBAL\Platforms\CratePlatform::getBlobTypeDeclarationSQL\' is not supported by platform.');

        $this->platform->getBlobTypeDeclarationSQL(array());
    }

    public function testTruncateTableSQL()
    {
        $this->expectException(DBALException::class);

        $this->platform->getTruncateTableSQL('foo');
    }

    public function testReadLockSQL()
    {
        $this->expectException(DBALException::class);

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

    public function testGenerateTableSqlWithoutColumns()
    {
        $this->expectException(DBALException::class);
        $this->expectExceptionMessage('No columns specified for table foo');


        $table = new Table("foo");
        $this->assertEquals($this->platform->getCreateTableSQL($table)[0],
            'CREATE TABLE foo');
    }

    public function testGenerateTableSql()
    {
        $table = new Table("foo");
        $table->addColumn('col_bool', 'boolean');
        $table->addColumn('col_int', 'integer');
        $table->addColumn('col_float', 'float');
        $table->addColumn('col_timestamp', 'timestamp');
        $table->addColumn('col_datetimetz', 'datetimetz');
        $table->addColumn('col_datetime', 'datetime');
        $table->addColumn('col_date', 'date');
        $table->addColumn('col_time', 'time');
        $table->addColumn('col_array', 'array');
        $table->addColumn('col_object', 'map');
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

    public function testGeneratesTableAlterationSql() : void
    {
        $expectedSql = $this->getGenerateAlterTableSql();

        $tableDiff = new TableDiff('mytable');
        $tableDiff->addedColumns['quota'] = new \Doctrine\DBAL\Schema\Column('quota', \Doctrine\DBAL\Types\Type::getType('integer'), array('notnull' => false));

        $sql = $this->platform->getAlterTableSQL($tableDiff);

        $this->assertEquals($expectedSql, $sql);
    }

    public function testGetAlterTableSqlDispatchEvent() : void
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

    public function testGenerateTableWithMultiColumnUniqueIndex() : void
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

        $column = new Column('obj', Type::getType(MapType::NAME));
        $this->assertEquals($this->getSQLDeclaration($column), 'obj OBJECT ( dynamic )');

        $column = new Column('obj', Type::getType(MapType::NAME),
            array('platformOptions'=>array('type'=>MapType::STRICT)));
        $this->assertEquals($this->getSQLDeclaration($column), 'obj OBJECT ( strict )');

        $column = new Column('obj', Type::getType(MapType::NAME),
            array('platformOptions'=>array('type'=>MapType::IGNORED, 'fields'=>array())));
        $this->assertEquals($this->getSQLDeclaration($column), 'obj OBJECT ( ignored )');

        $column = new Column('obj', Type::getType(MapType::NAME),
            array('platformOptions'=>array(
                'type'=>MapType::STRICT,
                'fields'=>array(
                    new Column('num', Type::getType(Type::INTEGER)),
                    new Column('text', Type::getType(Type::STRING)),
                    new Column('arr', Type::getType(ArrayType::NAME)),
                    new Column('obj', Type::getType(MapType::NAME)),
                ),
            )));
        $this->assertEquals($this->getSQLDeclaration($column), 'obj OBJECT ( strict ) AS ( num INTEGER, text TEXT, arr ARRAY ( TEXT ), obj OBJECT ( dynamic ) )');

    }

    public function testGenerateArraySQLDeclaration()
    {
        $column = new Column('arr', Type::getType(ArrayType::NAME));
        $this->assertEquals($this->getSQLDeclaration($column), 'arr ARRAY ( TEXT )');

        $column = new Column('arr', Type::getType(ArrayType::NAME),
            array('platformOptions'=> array('type'=>Type::INTEGER)));
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
    protected function getQuotesReservedKeywordInTruncateTableSQL() : string
    {
        $this->markTestSkipped('Platform does not support TRUNCATE TABLE.');
    }

    /**
     * @return array<int, array{string, array<string, mixed>}>
     */
    public function asciiStringSqlDeclarationDataProvider() : array
    {
        return [
            ['TEXT', ['length' => 12]],
            ['TEXT', ['length' => 12, 'fixed' => true]],
        ];
    }
}
