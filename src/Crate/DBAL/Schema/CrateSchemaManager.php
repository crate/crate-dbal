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

class CrateSchemaManager extends AbstractSchemaManager
{
    /**
     * {@inheritdoc}
     *
     */
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
    protected function _getPortableTableColumnDefinition($tableColumn)
    {
        $tableColumn = array_change_key_case($tableColumn, CASE_LOWER);

        if (!isset($tableColumn['column_name'])) {
            $tableColumn['column_name'] = '';
        }
        if (!isset($tableColumn['is_nullable'])) {
            $tableColumn['is_nullable'] = true;
        }
        if (!isset($tableColumn['column_default'])) {
            $tableColumn['column_default'] = null;
        }

        $dbType = strtolower($tableColumn['data_type']);
        $type = $this->_platform->getDoctrineTypeMapping($dbType);

        $options = array(
            'length'        => null,
            'notnull'       => ! $tableColumn['is_nullable'],
            'default'       => $tableColumn['column_default'],
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
    protected function _getPortableTablesList($tables)
    {
        $tableNames = array();
        foreach ($tables as $tableRow) {
            $tableRow = array_change_key_case($tableRow, \CASE_LOWER);
            $tableNames[] = $tableRow['table_name']; // ignore schema for now
        }

        return $tableNames;
    }
}
