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

use Crate\DBAL\Types\MapType;
use Crate\DBAL\Types\TimestampType;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Event\SchemaCreateTableColumnEventArgs;
use Doctrine\DBAL\Event\SchemaCreateTableEventArgs;
use Doctrine\DBAL\Events;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Types\Type;

class CratePlatform extends AbstractPlatform
{

    const TIMESTAMP_FORMAT =  'Y-m-d\TH:i:s';
    const TIMESTAMP_FORMAT_TZ =  'Y-m-d\TH:i:sO';

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();
        // todo: register and override new types
        $this->initializeDoctrineTypeMappings();
        if (!Type::hasType(MapType::NAME)) {
            Type::addType(MapType::NAME, 'Crate\DBAL\Types\MapType');
        }
        if (!Type::hasType(TimestampType::NAME)) {
            Type::addType(TimestampType::NAME, 'Crate\DBAL\Types\TimestampType');
        }
        Type::overrideType('array', 'Crate\DBAL\Types\ArrayType');
    }

    /**
     * {@inheritDoc}
     */
    public function getSubstringExpression($value, $from, $length = null)
    {
        if ($length === null) {
            return 'SUBSTR(' . $value . ', ' . $from . ')';
        }

        return 'SUBSTR(' . $value . ', ' . $from . ', ' . $length . ')';
    }

    /**
     * {@inheritDoc}
     */
    public function getNowExpression()
    {
        throw DBALException::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function getRegexpExpression()
    {
        return 'LIKE';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateDiffExpression($date1, $date2)
    {
        return $date1 . ' - ' . $date2;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsSequences()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsSchemas()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsIdentityColumns()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsIndexes()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsCommentOnStatement()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsForeignKeyConstraints()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsForeignKeyOnUpdate()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsViews()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function prefersSequences()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getListDatabasesSQL()
    {
        return 'SELECT table_name FROM information_schema.tables';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTablesSQL()
    {
        return "SELECT table_name, schema_name FROM information_schema.tables WHERE schema_name = 'doc' OR schema_name = 'blob'";
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableColumnsSQL($table, $database = null)
    {
        $t = explode('.', $table);
        if (count($t) == 1) {
            array_unshift($t, 'doc');
        }
        // todo: make safe
        return "SELECT * from information_schema.columns WHERE table_name = '$t[1]' AND schema_name = '$t[0]'";
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableConstraintsSQL($table, $database = null)
    {
        $t = explode('.', $table);
        if (count($t) == 1) {
            array_unshift($t, 'doc');
        }
        // todo: make safe
        return "SELECT constraint_name, constraint_type from information_schema.table_constraints WHERE table_name = '$t[1]' AND schema_name = '$t[0]' AND constraint_type = 'PRIMARY_KEY'";
    }

    /**
     * {@inheritDoc}
     */
    public function getAlterTableSQL(TableDiff $diff)
    {
        $sql = array();
        $commentsSQL = array();
        $columnSql = array();

        foreach ($diff->addedColumns as $column) {
            if ($this->onSchemaAlterTableAddColumn($column, $diff, $columnSql)) {
                continue;
            }

            $query = 'ADD ' . $this->getColumnDeclarationSQL($column->getQuotedName($this), $column->toArray());
            $sql[] = 'ALTER TABLE ' . $diff->name . ' ' . $query;
            if ($comment = $this->getColumnComment($column)) {
                $commentsSQL[] = $this->getCommentOnColumnSQL($diff->name, $column->getName(), $comment);
            }
        }

        if (count($diff->removedColumns) > 0) {
            throw DBALException::notSupported("Alter Table: drop columns");
        }
        if (count($diff->changedColumns) > 0) {
            throw DBALException::notSupported("Alter Table: change column options");
        }
        if (count($diff->renamedColumns) > 0) {
            throw DBALException::notSupported("Alter Table: rename columns");
        }

        $tableSql = array();

        if ( ! $this->onSchemaAlterTable($diff, $tableSql)) {
            if ($diff->newName !== false) {
                throw DBALException::notSupported("Alter Table: rename table");
            }

            $sql = array_merge($sql, $this->_getAlterTableIndexForeignKeySQL($diff), $commentsSQL);
        }

        return array_merge($sql, $tableSql, $columnSql);
    }

    /**
     * {@inheritDoc}
     */
    protected function _getCreateTableSQL($tableName, array $columns, array $options = array())
    {
        $queryFields = $this->getColumnDeclarationListSQL($columns);

        if (isset($options['primary']) && ! empty($options['primary'])) {
            $keyColumns = array_unique(array_values($options['primary']));
            $queryFields .= ', PRIMARY KEY(' . implode(', ', $keyColumns) . ')';
        }


        if (isset($options['indexes']) && ! empty($options['indexes'])) {
            foreach ($options['indexes'] as $index) {
                $queryFields .= ', ' . $this->getIndexColumnDeclarationSQL($index);
            }
        }

        if (isset($options['foreignKeys'])) {
            throw DBALException::notSupported("Create Table: foreign keys");
        }

        $query = 'CREATE TABLE ' . $tableName . ' (' . $queryFields . ')';

        $sql[] = $query;
        return $sql;
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnDeclarationSQL($name, array $field)
    {
        if (isset($field['columnDefinition'])) {
            $columnDef = $this->getCustomTypeDeclarationSQL($field);
        } else {
            $typeDecl = $field['type']->getSqlDeclaration($field, $this);
            $columnDef = $typeDecl;
        }

        return $name . ' ' . $columnDef;
    }

    /**
     * Generate table index column declaration
     */
    public function getIndexColumnDeclarationSQL(Index $index)
    {
        $name = $index->getQuotedName($this);
        $columns = $index->getColumns();

        if (count($columns) == 0) {
            throw new \InvalidArgumentException("Incomplete definition. 'columns' required.");
        }

        return 'INDEX ' . $name . ' USING FULLTEXT ('. $this->getIndexFieldDeclarationListSQL($columns) . ')';
    }

    /**
     * {@inheritDoc}
     *
     * Crate wants boolean values converted to the strings 'true'/'false'.
     */
    public function convertBooleans($item)
    {
        if (is_array($item)) {
            foreach ($item as $key => $value) {
                if (is_bool($value) || is_numeric($item)) {
                    $item[$key] = ($value) ? 'true' : 'false';
                }
            }
        } else {
           if (is_bool($item) || is_numeric($item)) {
               $item = ($item) ? 'true' : 'false';
           }
        }

        return $item;
    }

    /**
     * {@inheritDoc}
     */
    public function getBooleanTypeDeclarationSQL(array $field)
    {
        return 'BOOLEAN';
    }

    /**
     * {@inheritDoc}
     */
    public function getIntegerTypeDeclarationSQL(array $field)
    {
        return 'INT';
    }

    /**
     * {@inheritDoc}
     */
    public function getBigIntTypeDeclarationSQL(array $field)
    {
        return 'DOUBLE';
    }

    /**
     * {@inheritDoc}
     */
    public function getSmallIntTypeDeclarationSQL(array $field)
    {
        return 'SHORT';
    }

    /**
     * {@inheritDoc}
     */
    public function getFloatDeclarationSQL(array $field)
    {
        return 'FLOAT';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeTypeDeclarationSQL(array $fieldDeclaration)
    {
        return 'TIMESTAMP';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeTzTypeDeclarationSQL(array $fieldDeclaration)
    {
        return 'TIMESTAMP';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTypeDeclarationSQL(array $fieldDeclaration)
    {
        return 'TIMESTAMP';
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeTypeDeclarationSQL(array $fieldDeclaration)
    {
        return 'TIMESTAMP';
    }

    /**
     * {@inheritDoc}
     */
    protected function _getCommonIntegerTypeDeclarationSQL(array $columnDef)
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    protected function getVarcharTypeDeclarationSQLSnippet($length, $fixed)
    {
        return 'STRING';
    }

    /**
     * {@inheritDoc}
     */
    public function getClobTypeDeclarationSQL(array $field)
    {
        return 'STRING';
    }

    /**
     * Gets the SQL snippet used to declare an OBJECT column type.
     *
     * @param array $field
     *
     * @return string
     */
    public function getMapTypeDeclarationSQL(array $field)
    {
        return 'OBJECT';
    }

    /**
     * Gets the SQL snippet used to declare an ARRAY column type.
     *
     * @param array $field
     *
     * @return string
     */
    public function getArrayTypeDeclarationSQL(array $field)
    {
        return 'ARRAY';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'crate';
    }

    /**
     * {@inheritDoc}
     *
     * PostgreSQL returns all column names in SQL result sets in lowercase.
     */
    public function getSQLResultCasing($column)
    {
        return strtolower($column);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeTzFormatString()
    {
        return self::TIMESTAMP_FORMAT_TZ;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormatString()
    {
        return self::TIMESTAMP_FORMAT;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateFormatString()
    {
        return self::TIMESTAMP_FORMAT;
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeFormatString()
    {
        return self::TIMESTAMP_FORMAT;
    }

    /**
     * {@inheritDoc}
     */
    public function getTruncateTableSQL($tableName, $cascade = false)
    {
        throw DBALException::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function getReadLockSQL()
    {
        throw DBALException::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    protected function initializeDoctrineTypeMappings()
    {
        $this->doctrineTypeMapping = array(
            'short'         => 'smallint',
            'integer'       => 'integer',
            'int'           => 'integer',
            'long'          => 'integer',
            'bool'          => 'boolean',
            'boolean'       => 'boolean',
            'string'        => 'string',
            'float'         => 'float',
            'double'        => 'float',
            'timestamp'     => 'timestamp',
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getVarcharMaxLength()
    {
        return PHP_INT_MAX;
    }

    /**
     * {@inheritDoc}
     */
    protected function getReservedKeywordsClass()
    {
        return 'Crate\DBAL\Platforms\Keywords\CrateKeywords';
    }

    /**
     * {@inheritDoc}
     */
    public function getBlobTypeDeclarationSQL(array $field)
    {
        throw DBALException::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     * Gets the SQL statement(s) to create a table with the specified name, columns and constraints
     * on this platform.
     *
     * @param string $table The name of the table.
     * @param integer $createFlags
     *
     * @return array The sequence of SQL statements.
     */
    public function getCreateTableSQL(Table $table, $createFlags = self::CREATE_INDEXES)
    {
        if ( ! is_int($createFlags)) {
            throw new \InvalidArgumentException("Second argument of CratePlatform::getCreateTableSQL() has to be integer.");
        }

        if (count($table->getColumns()) === 0) {
            throw DBALException::noColumnsSpecifiedForTable($table->getName());
        }

        $tableName = $table->getQuotedName($this);
        $options = $table->getOptions();
        $options['uniqueConstraints'] = array();
        $options['indexes'] = array();
        $options['primary'] = array();

        if (($createFlags&self::CREATE_INDEXES) > 0) {
            foreach ($table->getIndexes() as $index) {
                /* @var $index Index */
                if ($index->isPrimary()) {
                    $platform = $this;
                    $options['primary'] = array_map(function ($columnName) use ($table, $platform) {
                        return $table->getColumn($columnName)->getQuotedName($platform);
                    }, $index->getColumns());
                    $options['primary_index'] = $index;
                } else {
                    $options['indexes'][$index->getName()] = $index;
                }
            }
        }

        $columnSql = array();
        $columns = array();

        foreach ($table->getColumns() as $column) {
            /* @var \Doctrine\DBAL\Schema\Column $column */

            if (null !== $this->_eventManager && $this->_eventManager->hasListeners(Events::onSchemaCreateTableColumn)) {
                $eventArgs = new SchemaCreateTableColumnEventArgs($column, $table, $this);
                $this->_eventManager->dispatchEvent(Events::onSchemaCreateTableColumn, $eventArgs);

                $columnSql = array_merge($columnSql, $eventArgs->getSql());

                if ($eventArgs->isDefaultPrevented()) {
                    continue;
                }
            }

            $columnData = array();
            $columnData['name'] = $column->getQuotedName($this);
            $columnData['type'] = $column->getType();
            $columnData['length'] = $column->getLength();
            $columnData['notnull'] = $column->getNotNull();
            $columnData['fixed'] = $column->getFixed();
            $columnData['unique'] = false; // TODO: what do we do about this?
            $columnData['version'] = $column->hasPlatformOption("version") ? $column->getPlatformOption('version') : false;

            if (strtolower($columnData['type']) == "string" && $columnData['length'] === null) {
                $columnData['length'] = 255;
            }

            $columnData['unsigned'] = $column->getUnsigned();
            $columnData['precision'] = $column->getPrecision();
            $columnData['scale'] = $column->getScale();
            $columnData['default'] = $column->getDefault();
            $columnData['columnDefinition'] = $column->getColumnDefinition();
            $columnData['autoincrement'] = $column->getAutoincrement();
            $columnData['comment'] = $this->getColumnComment($column);

            if (in_array($column->getName(), $options['primary'])) {
                $columnData['primary'] = true;
            }

            $columns[$columnData['name']] = $columnData;
        }

        if (null !== $this->_eventManager && $this->_eventManager->hasListeners(Events::onSchemaCreateTable)) {
            $eventArgs = new SchemaCreateTableEventArgs($table, $columns, $options, $this);
            $this->_eventManager->dispatchEvent(Events::onSchemaCreateTable, $eventArgs);

            if ($eventArgs->isDefaultPrevented()) {
                return array_merge($eventArgs->getSql(), $columnSql);
            }
        }

        $sql = $this->_getCreateTableSQL($tableName, $columns, $options);
        if ($this->supportsCommentOnStatement()) {
            foreach ($table->getColumns() as $column) {
                if ($this->getColumnComment($column)) {
                    $sql[] = $this->getCommentOnColumnSQL($tableName, $column->getName(), $this->getColumnComment($column));
                }
            }
        }

        return array_merge($sql, $columnSql);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreateDatabaseSQL($database)
    {
        throw DBALException::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function getDropDatabaseSQL($database)
    {
        throw DBALException::notSupported(__METHOD__);
    }

}
