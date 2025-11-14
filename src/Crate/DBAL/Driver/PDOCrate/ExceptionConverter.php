<?php

declare(strict_types=1);

namespace Crate\DBAL\Driver\PDOCrate;

use Doctrine\DBAL\Driver\API\ExceptionConverter as ExceptionConverterInterface;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\InvalidFieldNameException;
use Doctrine\DBAL\Exception\SchemaDoesNotExist;
use Doctrine\DBAL\Exception\SyntaxErrorException;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Query;

use Doctrine\DBAL\Schema\Exception\ColumnDoesNotExist;
use function strpos;

/** @internal */
final class ExceptionConverter implements ExceptionConverterInterface
{
    /** @link https://cratedb.com/docs/crate/reference/en/latest/interfaces/http.html#error-codes */
    public function convert(Exception $exception, ?Query $query): DriverException
    {
        switch ($exception->getCode()) {
            case '4000':
                return new SyntaxErrorException($exception, $query);

            case '4008':
            case '4043':
                return new InvalidFieldNameException($exception, $query);

            case '4041':
                return new TableNotFoundException($exception, $query);

            case '4045':
                return new SchemaDoesNotExist($exception, $query);

            case '4091':
                return new UniqueConstraintViolationException($exception, $query);

            case '4093':
                return new TableExistsException($exception, $query);
        }

        // Prior to fixing https://bugs.php.net/bug.php?id=64705 (PHP 7.4.10),
        // in some cases (mainly connection errors) the PDO exception wouldn't provide a SQLSTATE via its code.
        // We have to match against the SQLSTATE in the error message in these cases.
        if ($exception->getCode() === 7 && strpos($exception->getMessage(), 'SQLSTATE[08006]') !== false) {
            return new ConnectionException($exception, $query);
        }

        return new DriverException($exception, $query);
    }
}
