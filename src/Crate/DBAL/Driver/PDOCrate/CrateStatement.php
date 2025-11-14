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

use Crate\PDO\PDOInterface;
use Crate\PDO\PDOStatement;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\PDOStatementImplementations;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\Deprecations\Deprecation;
use PDO;

/**
 * @internal
 */
final class CrateStatement implements StatementInterface
{
    private PDOInterface $pdo;
    private PDOStatement $stmt;

    /**
     * @param string              $sql
     * @param array<string,mixed> $options
     */
    public function __construct(PDOInterface $pdo, $sql, $options = [])
    {
        $this->pdo  = $pdo;
        $this->stmt = $pdo->prepare($sql, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function execute($params = null): ResultInterface
    {

        if ($params !== null) {
            Deprecation::trigger(
                'doctrine/dbal',
                'https://github.com/doctrine/dbal/pull/5556',
                'Passing $params to Statement::execute() is deprecated. Bind parameters using'
                . ' Statement::bindParam() or Statement::bindValue() instead.',
            );
        }
        $this->stmt->execute($params);
        return new Result($this);
    }

    /**
     * {@inheritDoc}
     */
    public function columnCount(): int
    {
        return $this->stmt->columnCount();
    }

    /**
     * {@inheritDoc}
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function bindValue($param, $value, $type = ParameterType::STRING)
    {
        return $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function bindParam($param, &$variable, $type = ParameterType::STRING, $length = null)
    {
        return $this->stmt->bindParam($param, $variable, $type, $length);
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(
        $fetch_style = PDO::FETCH_ASSOC,
        $cursor_orientation = PDO::FETCH_ORI_NEXT,
        $cursor_offset = 0,
    ) {
        return $this->stmt->fetch($fetch_style, $cursor_orientation, $cursor_offset);
    }

    /**
     * @phpstan-param PDO::FETCH_* $mode
     *
     * @return list<mixed>
     *
     * @throws Exception
     */
    public function fetchAll(int $mode): array
    {
        return $this->stmt->fetchAll($mode);
    }

    public function fetchColumn($column_number = 0)
    {
        return $this->stmt->fetchColumn($column_number);
    }

    /**
     * {@inheritDoc}
     */
    public function closeCursor(): bool
    {
        return $this->stmt->closeCursor();
    }

    /**
     * Gets the wrapped CrateDB PDOStatement.
     *
     * @return PDOStatement
     */
    public function getWrappedStatement(): PDOStatement
    {
        return $this->stmt;
    }
}
