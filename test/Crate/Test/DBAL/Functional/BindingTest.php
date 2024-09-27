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

use Crate\Test\DBAL\DBALFunctionalTestCase;
use Doctrine\DBAL\Result;

class BindingTest extends DBALFunctionalTestCase
{

    public function testBindPositionalParam()
    {
        $name = 'crate';

        $stmt = $this->prepareStatement('SELECT * FROM sys.cluster WHERE name = ?');
        $stmt->bindValue(1, $name);
        $stmt->executeStatement();

        $noName = 'i0ejfNlzSFCloGYtSzddTw';

        $stmt = $this->prepareStatement('SELECT * FROM sys.cluster WHERE name = ? OR master_node = ?');
        $stmt->bindValue(1, $name);
        $stmt->bindValue(2, $noName);
        $this->assertInstanceOf(Result::class, $stmt->executeQuery());
    }

    public function testBindPositionalValue()
    {
        $stmt = $this->prepareStatement('SELECT * FROM sys.cluster WHERE name = ?');
        $stmt->bindValue(1, 'crate');
        $stmt->executeStatement();

        $stmt = $this->prepareStatement('SELECT * FROM sys.cluster WHERE name = ? OR master_node = ?');
        $stmt->bindValue(1, 'crate');
        $stmt->bindValue(2, 'i0ejfNlzSFCloGYtSzddTw');
        $this->assertInstanceOf(Result::class, $stmt->executeQuery());
    }

    public function testBindNamedParam()
    {
        $name = 'crate';

        $stmt = $this->prepareStatement('SELECT * FROM sys.cluster WHERE name = :name');
        $stmt->bindValue('name', $name);
        $stmt->executeStatement();

        $noName = 'i0ejfNlzSFCloGYtSzddTw';

        $stmt = $this->prepareStatement('SELECT * FROM sys.cluster WHERE name = :name OR master_node = :master_node');
        $stmt->bindValue('name', $name);
        $stmt->bindValue('master_node', $noName);
        $this->assertInstanceOf(Result::class, $stmt->executeQuery());
    }

    public function testBindNamedValue()
    {
        $stmt = $this->prepareStatement('SELECT * FROM sys.cluster WHERE name = :name');
        $stmt->bindValue('name', 'crate');
        $stmt->executeStatement();

        $stmt = $this->prepareStatement('SELECT * FROM sys.cluster WHERE name = :name OR master_node = :master_node');
        $stmt->bindValue('name', 'crate');
        $stmt->bindValue('master_node', 'i0ejfNlzSFCloGYtSzddTw');
        $this->assertInstanceOf(Result::class, $stmt->executeQuery());
    }

    public function testBindTimestamp()
    {
        if ($this->_conn->createSchemaManager()->tablesExist(["foo"])) {
            $this->execute("DROP TABLE foo");
        }

        $this->execute("CREATE TABLE foo (id int, ts timestamp) with (number_of_replicas=0)");
        $this->execute("INSERT INTO foo (id, ts) VALUES (1, 1413901591000)");
        $this->execute("INSERT INTO foo (id, ts) VALUES (2, 1413901592000)");
        $this->execute("INSERT INTO foo (id, ts) VALUES (3, 1413901593000)");
        $this->execute("REFRESH TABLE foo");

        $date = new \DateTime("2014-10-21 14:26:32"); // => 1413901592000

        $stmt = $this->prepareStatement('SELECT * FROM foo WHERE ts > ?');
        $stmt->bindValue(1, $date, 'datetimetz');
        $result = $stmt->executeQuery();
        $row = $result->fetchAllAssociative();
        $this->assertEquals($row[0]['id'], 3);
        $this->assertEquals($row[0]['ts'], 1413901593000);

        $stmt = $this->prepareStatement('SELECT * FROM foo WHERE ts < ?');
        $stmt->bindValue(1, $date, 'datetime');
        $result = $stmt->executeQuery();
        $row = $result->fetchAllAssociative();
        $this->assertEquals($row[0]['id'], 1);
        $this->assertEquals($row[0]['ts'], 1413901591000);

        $this->execute("DROP TABLE foo");
    }
}
