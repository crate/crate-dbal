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

namespace Crate\DBAL\Platforms;


use Crate\DBAL\Types\TimestampType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\PhpIntegerMappingType;

class CratePlatform4 extends CratePlatform1
{
    /**
     * {@inheritDoc}
     */
    protected function initializeDoctrineTypeMappings()
    {
        $this->doctrineTypeMapping = array(
                'integer'       => 'integer',
                'int'           => 'integer',
                'bool'          => 'boolean',
                'boolean'       => 'boolean',
                'timestamp'     => 'timestamp',

                'object'        => 'map',
                'array'         => 'array',

                // postgresql compatible type names, default for CrateDB >= 4.0
                'text'          => 'string',
                'char'          => 'string',
                'smallint'      => 'smallint',
                'bigint'        => 'bigint',
                'real'          => 'float',
                'double precision' => 'float',
                'timestamp without time zone' => 'timestamp',
                'timestamp with time zone' => 'timestamp',
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getBigIntTypeDeclarationSQL(array $field)
    {
        return 'BIGINT';
    }

    /**
     * {@inheritDoc}
     */
    public function getSmallIntTypeDeclarationSQL(array $field)
    {
        return 'SMALLINT';
    }

    /**
     * {@inheritDoc}
     */
    public function getFloatDeclarationSQL(array $field)
    {
        return 'DOUBLE PRECISION';
    }

    /**
     * {@inheritDoc}
     */
    protected function getVarcharTypeDeclarationSQLSnippet($length, $fixed)
    {
        return 'TEXT';
    }

    /**
     * {@inheritDoc}
     */
    public function getClobTypeDeclarationSQL(array $field)
    {
        return 'TEXT';
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultValueDeclarationSQL($field)
    {
        if (! isset($field['default'])) {
            return '';
        }

        $default = $field['default'];

        if (! isset($field['type'])) {
            return " DEFAULT '" . $default . "'";
        }

        $type = $field['type'];

        if ($type instanceof PhpIntegerMappingType) {
            return ' DEFAULT ' . $default;
        }

        if ($type instanceof TimestampType) {
            return ' DEFAULT ' . $default;
        }

        if ($type instanceof BooleanType) {
            return " DEFAULT '" . $this->convertBooleans($default) . "'";
        }

        return " DEFAULT '" . $default . "'";
    }
}