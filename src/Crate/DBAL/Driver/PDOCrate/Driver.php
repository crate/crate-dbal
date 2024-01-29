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

use Crate\DBAL\Platforms\CratePlatform1;
use Crate\DBAL\Platforms\CratePlatform;
use Crate\DBAL\Platforms\CratePlatform4;
use Crate\DBAL\Schema\CrateSchemaManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\VersionAwarePlatformDriver;

class Driver implements \Doctrine\DBAL\Driver, VersionAwarePlatformDriver
{
    const VERSION = '3.0.1';
    const NAME = 'crate';

    private const VERSION_057 = '0.57.0';
    private const VERSION_4 = '4.0.0';

    /**
     * {@inheritDoc}
     * @return PDOConnection The database connection.
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        return new PDOConnection($this->constructPdoDsn($params), $username, $password, $driverOptions);
    }

    /**
     * Constructs the Crate PDO DSN.
     *
     * @return string The DSN.
     */
    private function constructPdoDsn(array $params)
    {
        $dsn = self::NAME . ':';
        if (isset($params['host']) && $params['host'] != '') {
            $dsn .= $params['host'];
        } else {
            $dsn .= 'localhost';
        }
        $dsn .= ':' . (isset($params['port']) ? $params['port'] : '4200');

        return $dsn;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatabasePlatform()
    {
        return new CratePlatform();
    }

    /**
     * {@inheritDoc}
     */
    public function getSchemaManager(Connection $conn, AbstractPlatform $platform)
    {
        // TODO: `$platform` added when upgrading to Doctrine3 - what to do with it?
        return new CrateSchemaManager($conn);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatabase(Connection $conn)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function createDatabasePlatformForVersion($version)
    {
        if (version_compare($version, self::VERSION_057, "<")) {
            return new CratePlatform();
        } elseif (version_compare($version, self::VERSION_4, "<")) {
            return new CratePlatform1();
        } else {
            return new CratePlatform4();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getExceptionConverter(): ExceptionConverter
    {
        // TODO: Implement getExceptionConverter() method.
        //       Added when upgrading to Doctrine3.
    }
}
