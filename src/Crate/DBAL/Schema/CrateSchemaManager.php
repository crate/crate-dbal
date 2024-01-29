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
namespace Crate\DBAL\Schema;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Table;

class CrateSchemaManager extends AbstractSchemaManager
{
    /**
     * {@inheritdoc}
     *
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    protected function _getPortableTableIndexesList($tableIndexes, $tableName = null)
    {
        $buffer = [];
        foreach ($tableIndexes as $row) {
            $isPrimary = $row['constraint_type'] == 'PRIMARY KEY';
            $buffer[] = [
                'key_name' => $row['constraint_name'],
                'column_name' => $row['column_name'],
                'non_unique' => ! $isPrimary,
                'primary' => $isPrimary,
                'where' => '',
            ];
        }

        return parent::_getPortableTableIndexesList($buffer, $tableName);
    }
    /**
     * {@inheritDoc}
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    protected function _getPortableTableColumnDefinition($tableColumn)
    {
        $tableColumn = array_change_key_case($tableColumn, CASE_LOWER);

        if (!isset($tableColumn['column_name'])) {
            $tableColumn['column_name'] = '';
        }
        if (!isset($tableColumn['is_nullable'])) {
            $tableColumn['is_nullable'] = true;
        }

        $dbType = strtolower($tableColumn['data_type']);
        $type = $this->_platform->getDoctrineTypeMapping($dbType);

        $options = array(
            'length'        => null,
            'notnull'       => ! $tableColumn['is_nullable'],
            'default'       => null,
            'precision'     => null,
            'scale'         => null,
            'fixed'         => null,
            'unsigned'      => false,
            'autoincrement' => false,
            'comment'       => '',
        );

        return new Column($tableColumn['column_name'], Type::getType($type), $options);
    }

    /**
     * {@inheritDoc}
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    protected function _getPortableTablesList($tables)
    {
        $tableNames = array();
        foreach ($tables as $tableRow) {
            $tableRow = array_change_key_case($tableRow, \CASE_LOWER);
            $tableNames[] = $tableRow['table_name']; // ignore schema for now
        }

        return $tableNames;
    }

    /**
     * Flattens a multidimensional array into a 1 dimensional array, where
     * keys are concatinated with '.'
     *
     * @return array
     */
    private function flatten(array $array, string $prefix = '') : array
    {
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + self::flatten($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function listTableDetails($tableName) : Table
    {
        $columns = $this->listTableColumns($tableName);
        $indexes = $this->listTableIndexes($tableName);
        $options = [];

        $s = $this->_conn->fetchAssoc($this->_platform->getTableOptionsSQL($tableName));

        $options['sharding_routing_column'] = $s['clustered_by'];
        $options['sharding_num_shards'] = $s['number_of_shards'];
        $options['partition_columns'] = $s['partitioned_by'];
        $options['table_options'] = self::flatten($s['settings']);
        $options['table_options']['number_of_replicas'] = $s['number_of_replicas'];
        $options['table_options']['column_policy'] = $s['column_policy'];
        return new Table($tableName, $columns, $indexes, [], [], $options);
    }
}
