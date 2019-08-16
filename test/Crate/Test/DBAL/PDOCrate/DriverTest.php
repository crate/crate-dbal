<?php /** @noinspection PhpUnhandledExceptionInspection */

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

namespace Crate\Test\DBAL\PDOCrate;


use Crate\DBAL\Driver\PDOCrate\Driver;
use Crate\DBAL\Platforms\CratePlatform;
use Crate\DBAL\Platforms\CratePlatform1;
use Crate\DBAL\Platforms\CratePlatform4;
use PHPUnit\Framework\TestCase;

class DriverTest extends TestCase
{
    public function testCreatePlatformForVersionLess_0_57()
    {
        $driver = new Driver();
        $this->assertInstanceOf(CratePlatform::class, $driver->createDatabasePlatformForVersion("0.56.6"));

    }

    public function testCreatePlatformForVersionLess_4()
    {
        $driver = new Driver();
        $this->assertInstanceOf(CratePlatform1::class, $driver->createDatabasePlatformForVersion("3.2"));

    }

    public function testCreatePlatformForVersionGreaterEquals_4()
    {
        $driver = new Driver();
        $this->assertInstanceOf(CratePlatform4::class, $driver->createDatabasePlatformForVersion("4.0.0"));
    }
}