<?php

/*
 * Basic example program about handling CrateDB OBJECTs with Doctrine DBAL.
 * https://github.com/crate/crate-dbal
 */
require __DIR__ . '/../vendor/autoload.php';

use Crate\DBAL\Platforms\CratePlatform4;
use Crate\DBAL\Types\MapType;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;

use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Type;

// Register driver.
$dsnParser = new DsnParser(array('crate' => 'Crate\DBAL\Driver\PDOCrate\Driver'));

// Compute connection options.
$options = $dsnParser->parse('crate://crate:crate@localhost:4200/');

// Select platform. It is highly encouraged to use the platform
// class that matches your database vendor and version best.
// https://www.doctrine-project.org/projects/doctrine-dbal/en/3.10/reference/platforms.html
$options['platform'] = new CratePlatform4();

// Connect to database.
$connection = DriverManager::getConnection($options);

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

// Provision database table.
$schemaManager = $connection->createSchemaManager();
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
