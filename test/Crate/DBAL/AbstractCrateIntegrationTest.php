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

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use Doctrine\Tests\DbalFunctionalTestCase;

abstract class AbstractCrateIntegrationTest extends DbalFunctionalTestCase {

    /**
     * Shared connection when a TestCase is run alone (outside of it's functional suite)
     *
     * @var \Doctrine\DBAL\Connection
     */
    private static $_sharedConn;

    protected $_conn = null;

    protected function resetSharedConn()
    {
        if (self::$_sharedConn) {
            self::$_sharedConn->close();
            self::$_sharedConn = null;
        }
    }

    public function setUp()
    {
        // TODO: register custom types correctly!!!
        try {
            Type::addType('timestamp', 'Crate\DBAL\Types\TimestampType');
        } catch(DBALException $ex) {}

        if ( ! isset(self::$_sharedConn)) {
            $params = array(
                'driverClass' => 'Crate\DBAL\Driver\PDOCrate\Driver',
                'host' => 'localhost',
                'port' => '4200'
            );
            self::$_sharedConn = \Doctrine\DBAL\DriverManager::getConnection($params);
        }
        $this->_conn = self::$_sharedConn;

        $this->_sqlLoggerStack = new \Doctrine\DBAL\Logging\DebugStack();
        $this->_conn->getConfiguration()->setSQLLogger($this->_sqlLoggerStack);
    }

    public function execute($stmt)
    {
        return $this->_conn->query($stmt);
    }

    public function refresh($table_name)
    {
        $this->_conn->query('REFRESH TABLE ' . $table_name);
    }

}
