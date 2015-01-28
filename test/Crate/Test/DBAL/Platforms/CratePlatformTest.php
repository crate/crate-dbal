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

namespace Crate\Test\DBAL\Platforms;

use Crate\DBAL\Platforms\CratePlatform;
use Crate\DBAL\Types\ArrayType;
use Crate\DBAL\Types\MapType;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Events;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Types\Type;
use Doctrine\Tests\DBAL\Platforms\AbstractPlatformTestCase;

class CratePlatformTest extends AbstractPlatformTestCase {

    public function createPlatform()
    {
        return new CratePlatform();
    }

    public function getGenerateTableSql()
    {
        return 'CREATE TABLE test (id INTEGER, test STRING, PRIMARY KEY(id))';
    }

    public function getGenerateTableWithMultiColumnUniqueIndexSql()
    {
        return array(
                'CREATE TABLE test (foo STRING, bar STRING, ' .
                'INDEX UNIQ_D87F7E0C8C73652176FF8CAA USING FULLTEXT (foo, bar))'
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
        $fk = new \Doctrine\DBAL\Schema\ForeignKeyConstraint(array('fk_name_id'), 'other_table', array('id'), '');
    
        $this->assertEquals(
            $this->getGenerateForeignKeySql(),
            $this->_platform->getCreateForeignKeySQL($fk, 'test')
        );
    }
    
    public function getGenerateForeignKeySql()
    {
        $this->markTestSkipped('Platform does not support ADD FOREIGN KEY.');
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
            'CREATE TABLE "quoted" ("create" STRING, PRIMARY KEY("create"))',
        );
    }

    protected function getQuotedColumnInIndexSQL()
    {
        return array(
            'CREATE TABLE "quoted" ("create" STRING, ' .
            'INDEX IDX_22660D028FD6E0FB USING FULLTEXT ("create")' .
            ')'
        );
    }

    /**
     * @todo
     */
    protected function getQuotedNameInIndexSQL()
    {
        return array(
            'CREATE TABLE test (column1 STRING, INDEX "create" USING FULLTEXT (column1))'
        );
    }
    
    /**
     * @todo
     */
    protected function getQuotedColumnInForeignKeySQL()
    {
        $this->markTestSkipped('Platform does not support ADD FOREIGN KEY.');
    }
    
    /**
     * @todo
     */
    protected function getQuotesReservedKeywordInUniqueConstraintDeclarationSQL()
    {
        return 'CONSTRAINT "select" UNIQUE (foo)';
    }
    
    /**
     * @todo
     */
    protected function getQuotesReservedKeywordInIndexDeclarationSQL()
    {
        return 'INDEX "select" USING FULLTEXT (foo)';
    }
    
    /**
     * @todo
     */
    protected function getQuotedAlterTableRenameColumnSQL()
    {
        return array(
            "ALTER TABLE mytable " .
            "CHANGE unquoted1 unquoted INT NOT NULL COMMENT 'Unquoted 1', " .
            "CHANGE unquoted2 `where` INT NOT NULL COMMENT 'Unquoted 2', " .
            "CHANGE unquoted3 `foo` INT NOT NULL COMMENT 'Unquoted 3', " .
            "CHANGE `create` reserved_keyword INT NOT NULL COMMENT 'Reserved keyword 1', " .
            "CHANGE `table` `from` INT NOT NULL COMMENT 'Reserved keyword 2', " .
            "CHANGE `select` `bar` INT NOT NULL COMMENT 'Reserved keyword 3', " .
            "CHANGE quoted1 quoted INT NOT NULL COMMENT 'Quoted 1', " .
            "CHANGE quoted2 `and` INT NOT NULL COMMENT 'Quoted 2', " .
            "CHANGE quoted3 `baz` INT NOT NULL COMMENT 'Quoted 3'"
        );
    }
    
    /**
     * @todo
     */
    protected function getQuotedAlterTableChangeColumnLengthSQL()
    {
        return array(
            "ALTER TABLE mytable " .
            "CHANGE unquoted1 unquoted1 VARCHAR(255) NOT NULL COMMENT 'Unquoted 1', " .
            "CHANGE unquoted2 unquoted2 VARCHAR(255) NOT NULL COMMENT 'Unquoted 2', " .
            "CHANGE unquoted3 unquoted3 VARCHAR(255) NOT NULL COMMENT 'Unquoted 3', " .
            "CHANGE `create` `create` VARCHAR(255) NOT NULL COMMENT 'Reserved keyword 1', " .
            "CHANGE `table` `table` VARCHAR(255) NOT NULL COMMENT 'Reserved keyword 2', " .
            "CHANGE `select` `select` VARCHAR(255) NOT NULL COMMENT 'Reserved keyword 3'"
        );
    }
    
    /**
     * @todo
     */
    protected function getCommentOnColumnSQL()
    {
        return array(
            "COMMENT ON COLUMN foo.bar IS 'comment'",
            "COMMENT ON COLUMN `Foo`.`BAR` IS 'comment'",
            "COMMENT ON COLUMN `select`.`from` IS 'comment'",
        );
    }

    /**
     * @todo
     */
    public function getAlterTableRenameColumnSQL()
    {
        return array(
            "ALTER TABLE foo CHANGE bar baz INT DEFAULT 666 NOT NULL COMMENT 'rename test'",
        );
    }
    
    /**
     * @todo
     */
    protected function getQuotesTableIdentifiersInAlterTableSQL()
    {
        return array(
            'ALTER TABLE `foo` DROP FOREIGN KEY fk1',
            'ALTER TABLE `foo` DROP FOREIGN KEY fk2',
            'ALTER TABLE `foo` RENAME TO `table`, ADD bloo INT NOT NULL, DROP baz, CHANGE bar bar INT DEFAULT NULL, ' .
            'CHANGE id war INT NOT NULL',
            'ALTER TABLE `table` ADD CONSTRAINT fk_add FOREIGN KEY (fk3) REFERENCES fk_table (id)',
            'ALTER TABLE `table` ADD CONSTRAINT fk2 FOREIGN KEY (fk2) REFERENCES fk_table2 (id)',
        );
    }
    
    /**
     * @todo
     */
    protected function getAlterStringToFixedStringSQL()
    {
        return array(
            'ALTER TABLE mytable CHANGE name name CHAR(2) NOT NULL',
        );
    }
    
    /**
     * @todo
     */
    protected function getGeneratesAlterTableRenameIndexUsedByForeignKeySQL()
    {
        return array(
            'ALTER TABLE mytable DROP FOREIGN KEY fk_foo',
            'DROP INDEX idx_foo ON mytable',
            'CREATE INDEX idx_foo_renamed ON mytable (foo)',
            'ALTER TABLE mytable ADD CONSTRAINT fk_foo FOREIGN KEY (foo) REFERENCES foreign_table (id)',
        );
    }
    

    public function testGenerateSubstrExpression()
    {
        $this->assertEquals($this->_platform->getSubstringExpression('col'), "SUBSTR(col, 0)");
        $this->assertEquals($this->_platform->getSubstringExpression('col', 0), "SUBSTR(col, 0)");
        $this->assertEquals($this->_platform->getSubstringExpression('col', 1, 2), "SUBSTR(col, 1, 2)");
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Operation 'Crate\DBAL\Platforms\CratePlatform::getNowExpression' is not supported by platform.
     */
    public function testGenerateNowExpression()
    {
        $this->_platform->getNowExpression();
    }

    public function testGenerateRegexExpression()
    {
        $this->assertEquals($this->_platform->getRegexpExpression(), "LIKE");
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Operation 'Crate\DBAL\Platforms\CratePlatform::getDateDiffExpression' is not supported by platform.
     */
    public function testGenerateDateDiffExpression()
    {
        $this->_platform->getDateDiffExpression('2014-10-10 10:10:10', '2014-10-20 20:20:20');
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Operation 'Crate\DBAL\Platforms\CratePlatform::getCreateDatabaseSQL' is not supported by platform.
     */
    public function testCreateDatabases()
    {
        $this->_platform->getCreateDatabaseSQL('foo');
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Operation 'Crate\DBAL\Platforms\CratePlatform::getListDatabasesSQL' is not supported by platform.
     */
    public function testListDatabases()
    {
        $this->_platform->getListDatabasesSQL();
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Operation 'Crate\DBAL\Platforms\CratePlatform::getDropDatabaseSQL' is not supported by platform.
     */
    public function testDropDatabases()
    {
        $this->_platform->getDropDatabaseSQL('foo');
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Operation 'Crate\DBAL\Platforms\CratePlatform::getBlobTypeDeclarationSQL' is not supported by platform.
     */
    public function testGenerateBlobTypeGeneration()
    {
        $this->_platform->getBlobTypeDeclarationSQL(array());
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     */
    public function testTruncateTableSQL()
    {
        $this->_platform->getTruncateTableSQL('foo');
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     */
    public function testReadLockSQL()
    {
        $this->_platform->getReadLockSQL();
    }

    public function testConvertBooleans()
    {
        $this->assertEquals($this->_platform->convertBooleans(false), 'false');
        $this->assertEquals($this->_platform->convertBooleans(true), 'true');

        $this->assertEquals($this->_platform->convertBooleans(0), 'false');
        $this->assertEquals($this->_platform->convertBooleans(1), 'true');

        $this->assertEquals($this->_platform->convertBooleans(array(true, 1, false, 0)),
            array('true', 'true', 'false', 'false'));
    }

    public function testSQLResultCasting()
    {
        $this->assertEquals($this->_platform->getSQLResultCasing("LoWeRcAsE"), 'lowercase');
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage No columns specified for table foo
     */
    public function testGenerateTableSqlWithoutColumns()
    {
        $table = new Table("foo");
        $this->assertEquals($this->_platform->getCreateTableSQL($table)[0],
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
        $this->assertEquals($this->_platform->getCreateTableSQL($table)[0],
            'CREATE TABLE foo (col_bool BOOLEAN, col_int INTEGER, col_float DOUBLE, col_timestamp TIMESTAMP, col_datetimetz TIMESTAMP, col_datetime TIMESTAMP, col_date TIMESTAMP, col_time TIMESTAMP, col_array ARRAY ( STRING ), col_object OBJECT ( dynamic ))');
    }

    public function testGeneratesTableAlterationSql()
    {
        $expectedSql = $this->getGenerateAlterTableSql();

        $tableDiff = new TableDiff('mytable');
        $tableDiff->addedColumns['quota'] = new \Doctrine\DBAL\Schema\Column('quota', \Doctrine\DBAL\Types\Type::getType('integer'), array('notnull' => false));

        $sql = $this->_platform->getAlterTableSQL($tableDiff);

        $this->assertEquals($expectedSql, $sql);
    }

    public function testGetAlterTableSqlDispatchEvent()
    {
        $events = array(
            'onSchemaAlterTableAddColumn'
        );

        $listenerMock = $this->getMock('GetAlterTableSqlDispatchEvenListener', $events);
        $listenerMock
            ->expects($this->once())
            ->method('onSchemaAlterTableAddColumn');

        $eventManager = new EventManager();
        $events = array(
            Events::onSchemaAlterTableAddColumn,
        );
        $eventManager->addEventListener($events, $listenerMock);

        $this->_platform->setEventManager($eventManager);

        $tableDiff = new TableDiff('mytable');
        $tableDiff->addedColumns['added'] = new \Doctrine\DBAL\Schema\Column('added', \Doctrine\DBAL\Types\Type::getType('integer'), array());

        $this->_platform->getAlterTableSQL($tableDiff);
    }

    public function testGenerateTableWithMultiColumnUniqueIndex()
    {
        $this->markTestSkipped("Custom index creation currently not supported");

        $table = new Table('test');
        $table->addColumn('foo', 'string', array('notnull' => false, 'length' => 255));
        $table->addColumn('bar', 'string', array('notnull' => false, 'length' => 255));
        $table->addUniqueIndex(array("foo", "bar"));

        $sql = $this->_platform->getCreateTableSQL($table);
        $this->assertEquals($this->getGenerateTableWithMultiColumnUniqueIndexSql(), $sql);
    }

    /**
     * @param Column $column
     */
    private function getSQLDeclaration($column)
    {
        $p = $this->_platform;
        return $p->getColumnDeclarationSQL($column->getName(), $p->prepareColumnData($column));
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
        $this->assertEquals($this->getSQLDeclaration($column), 'obj OBJECT ( strict ) AS ( num INTEGER, text STRING, arr ARRAY ( STRING ), obj OBJECT ( dynamic ) )');

    }

    public function testGenerateArraySQLDeclaration()
    {
        $column = new Column('arr', Type::getType(ArrayType::NAME));
        $this->assertEquals($this->getSQLDeclaration($column), 'arr ARRAY ( STRING )');

        $column = new Column('arr', Type::getType(ArrayType::NAME),
            array('platformOptions'=> array('type'=>Type::INTEGER)));
        $this->assertEquals($this->getSQLDeclaration($column), 'arr ARRAY ( INTEGER )');

    }

    public function testPlatformSupport() {
        $this->assertFalse($this->_platform->supportsSequences());
        $this->assertTrue($this->_platform->supportsSchemas());
        $this->assertTrue($this->_platform->supportsIdentityColumns());
        $this->assertFalse($this->_platform->supportsIndexes());
        $this->assertFalse($this->_platform->supportsCommentOnStatement());
        $this->assertFalse($this->_platform->supportsForeignKeyConstraints());
        $this->assertFalse($this->_platform->supportsForeignKeyOnUpdate());
        $this->assertFalse($this->_platform->supportsViews());
        $this->assertFalse($this->_platform->prefersSequences());
    }

}
