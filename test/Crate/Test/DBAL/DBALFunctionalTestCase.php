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
namespace Crate\Test\DBAL;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\DebugStack;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Throwable;
use Crate\DBAL\Driver\PDOCrate\Driver;

abstract class DBALFunctionalTestCase extends TestCase
{
    /**
     * Shared connection when a TestCase is run alone (outside of it's functional suite)
     *
     * @var \Doctrine\DBAL\Connection
     */
    private static $_sharedConn;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $_conn;

//    /**
//     * @var \Doctrine\DBAL\Logging\DebugStack
//     */
//    protected $_sqlLoggerStack;

    protected function resetSharedConn()
    {
        if (self::$_sharedConn) {
            self::$_sharedConn->close();
            self::$_sharedConn = null;
        }
    }

    public function setUp() : void
    {
        if ( ! isset(self::$_sharedConn)) {
            $params = array(
                'driverClass' => Driver::class,
                'host' => 'localhost',
                'port' => 4200
            );
            self::$_sharedConn = DriverManager::getConnection($params);
        }
        $this->_conn = self::$_sharedConn;

//        $this->_sqlLoggerStack = new DebugStack();

//        $this->_conn->getConfiguration()->setMiddlewares();
    }

//    protected function onNotSuccessfulTest(Throwable $e) : void
//    {
//        if ($e instanceof AssertionFailedError) {
//            throw $e;
//        }
//
//        if(isset($this->_sqlLoggerStack->queries) && count($this->_sqlLoggerStack->queries)) {
//            $queries = "";
//            $i = count($this->_sqlLoggerStack->queries);
//            foreach (array_reverse($this->_sqlLoggerStack->queries) AS $query) {
//                $params = array_map(function($p) { if (is_object($p)) return get_class($p); else return "'".print_r($p, true)."'"; }, $query['params'] ?: array());
//                $queries .= ($i+1).". SQL: '".$query['sql']."' Params: ".implode(", ", $params).PHP_EOL;
//                $i--;
//            }
//
//            $trace = $e->getTrace();
//            $traceMsg = "";
//            foreach($trace AS $part) {
//                if(isset($part['file'])) {
//                    if(strpos($part['file'], "PHPUnit/") !== false) {
//                        // Beginning with PHPUnit files we don't print the trace anymore.
//                        break;
//                    }
//
//                    $traceMsg .= $part['file'].":".$part['line'].PHP_EOL;
//                }
//            }
//
//            $message = "[".get_class($e)."] ".$e->getMessage().PHP_EOL.PHP_EOL."With queries:".PHP_EOL.$queries.PHP_EOL."Trace:".PHP_EOL.$traceMsg;
//
//            throw new \Exception($message, (int)$e->getCode(), $e);
//        }
//        throw $e;
//    }

    public function execute($stmt)
    {
        return $this->_conn->executeQuery($stmt);
    }

    public function refresh($table_name)
    {
        $this->_conn->query('REFRESH TABLE ' . $table_name);
    }

    public function prepareStatement($sql)
    {
        return $this->_conn->prepare($sql);
    }
}
