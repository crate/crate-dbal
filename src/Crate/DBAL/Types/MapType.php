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

namespace Crate\DBAL\Types;

use Crate\DBAL\Platforms\CratePlatform;
use Crate\PDO\PDOCrateDB;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Type that maps a PHP associative array (map) to an object SQL type.
 *
 * TODO: Add support for strict|dynamic|ignored object types
 *
 */
class MapType extends Type
{

    const NAME = 'map';
    const STRICT = 'strict';
    const DYNAMIC = 'dynamic';
    const IGNORED = 'ignored';

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Gets the (preferred) binding type for values of this type that
     * can be used when binding parameters to prepared statements.
     *
     * @return integer
     */
    public function getBindingType()
    {
        return PDOCrateDB::PARAM_OBJECT;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!is_array($value) || (count($value) > 0 && !(array_keys($value) !== range(0, count($value) - 1)))) {
            return null;
        }

        return $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value == null ?: (array) $value;
    }

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array $fieldDeclaration The field declaration.
     * @param AbstractPlatform $platform The currently used database platform.
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $options = !array_key_exists('platformOptions', $fieldDeclaration) ?
            array() : $fieldDeclaration['platformOptions'];

        return $this->getMapTypeDeclarationSQL($platform, $fieldDeclaration, $options);
    }

    /**
     * Gets the SQL snippet used to declare an OBJECT column type.
     *
     * @param array $field
     *
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMapTypeDeclarationSQL(AbstractPlatform $platform, array $field, array $options)
    {
        $type = array_key_exists('type', $options) ? $options['type'] : MapType::DYNAMIC;

        $fields = array_key_exists('fields', $options) ? $options['fields'] : array();
        $columns = array();
        foreach ($fields as $field) {
            $columns[$field->getQuotedName($platform)] = CratePlatform::prepareColumnData($platform, $field);
        }
        $objectFields = $platform->getColumnDeclarationListSQL($columns);

        $declaration = count($columns) > 0 ? ' AS ( ' . $objectFields . ' )' : '';
        return 'OBJECT ( ' . $type . ' )' . $declaration ;
    }
}
