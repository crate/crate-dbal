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
namespace Crate\Test\DBAL\Functional\Schema;

use Crate\Test\DBAL\DBALFunctionalTestCase;
use Doctrine\DBAL\Schema\Table;

class SchemaManagerTest extends DBALFunctionalTestCase
{
    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected $_sm;

    public function setUp()
    {
        parent::setUp();
        $this->_sm = $this->_conn->getSchemaManager();
    }

    public function tearDown()
    {
        foreach ($this->_sm->listTableNames() as $tableName)
        $this->_sm->dropTable($tableName);
    }

    public function testListTables()
    {
        $this->createTestTable('list_tables_test');
        $tables = $this->_sm->listTables();

        $this->assertInternalType('array', $tables);
        $this->assertTrue(count($tables) > 0, "List Tables has to find at least one table named 'list_tables_test'.");

        $foundTable = false;
        foreach ($tables AS $table) {
            $this->assertInstanceOf('Doctrine\DBAL\Schema\Table', $table);
            if (strtolower($table->getName()) == 'list_tables_test') {
                $foundTable = true;

                $this->assertTrue($table->hasColumn('id'));
                $this->assertTrue($table->hasColumn('test'));
                $this->assertTrue($table->hasColumn('foreign_key_test'));
            }
        }

        $this->assertTrue( $foundTable , "The 'list_tables_test' table has to be found.");
    }

    public function createListTableColumns()
    {
        $table = new Table('list_table_columns');
        $table->addColumn('id', 'integer');
        $table->addColumn('text', 'string');
        $table->addColumn('ts', 'timestamp');
        $table->addColumn('num_float', 'float');
        $table->setPrimaryKey(array('id'));
        // todo: MapType
        // todo: ArrayType
        return $table;
    }

    public function testListTableColumns()
    {
        $table = $this->createListTableColumns();

        $this->_sm->dropAndCreateTable($table);

        $columns = $this->_sm->listTableColumns('list_table_columns');

        $this->assertArrayHasKey('id', $columns);
        $this->assertEquals('id',   strtolower($columns['id']->getname()));
        $this->assertInstanceOf('Doctrine\DBAL\Types\IntegerType', $columns['id']->gettype());

        $this->assertArrayHasKey('text', $columns);
        $this->assertEquals('text', strtolower($columns['text']->getname()));
        $this->assertInstanceOf('Doctrine\DBAL\Types\StringType', $columns['text']->gettype());

        $this->assertEquals('ts',  strtolower($columns['ts']->getname()));
        $this->assertInstanceOf('Crate\DBAL\Types\TimestampType', $columns['ts']->gettype());

        $this->assertEquals('num_float', strtolower($columns['num_float']->getname()));
        $this->assertInstanceOf('Doctrine\DBAL\Types\FloatType', $columns['num_float']->gettype());
    }


    public function testCreateSchema()
    {
        $this->createTestTable('test_table');

        $schema = $this->_sm->createSchema();
        $this->assertTrue($schema->hasTable('test_table'));
    }

    /**
     * @param string $name
     * @param array $data
     */
    protected function createTestTable($name = 'test_table', $data = array())
    {
        $options = array();
        if (isset($data['options'])) {
            $options = $data['options'];
        }

        $table = $this->getTestTable($name, $options);
        $this->_sm->dropAndCreateTable($table);
    }

    protected function getTestTable($name, $options=array())
    {
        $table = new Table($name, array(), array(), array(), false, $options);
        $table->setSchemaConfig($this->_sm->createSchemaConfig());
        $table->addColumn('id', 'integer', array('notnull' => true));
        $table->setPrimaryKey(array('id'));
        $table->addColumn('test', 'string', array('length' => 255));
        $table->addColumn('foreign_key_test', 'integer');
        return $table;
    }

    protected function getTestCompositeTable($name)
    {
        $table = new Table($name, array(), array(), array(), false, array());
        $table->setSchemaConfig($this->_sm->createSchemaConfig());
        $table->addColumn('id', 'integer', array('notnull' => true));
        $table->addColumn('other_id', 'integer', array('notnull' => true));
        $table->setPrimaryKey(array('id', 'other_id'));
        $table->addColumn('test', 'string', array('length' => 255));
        $table->addColumn('test_other', 'string', array('length' => 255));
        return $table;
    }

    protected function assertHasTable($tables, $tableName)
    {
        $foundTable = false;
        foreach ($tables AS $table) {
            $this->assertInstanceOf('Doctrine\DBAL\Schema\Table', $table, 'No Table instance was found in tables array.');
            if (strtolower($table->getName()) == 'list_tables_test_new_name') {
                $foundTable = true;
            }
        }
        $this->assertTrue($foundTable, "Could not find new table");
    }

}
