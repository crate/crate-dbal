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

namespace Crate\Test\DBAL\Functional\Types;


use Crate\DBAL\Platforms\CratePlatform;
use Crate\DBAL\Types\MapType;
use Crate\Test\DBAL\DBALFunctionalTestCase;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

class MapTypeTest extends DBALFunctionalTestCase {

    public function testStrictMapTableCreationWithSchemaManager() {
        $platform = $this->_conn->getDatabasePlatform();

        $table = new Table('items');
        $objDefinition = array(
            'type' => MapType::STRICT,
            'fields' => array(
                new Column('id',  Type::getType(Types::INTEGER), array()),
                new Column('name',  Type::getType(Types::STRING), array()),
            ),
        );
        $table->addColumn(
            'object_column', MapType::NAME,
            array('platformOptions' => $objDefinition)
        );

        $createFlags = CratePlatform::CREATE_INDEXES|CratePlatform::CREATE_FOREIGNKEYS;
        $sql = $platform->getCreateTableSQL($table, $createFlags);
        $this->assertEquals(array('CREATE TABLE items (object_column OBJECT ( strict ) AS ( id INTEGER, name TEXT ))'), $sql);
    }

} 