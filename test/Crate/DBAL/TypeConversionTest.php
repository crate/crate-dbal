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

use Crate\DBAL\Types\TimestampType;
use Doctrine\DBAL\Types\Type;

class TypeConversionTest extends AbstractCrateIntegrationTest {

    private $platform;

    public function setUp()
    {
        AbstractCrateIntegrationTest::setUp();
        $this->platform = $this->_conn->getDatabasePlatform();
    }

    public function testTimestampType()
    {
        $type = Type::getType(TimestampType::NAME);

        $ts = 1413905018;
        $input = new \DateTime();
        $input->setTimestamp($ts); // "2014-10-21 15:23:38"

        // to DB value
        $output = $type->convertToDatabaseValue($input, $this->platform);
        $this->assertEquals($output, $ts*1000);

        // to PHP value
        $inputRestored = $type->convertToPHPValue($output, $this->platform);
        $this->assertEquals($inputRestored, $input);

        $inputRestored = $type->convertToPHPValue($input, $this->platform);
        $this->assertEquals($inputRestored, $input);
    }

    public function testTimestampTypeNull()
    {
        $type = Type::getType(TimestampType::NAME);

        // to DB value
        $value = $type->convertToDatabaseValue(null, $this->platform);
        $this->assertEquals($value, null);

        // to PHP value
        $value = $type->convertToPHPValue(null, $this->platform);
        $this->assertEquals($value, null);
    }

    public function testTimestampTypeInvalid()
    {
        $type = Type::getType(TimestampType::NAME);

        // to DB value
        $value = $type->convertToDatabaseValue("invalid", $this->platform);
        $this->assertEquals($value, null);

        // to PHP value
        $value = $type->convertToPHPValue("invalid", $this->platform);
        $this->assertEquals($value, null);
    }

    public function testArrayType()
    {
        $type = Type::getType(Type::SIMPLE_ARRAY);

    }

}
