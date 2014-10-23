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

use Doctrine\DBAL\Types\Type,
    Doctrine\DBAL\Platforms\AbstractPlatform;


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
    const IGNORE = 'ignore';

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
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
        return $value;
    }

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @return string
     * @param array $fieldDeclaration The field declaration.
     * @param AbstractPlatform $platform The currently used database platform.
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getMapTypeDeclarationSQL($fieldDeclaration);
    }

}
