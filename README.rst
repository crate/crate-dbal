.. image:: https://cdn.crate.io/web/2.0/img/crate-avatar_100x100.png
    :width: 100px
    :height: 100px
    :alt: Crate.IO
    :target: https://crate.io

.. image:: https://travis-ci.org/crate/crate-dbal.svg?branch=master
    :target: https://travis-ci.org/crate/crate-dbal
    :alt: Build status

.. image:: https://scrutinizer-ci.com/g/crate/crate-dbal/badges/coverage.png?b=master
    :target: https://scrutinizer-ci.com/g/crate/crate-dbal
    :alt: Coverage

.. image:: https://scrutinizer-ci.com/g/crate/crate-dbal/badges/quality-score.png?b=master
    :target: https://scrutinizer-ci.com/g/crate/crate-dbal
    :alt: Quality


DBAL Driver for Crate
=====================

`DBAL`_ is a database abstraction layer with features for database schema introspection,
schema management and `PDO`_ abstraction written in `PHP`_.

**crate-dbal** is an implementation of this abstraction layer for `Crate`_.


Installation
------------

Install the library by adding it to your ``composer.json`` or running::

  php composer.phar require crate/crate-dbal:~0.0.5

Configuration
-------------

The Crate driver class is ``Crate\DBAL\Driver\PDOCrate\Driver``.

You can obtain a connection from the ``DriverManager`` using the following parameters::

  $params = array(
      'driverClass' => 'Crate\DBAL\Driver\PDOCrate\Driver',
      'host' => 'localhost',
      'port' => 4200
  );
  $connection = \Doctrine\DBAL\DriverManager::getConnection($params);
  $schemaManager = $connection->getSchemaManager();

Supported Types
---------------

The following Crate data types are currently supported:

- ``BOOLEAN``
- ``STRING``
- ``SHORT``
- ``INTEGER``
- ``LONG``
- ``FLOAT``
- ``DOUBLE``
- ``TIMESTAMP``
- ``OBJECT``
- ``ARRAY``

Limitations
-----------

The schema for the ``OBJECT`` and ``ARRAY`` data types can be defined only programmatically.

Example::

  <?php
  use Doctrine\DBAL\Schema\Column;
  use Doctrine\DBAL\Schema\Table;
  use Doctrine\DBAL\Types\Type;
  use Crate\DBAL\Types\MapType;

  $table = new Table('test_table');
  $objDefinition = array(
    'type' => MapType::STRICT,
     'fields' => array(
       new Column('id',  Type::getType('integer'), array()),
       new Column('name',  Type::getType('string'), array()),
       ),
     );
  $table->addColumn('object_column', MapType::NAME,
                    array('platformOptions'=>$objDefinition));
  $schemaManager->createTable($table);


Not Supported
.............

- fulltext indexes
- JOINs in general are not supported,
  however referencing relations can be done without joins
  using Doctrine's lazy loading mechanism with subsequent SELECTs
  (except many-to-many releations)
- `DQL`_ statements with JOINs are not supported

Usage with Doctrine ORM
-----------------------

::

  <?php
  require_once "vendor/autoload.php";

  use Doctrine\ORM\Tools\Setup;
  use Doctrine\ORM\EntityManager;

  $paths = array("/path/to/entity-files");
  $isDevMode = false;

  // the connection configuration
  $params = array(
      'driverClass' => 'Crate\DBAL\Driver\PDOCrate\Driver',
      'host' => 'localhost',
      'port' => 4200
  );

  $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
  $entityManager = EntityManager::create($params, $config);

For a more detailed configuration please refer to the `Doctrine ORM`_ documentation.


.. _`DBAL`: http://www.doctrine-project.org/projects/dbal.html
.. _`PDO`: http://php.net/manual/en/book.pdo.php
.. _`PHP`: http://php.net
.. _`Crate`: https://crate.io
.. _`Doctrine ORM`: http://doctrine-orm.readthedocs.org/en/latest/reference/configuration.html
.. _`DQL`: http://doctrine-orm.readthedocs.org/en/latest/reference/dql-doctrine-query-language.html

