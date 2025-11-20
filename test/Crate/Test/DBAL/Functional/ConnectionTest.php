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
use Crate\Test\DBAL\DBALFunctionalTest;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DriverManager;
use Doctrine\Deprecations\PHPUnit\VerifyDeprecations;

class ConnectionTest extends DBALFunctionalTest
{
    use VerifyDeprecations;

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
        $conn = DriverManager::getConnection($params);
        $conn->connect();
        $credentials = $conn->getNativeConnection()->getAttribute(PDOCrateDB::CRATE_ATTR_HTTP_BASIC_AUTH);

        $this->assertEquals(array("crate", "secret"), $credentials);
    }

    public function testGetConnection()
    {
      $this->assertInstanceOf('Doctrine\DBAL\Connection', $this->_conn);
      $this->assertInstanceOf('Crate\DBAL\Driver\PDOCrate\PDOConnection', $this->_conn->getWrappedConnection());
    }

    public function testGetDriver()
    {
        $this->assertInstanceOf('Crate\DBAL\Driver\PDOCrate\Driver', $this->_conn->getDriver());
    }

    /**
     * @var \Doctrine\DBAL\Statement $stmt
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function testStatement()
    {
        $sql = 'SELECT * FROM sys.cluster';
        $stmt = $this->_conn->prepare($sql);

        // Well, it's three layers of Statement objects now.
        $this->assertInstanceOf('Doctrine\DBAL\Statement', $stmt);
        $this->assertInstanceOf('Crate\DBAL\Driver\PDOCrate\CrateStatement', $stmt->getWrappedStatement());
        $this->assertInstanceOf('Crate\PDO\PDOStatement', $stmt->getWrappedStatement()->getWrappedStatement());

    }

    public function testConnect()
    {
        $this->assertTrue($this->_conn->connect());

        $stmt = $this->_conn->query('select * from sys.cluster');
        $this->assertEquals(1, $stmt->rowCount());

        $row = $stmt->fetch();
        $this->assertEquals('crate', $row['name']);
    }

    public function testBeginTransaction()
    {
        $this->expectDeprecationWithIdentifier('https://github.com/crate/crate-dbal/issues/155');
        $this->_conn->beginTransaction();

        $this->expectNoDeprecationWithIdentifier('https://github.com/crate/crate-dbal/issues/155');
        $this->_conn->beginTransaction();
    }

    public function testCommit()
    {
        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('There is no active transaction.');
        $this->_conn->commit();
    }

    public function testRollback()
    {
        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('There is no active transaction.');
        $this->_conn->rollback();
    }

}
