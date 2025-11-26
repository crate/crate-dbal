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


use Crate\Test\DBAL\DBALFunctionalTest;
use Doctrine\DBAL\Schema\Table;
use InvalidArgumentException;

class TableOptionsTest extends DBALFunctionalTest {

    public function tearDown() : void
    {
        parent::tearDown();
        if ($this->_conn->createSchemaManager()->tablesExist(["table_option_test"])) {
            try {
                $sm = $this->_conn->createSchemaManager();
                $sm->dropTable('table_option_test');
            } catch(\Exception $e) {
                $this->fail($e->getMessage());
            }
        }
    }

    public function testAdditionalTableOptions()
    {
        $platform = $this->_conn->getDatabasePlatform();

        $options = [];
        $options['sharding_routing_column'] = 'id';
        $options['sharding_num_shards'] = 6;
        $options['partition_columns'] = ['parted', 'date'];
        $options['table_options'] = [];
        $options['table_options']['number_of_replicas'] = '0-2';
        $options['table_options']['write.wait_for_active_shards'] = 'ALL';

        $table = new Table('t1', [], [], [], [], $options);
        $table->addColumn('id', 'integer');
        $table->addColumn('parted', 'integer');
        $table->addColumn('date', 'timestamp');

        $sql = $platform->getCreateTableSQL($table);
        $this->assertEquals(array(
                'CREATE TABLE t1 (id INTEGER, parted INTEGER, date TIMESTAMP)'
                . ' CLUSTERED BY (id) INTO 6 SHARDS'
                . ' PARTITIONED BY (parted, date)'
                . ' WITH ("number_of_replicas" = \'0-2\', "write.wait_for_active_shards" = \'ALL\')')
                , $sql);
    }

    public function testGetAdditionalTableOptions()
    {
        $options = [];
        $options['sharding_routing_column'] = 'id';
        $options['sharding_num_shards'] = 6;
        $options['partition_columns'] = ['parted', 'date'];
        $options['table_options'] = [];
        $options['table_options']['number_of_replicas'] = '0-2';
        $options['table_options']['write.wait_for_active_shards'] = 'ALL';

        $table = new Table('table_option_test', [], [], [], [], $options);
        $table->addColumn('id', 'integer');
        $table->addColumn('parted', 'integer');
        $table->addColumn('date', 'timestamp');

        $sm = $this->_conn->createSchemaManager();
        $sm->createTable($table);

        $schema = $sm->createSchema();

        $retrievedTable = $schema->getTable($table->getName());
        $options = $retrievedTable->getOptions();

        $this->assertEquals($options['sharding_routing_column'], 'id');
        $this->assertEquals($options['sharding_num_shards'], 6);
        $this->assertEquals($options['partition_columns'], ['parted', 'date']);
        $this->assertEquals($options['table_options']['number_of_replicas'], '0-2');
        $this->assertEquals($options['table_options']['write.wait_for_active_shards'], 'ALL');
    }

    public function testPartitionColumnsNotArray()
    {
        $platform = $this->_conn->getDatabasePlatform();

        $options = [];
        $options['partition_columns'] = 'parted';
        $table = new Table('t1', [], [], [], [], $options);
        $table->addColumn('parted', 'integer');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Expecting array type at 'partition_columns'");
        $platform->getCreateTableSQL($table);
    }
}
