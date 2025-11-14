<?php

/*
 * Basic example program about handling CrateDB OBJECTs with Doctrine DBAL.
 * https://github.com/crate/crate-dbal
 */
require __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DriverManager;

use Crate\DBAL\Platforms\CratePlatform4;
use Crate\DBAL\Types\MapType;

// Initialize machinery.
// This ensures that the 'map' type is registered in the type system from the beginning.
$platform = new CratePlatform4();

// Define table schema.
$table = new Table('example');
$objDefinition = array(
    'type' => MapType::STRICT,
    'fields' => array(
        new Column('id', Type::getType('integer'), array()),
        new Column('name', Type::getType('string'), array()),
    ),
);
$table->addColumn(
    'data',
    MapType::NAME,
    array('platformOptions' => $objDefinition),
);

// Register driver.
$dsnParser = new DsnParser(array('crate' => 'Crate\DBAL\Driver\PDOCrate\Driver'));

// Connect to database.
$connectionParams = $dsnParser->parse('crate://crate:crate@localhost:4200/');
$connection = DriverManager::getConnection($connectionParams);
$schemaManager = $connection->createSchemaManager();

// Provision database table.
try {
    $schemaManager->dropTable($table->getName());
} catch (TableNotFoundException) {
}
$schemaManager->createTable($table);

// Insert data.
$connection->insert('example', array('data' => array('id' => 42, 'name' => 'foo')), array('data' => 'map'));
$connection->insert('example', array('data' => array('id' => 43, 'name' => 'bar')), array('data' => 'map'));
$connection->executeStatement('REFRESH TABLE example');

// Query data.
$result = $connection->executeQuery('SELECT * FROM example');
print_r($result->fetchAllAssociative());

?>
