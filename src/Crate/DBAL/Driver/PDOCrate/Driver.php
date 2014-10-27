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

use Crate\PDO\PDO;

class Driver implements \Doctrine\DBAL\Driver
{
    const VERSION = '0.0.1';
    const NAME = 'crate';

    /**
     * {@inheritDoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        $pdo = new PDO($this->_constructPdoDsn($params), $username, $password, $driverOptions);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    /**
     * Constructs the Crate PDO DSN.
     *
     * @return string  The DSN.
     */
    private function _constructPdoDsn(array $params)
    {
        $dsn = '';
        if (isset($params['host']) && $params['host'] != '') {
            $dsn .= 'http://' . $params['host'];
        } else {
            $dsn .= "http://localhost";
        }
        $dsn .= ':' . (isset($params['port']) ? $params['port'] : '4200');
        $dsn .= '/_sql';

        return $dsn;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatabasePlatform()
    {
        return new \Crate\DBAL\Platforms\CratePlatform();
    }

    /**
     * {@inheritDoc}
     */
    public function getSchemaManager(\Doctrine\DBAL\Connection $conn)
    {
        return new \Crate\DBAL\Schema\CrateSchemaManager($conn);
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
    public function getDatabase(\Doctrine\DBAL\Connection $conn)
    {
        return null;
    }

}
