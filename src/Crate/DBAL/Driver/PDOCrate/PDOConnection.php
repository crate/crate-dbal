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

namespace Crate\DBAL\Driver\PDOCrate;

use Crate\PDO\PDOCrateDB;
use Crate\PDO\PDOStatement;
use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\ParameterType;
use PDO;
use PDOException;

class PDOConnection implements ConnectionInterface
{
    private PDOCrateDB $connection;

    /**
     * @param string $dsn
     * @param string $user
     * @param string $password
     * @param array|null $options
     */
    public function __construct($dsn, $user = null, $password = null, ?array $options = null)
    {
        $this->connection = new PDOCrateDB($dsn, $user, $password, $options);
        $this->connection->setAttribute(PDO::ATTR_STATEMENT_CLASS, [PDOStatement::class, []]);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getServerVersion(): string
    {
        try {
            return $this->connection->getServerVersion();
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function getNativeConnection(): PDOCrateDB
    {
        return $this->connection;
    }

    /**
     * {@inheritDoc}
     *
     * References:
     * - https://github.com/doctrine/dbal/issues/2025
     * - https://github.com/doctrine/dbal/pull/517
     * - https://github.com/doctrine/dbal/pull/373
     */
    public function prepare($sql, $options = []): CrateStatement
    {
        try {
            return new CrateStatement($this->connection, $sql, $options);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function exec($sql): int
    {
        try {
            $result = $this->connection->exec($sql);
            assert($result !== false);
            return $result;
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function query(string $sql): ResultInterface
    {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute();
            return new Result($stmt);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function quote($value, $type = ParameterType::STRING): string
    {
        try {
            return $this->connection->quote($value, $type);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function lastInsertId($name = null): string
    {
        try {
            return $this->connection->lastInsertId($name);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function beginTransaction()
    {
        try {
            return $this->connection->beginTransaction();
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function commit()
    {
        try {
            return $this->connection->commit();
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function rollBack()
    {
        try {
            return $this->connection->rollBack();
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }
}
