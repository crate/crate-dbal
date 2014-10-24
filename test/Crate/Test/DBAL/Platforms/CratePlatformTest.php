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

    protected function getQuotedColumnInPrimaryKeySQL()
    {
        return array(
            'CREATE TABLE "quoted" ("key" STRING, PRIMARY KEY("key"))',
        );
    }

    protected function getQuotedColumnInIndexSQL()
    {
        return array(
            'CREATE TABLE "quoted" ("key" STRING,' .
            'INDEX IDX_22660D028A90ABA9 USING FULLTEXT ("key")' .
            ')'
        );
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

}