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

use Crate\DBAL\Platforms\Keywords\CrateKeywords;
use Crate\DBAL\Schema\CrateSchemaManager;
use Crate\DBAL\Types\MapType;
use Crate\DBAL\Types\TimestampType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Event\SchemaCreateTableColumnEventArgs;
use Doctrine\DBAL\Event\SchemaCreateTableEventArgs;
use Doctrine\DBAL\Events;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\DateIntervalUnit;
use Doctrine\DBAL\Platforms\Keywords\KeywordList;
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\TransactionIsolationLevel;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Types\Types;
use InvalidArgumentException;
use Crate\DBAL\Types\ArrayType;

class CratePlatform extends AbstractPlatform
{

    const TIMESTAMP_FORMAT =  'Y-m-d\TH:i:s';
    const TIMESTAMP_FORMAT_TZ =  'Y-m-d\TH:i:sO';
    const TABLE_WHERE_CLAUSE_FORMAT = '%s.table_name = %s AND %s.schema_name = %s';

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        $this->initializeDoctrineTypeMappings();

        if (!Type::hasType(MapType::NAME)) {
            Type::addType(MapType::NAME, MapType::class);

            $this->registerDoctrineTypeMapping(10, MapType::NAME);
        }
        if (!Type::hasType(TimestampType::NAME)) {
            Type::addType(TimestampType::NAME, TimestampType::class);
        }

        if (!Type::hasType(ArrayType::NAME)) {
            Type::addType(ArrayType::NAME, ArrayType::class);
            $this->registerDoctrineTypeMapping(9, ArrayType::NAME);
        }

    }

    /**
     * {@inheritDoc}
     */
    public function getSubstringExpression($value, $from = 0, $length = null): string
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
        throw Exception::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function getRegexpExpression(): string
    {
        return 'LIKE';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateDiffExpression($date1, $date2): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsIdentityColumns(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsIndexes(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsForeignKeyConstraints(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsViews(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getListDatabasesSQL(): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function getListTablesSQL()
    {
        return "SELECT table_name, schema_name FROM information_schema.tables " .
               "WHERE schema_name = 'doc' OR schema_name = 'blob'";
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableColumnsSQL($table, $database = null)
    {
        return "SELECT * from information_schema.columns c " .
               "WHERE " . $this->getTableWhereClause($table);
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableConstraintsSQL($table, $database = null)
    {
        return "SELECT c.constraint_name, c.constraint_type " .
               "FROM information_schema.table_constraints c " .
               "WHERE " . $this->getTableWhereClause($table) . " AND constraint_type = 'PRIMARY KEY'";
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableIndexesSQL($table, $currentDatabase = null)
    {
        return "SELECT c.constraint_name, c.constraint_type, k.column_name " .
               "FROM information_schema.table_constraints c " .
               "JOIN information_schema.key_column_usage k on c.constraint_name = k.constraint_name " .
               "WHERE " . $this->getTableWhereClause($table);
    }

    private function getTableWhereClause($table, $tableAlias = 'c')
    {
        if (strpos($table, '.') !== false) {
            [$schema, $table] = explode('.', $table);
            $schema = $this->quoteStringLiteral($schema);
        } else {
            $schema = $this->quoteStringLiteral('doc');
        }

        $table = new Identifier($table);
        $table = $this->quoteStringLiteral($table->getName());

        return sprintf(
            $this->getTableWhereClauseFormat(),
            $tableAlias,
            $table,
            $tableAlias,
            $schema
        );
    }

    /**
     * Return sprintf format string for usage at getTableWhereClause
     *
     * @return string
     */
    protected function getTableWhereClauseFormat()
    {
        return self::TABLE_WHERE_CLAUSE_FORMAT;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlterTableSQL(TableDiff $diff): array
    {
        $sql = array();
        $commentsSQL = array();
        $columnSql = array();

        foreach ($diff->getAddedColumns() as $column) {
            if ($this->onSchemaAlterTableAddColumn($column, $diff, $columnSql)) {
                continue;
            }

            $query = 'ADD ' . $this->getColumnDeclarationSQL($column->getQuotedName($this), $column->toArray());
            $sql[] = 'ALTER TABLE ' . $diff->name . ' ' . $query;
            if ($comment = $column->getComment()) {
                $commentsSQL[] = $this->getCommentOnColumnSQL($diff->name, $column->getName(), $comment);
            }
        }

        if (count($diff->getDroppedColumns()) > 0) {
            throw Exception::notSupported("Alter Table: drop columns");
        }
        if (count($diff->getChangedColumns()) > 0) {
            throw Exception::notSupported("Alter Table: change column options");
        }
        if (count($diff->getRenamedColumns()) > 0) {
            throw Exception::notSupported("Alter Table: rename columns");
        }

        $tableSql = array();

        if (!$this->onSchemaAlterTable($diff, $tableSql)) {
            if ($diff->newName !== false) {
                throw Exception::notSupported("Alter Table: rename table");
            }

            $sql = array_merge($sql, $this->getPreAlterTableIndexForeignKeySQL($diff), $this->getPostAlterTableIndexForeignKeySQL($diff), $commentsSQL);
        }

        return array_merge($sql, $tableSql, $columnSql);
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnDeclarationSQL($name, array $column): string
    {
        if (isset($column['columnDefinition'])) {
            $columnDef = $column['columnDefinition'];
        } else {
            $typeDecl = $column['type']->getSqlDeclaration($column, $this);
            $columnDef = $typeDecl;
        }

        return $name . ' ' . $columnDef;
    }

    /**
     * Generate table index column declaration
     * @codeCoverageIgnore
     */
    public function getIndexDeclarationSQL(Index $index): string
    {
        $columns = $index->getQuotedColumns($this);
        $name = new Identifier($index->getName());

        if (count($columns) == 0) {
            throw new \InvalidArgumentException("Incomplete definition. 'columns' required.");
        }

        $index = new Index($name->getQuotedName($this), $columns);
        return 'INDEX ' . $name->getQuotedName($this) .
               ' USING FULLTEXT ('. $this->getIndexFieldDeclarationListSQL($index) . ')';
    }

    /**
     * {@inheritDoc}
     *
     * Crate wants boolean values converted to the strings 'true'/'false'.
     */
    public function convertBooleans($item): mixed
    {
        if (is_array($item)) {
            foreach ($item as $key => $value) {
                if (is_bool($value)) {
                    $item[$key] = ($value) ? 'true' : 'false';
                } elseif (is_numeric($value)) {
                    $item[$key] = ($value > 0) ? 'true' : 'false';
                }
            }
        } else {
            if (is_bool($item)) {
                $item = ($item) ? 'true' : 'false';
            } elseif (is_numeric($item)) {
                $item = ($item > 0) ? 'true' : 'false';
            }
        }

        return $item;
    }

    /**
     * {@inheritDoc}
     */
    public function getBooleanTypeDeclarationSQL(array $field): string
    {
        return 'BOOLEAN';
    }

    /**
     * {@inheritDoc}
     */
    public function getIntegerTypeDeclarationSQL(array $field): string
    {
        return 'INTEGER';
    }

    /**
     * {@inheritDoc}
     */
    public function getBigIntTypeDeclarationSQL(array $field): string
    {
        return 'LONG';
    }

    /**
     * {@inheritDoc}
     */
    public function getSmallIntTypeDeclarationSQL(array $field): string
    {
        return 'SHORT';
    }

    /**
     * {@inheritDoc}
     */
    public function getFloatDeclarationSQL(array $field): string
    {
        return 'DOUBLE';
    }

    /**
     * {@inheritDoc}
     */
    public function getDecimalTypeDeclarationSQL(array $columnDef): string
    {
        return 'DOUBLE';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeTypeDeclarationSQL(array $fieldDeclaration): string
    {
        return 'TIMESTAMP';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeTzTypeDeclarationSQL(array $fieldDeclaration): string
    {
        return 'TIMESTAMP';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTypeDeclarationSQL(array $fieldDeclaration): string
    {
        return 'TIMESTAMP';
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeTypeDeclarationSQL(array $fieldDeclaration): string
    {
        return 'TIMESTAMP';
    }

    /**
     * {@inheritDoc}
     */
    protected function _getCommonIntegerTypeDeclarationSQL(array $columnDef): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    protected function getVarcharTypeDeclarationSQLSnippet(int|null $length): string
    {
        return Types::STRING;
    }

    /**
     * {@inheritDoc}
     */
    public function getClobTypeDeclarationSQL(array $field): string
    {
        return 'STRING';
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
    public function getDateTimeTzFormatString(): string
    {
        return self::TIMESTAMP_FORMAT_TZ;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormatString(): string
    {
        return self::TIMESTAMP_FORMAT;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateFormatString(): string
    {
        return self::TIMESTAMP_FORMAT;
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeFormatString(): string
    {
        return self::TIMESTAMP_FORMAT;
    }

    /**
     * {@inheritDoc}
     */
    public function getTruncateTableSQL($tableName, $cascade = false): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function getReadLockSQL()
    {
        throw Exception::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    protected function initializeDoctrineTypeMappings(): void
    {
        $this->doctrineTypeMapping = array(
            'short'         => 'smallint',
            'integer'       => 'integer',
            'long'          => 'bigint',
            'int'           => 'integer',
            'bool'          => 'boolean',
            'boolean'       => 'boolean',
            'string'        => 'string',
            'float'         => 'float',
            'double'        => 'float',
            'timestamp'     => 'timestamp',
            'object'        => 'map',
            'array'         => 'array',
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDoctrineTypeMapping($dbType): string
    {
        // typed arrays will always end up in the same generic php array type
        if (substr_compare($dbType, 'array', -5) === 0) {
            $dbType = 'array';
        }
        return parent::getDoctrineTypeMapping($dbType);
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
    public function getBlobTypeDeclarationSQL(array $field): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     * Gets the SQL statement(s) to create a table with the specified name, columns and constraints
     * on this platform.
     *
     * @param Table $table The name of the table.
     * @param integer $createFlags
     *
     * @return array The sequence of SQL statements.
     */
    public function getCreateTableSQL(Table $table, $createFlags = self::CREATE_INDEXES): array
    {
        if (!is_int($createFlags)) {
            $msg = "Second argument of CratePlatform::getCreateTableSQL() has to be integer.";
            throw new InvalidArgumentException($msg);
        }

        if (count($table->getColumns()) === 0) {
            throw Exception::noColumnsSpecifiedForTable($table->getName());
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
                } elseif ($index->isUnique()) {
                    throw Exception::notSupported(
                        "Unique constraints are not supported. Use `primary key` instead"
                    );
                } else {
                    $options['indexes'][$index->getName()] = $index;
                }
            }
        }

        $columnSql = array();
        $columns = array();

        foreach ($table->getColumns() as $column) {
            $columns[$column->getQuotedName($this)] = self::prepareColumnData($this, $column, $options['primary']);
        }

        $sql = $this->_getCreateTableSQL($tableName, $columns, $options);
        if ($this->supportsCommentOnStatement()) {
            foreach ($table->getColumns() as $column) {
                if ($this->getColumnComment($column)) {
                    $sql[] = $this->getCommentOnColumnSQL(
                        $tableName,
                        $column->getName(),
                        $this->getColumnComment($column)
                    );
                }
            }
        }

        return array_merge($sql, $columnSql);
    }

    /**
     * {@inheritDoc}
     */
    protected function _getCreateTableSQL($name, array $columns, array $options = array()): array
    {
        $columnListSql = $this->getColumnDeclarationListSQL($columns);

        if (isset($options['primary']) && ! empty($options['primary'])) {
            $keyColumns = array_unique(array_values($options['primary']));
            $columnListSql .= ', PRIMARY KEY(' . implode(', ', $keyColumns) . ')';
        }

        if (isset($options['indexes']) && ! empty($options['indexes'])) {
            foreach ($options['indexes'] as $index => $definition) {
                $columnListSql .= ', ' . $this->getIndexDeclarationSQL($index, $definition);
            }
        }

        if (isset($options['foreignKeys'])) {
            throw Exception::notSupported("Create Table: foreign keys");
        }

        $query = 'CREATE TABLE ' . $name . ' (' . $columnListSql . ')';
        $query .= $this->buildShardingOptions($options);
        $query .= $this->buildPartitionOptions($options);
        $query .= $this->buildTableOptions($options);
        return array($query);
    }

    /**
     * Build SQL for table options
     *
     * @param mixed[] $options
     *
     * @return string
     */
    private function buildTableOptions(array $options)
    {
        if (! isset($options['table_options'])) {
            return '';
        }

        $tableOptions = [];
        foreach ($options['table_options'] as $key => $val) {
            $tableOptions[] = sprintf('"%s" = %s', $key, $this->quoteStringLiteral($val));
        }
        if (count($tableOptions) == 0) {
            return '';
        }

        return sprintf(' WITH (%s)', implode(', ', $tableOptions));
    }

    /**
     * Build SQL for sharding options.
     *
     * @param mixed[] $options
     *
     * @return string
     */
    private function buildShardingOptions(array $options)
    {
        $shardingOptions = [];

        if (isset($options['sharding_routing_column'])) {
            $columnName = new Identifier($options['sharding_routing_column']);
            $shardingOptions[] = sprintf('BY (%s)', $columnName->getQuotedName($this));
        }
        if (isset($options['sharding_num_shards'])) {
            $shardingOptions[] = sprintf("INTO %d SHARDS", $options['sharding_num_shards']);
        }

        if (count($shardingOptions) == 0) {
            return '';
        }

        return sprintf(" CLUSTERED %s", implode(' ', $shardingOptions));
    }

    /**
     * Build SQL for partition options.
     *
     * @param mixed[] $options
     *
     * @return string
     */
    private function buildPartitionOptions(array $options)
    {
        if (! isset($options['partition_columns'])) {
            return '';
        }
        $columns = $options['partition_columns'];
        if (! is_array($columns)) {
            throw new InvalidArgumentException(sprintf("Expecting array type at 'partition_columns'"));
        }
        $quotedNames = [];
        foreach ($columns as $name) {
            $name = new Identifier($name);
            $quotedNames[] = $name->getQuotedName($this);
        }

        return sprintf(" PARTITIONED BY (%s)", implode(', ', $quotedNames));
    }

    /**
     * @param \Doctrine\DBAL\Schema\Column $column The name of the table.
     * @param array $primaries List of primary key column names
     *
     * @return array The column data as associative array.
     * @throws Exception
     */
    public static function prepareColumnData(AbstractPlatform $platform, $column, $primaries = array())
    {
        if ($column->hasPlatformOption("unique") ? $column->hasPlatformOption("unique") : false) {
            throw Exception::notSupported("Unique constraints are not supported. Use `primary key` instead");
        }

        $columnData = array();
        $columnData['name'] = $column->getQuotedName($platform);
        $columnData['type'] = $column->getType();
        $columnData['length'] = $column->getLength();
        $columnData['notnull'] = $column->getNotNull();
        $columnData['fixed'] = $column->getFixed();
        $columnData['unique'] = false;
        $columnData['version'] = $column->hasPlatformOption("version") ? $column->getPlatformOption("version") : false;

        if ($columnData['type'] == $platform->getVarcharTypeDeclarationSQLSnippet(0, false)
                && $columnData['length'] === null) {
            $columnData['length'] = 255;
        }

        $columnData['unsigned'] = $column->getUnsigned();
        $columnData['precision'] = $column->getPrecision();
        $columnData['scale'] = $column->getScale();
        $columnData['default'] = $column->getDefault();
        $columnData['columnDefinition'] = $column->getColumnDefinition();
        $columnData['autoincrement'] = $column->getAutoincrement();
        $columnData['platformOptions'] = $column->getPlatformOptions();

        if (in_array($column->getName(), $primaries)) {
            $columnData['primary'] = true;
        }
        return $columnData;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreateDatabaseSQL($database): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function getDropDatabaseSQL($database): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreateForeignKeySQL(ForeignKeyConstraint $foreignKey, $table): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function getGuidTypeDeclarationSQL(array $field): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    /**
     * Returns the SQL query to return the CrateDB specific table options associated
     * with a given table.
     *
     * @return string
     */
    public function getTableOptionsSQL(string $table) : string
    {
        return "SELECT clustered_by, number_of_shards, partitioned_by, number_of_replicas, column_policy, settings " .
               "FROM information_schema.tables c " .
               "WHERE " . $this->getTableWhereClause($table);
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentDatabaseExpression(): string
    {
        return 'current_database()';
    }

    public function createSchemaManager(Connection $connection): CrateSchemaManager
    {
        return new CrateSchemaManager($connection, $this);
    }

    public function getLocateExpression(string $string, string $substring, ?string $start = null): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    protected function getDateArithmeticIntervalExpression(string $date, string $operator, string $interval, DateIntervalUnit $unit,): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    public function getListViewsSQL(string $database): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    public function getSetTransactionIsolationSQL(TransactionIsolationLevel $level): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    protected function createReservedKeywordsList(): KeywordList
    {
        return new CrateKeywords();
    }
}
