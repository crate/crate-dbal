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

use Crate\PDO\PDOCrateDB;
use Crate\Test\DBAL\DBALFunctionalTestCase;
use Doctrine\DBAL\Connection;
use Crate\DBAL\Driver\PDOCrate\Driver;
use Doctrine\DBAL\Statement;
use Crate\PDO\PDOStatement;

class ConnectionTest extends DBALFunctionalTestCase
{
    public function setUp() : void
    {
        $this->resetSharedConn();
        parent::setUp();
    }

    public function tearDown() : void
    {
        parent::tearDown();
        $this->resetSharedConn();
    }

    public function testBasicAuthConnection()
    {
        $auth = ['crate', 'secret'];
        $params = array(
            'driverClass' => 'Crate\DBAL\Driver\PDOCrate\Driver',
            'host' => 'localhost',
            'port' => 4200,
            'user' => $auth[0],
            'password' => $auth[1],
        );
        $conn = \Doctrine\DBAL\DriverManager::getConnection($params);
        $this->assertEquals($auth[0], $conn->getParams()['user']);
        $this->assertEquals($auth[1], $conn->getParams()['password']);
        $auth_attr = $conn->getNativeConnection()->getAttribute(PDOCrateDB::CRATE_ATTR_HTTP_BASIC_AUTH);
        $this->assertEquals($auth, $auth_attr);
    }

    public function testGetConnection()
    {
      $this->assertInstanceOf(Connection::class, $this->_conn);
    }

    public function testGetDriver()
    {
        $this->assertInstanceOf(Driver::class, $this->_conn->getDriver());
    }

    public function testStatement()
    {
        $sql = 'SELECT * FROM sys.cluster';
        $stmt = $this->_conn->prepare($sql);
        $this->assertInstanceOf(Statement::class, $stmt);
        $this->assertInstanceOf(\Doctrine\DBAL\Driver\Statement::class, $stmt->getWrappedStatement());
    }

    public function testConnect()
    {
        $stmt = $this->_conn->executeQuery('select * from sys.cluster');
        $this->assertEquals(1, $stmt->rowCount());

        $row = $stmt->fetchAssociative();
        $this->assertEquals('crate', $row['name']);
    }

}

