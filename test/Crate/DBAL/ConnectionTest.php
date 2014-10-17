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
namespace Crate\DBAL;

class ConnectionTest extends \Doctrine\Tests\DbalTestCase {

    protected $_conn = null;

    public function setUp()
    {
        $params = array(
            'driverClass' => 'Crate\DBAL\Driver\PDOCrate\Driver',
            'host' => 'localhost',
            'port' => '4200'
        );
        $this->_conn = \Doctrine\DBAL\DriverManager::getConnection($params);
    }

    public function testGetDriver()
    {
        $this->assertInstanceOf('Crate\DBAL\Driver\PDOCrate\Driver', $this->_conn->getDriver());
    }

    public function testConnect() {
        $this->assertTrue($this->_conn->connect());

        $stmt = $this->_conn->query('select * from sys.cluster');
        $this->assertEquals(1, $stmt->rowCount());

        $row = $stmt->fetch();
        $this->assertEquals('crate', $row['name']);
    }

} 