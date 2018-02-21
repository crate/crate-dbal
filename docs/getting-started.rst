===============
Getting Started
===============

This page shows you how to get started with the :ref:`the CrateDB DBAL driver
library <index>`.

Prerequisites
=============

You need to be using PHP and `Composer`_.

Installation
============

Install the library by adding it to your ``composer.json``:

.. code-block:: json

   {
     "require": {
       "crate/crate-dbal":"~0.3.0"
     }
   }


Or you can run::

   sh$ php composer.phar require crate/crate-dbal:~0.3.1

Then run ``composer install`` or ``composer update``.

Inside your PHP script you will need to require the autoload file:

.. code-block:: php

    <?php
    require 'vendor/autoload.php';
    ...

For more information how to use Composer, please refer to the
`Composer documentation`_.

Connect to CrateDB
==================

The CrateDB driver class is ``Crate\DBAL\Driver\PDOCrate\Driver``.

You can obtain a connection from the ``DriverManager`` using `standard DBAL
parameters`_:

.. code-block:: php

    $params = array(
        'driverClass' => 'Crate\DBAL\Driver\PDOCrate\Driver',
        'host' => 'localhost',
        'port' => 4200
    );
    $connection = \Doctrine\DBAL\DriverManager::getConnection($params);
    $schemaManager = $connection->getSchemaManager();

ORM
===

If you are using Doctrine's ORM features, an extra dependency is needed in your
*composer.json* file.

.. code-block:: json

   {
     "require": {
       "crate/crate-dbal":"~0.3.0",
       "doctrine/orm": "*"
     }
   }

Then you can install with ``composer intall``.

To create a connection, do:

.. code-block:: php

   require_once "vendor/autoload.php";

   use Doctrine\ORM\Tools\Setup;
   use Doctrine\ORM\EntityManager;

   $paths = array("/path/to/entity-files");
   $isDevMode = false;

   // the connection configuration
   $params = array(
       'driverClass' => 'Crate\DBAL\Driver\PDOCrate\Driver',
       'host' => 'SERVER_IP',
       'port' => 4200
   );

   $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
   $entityManager = EntityManager::create($params, $config);

Setting up the Doctrine ORM requires some extra steps. We suggest reading
`the official Doctrine documentation
<http://doctrine-orm.readthedocs.org/en/latest/index.html>`_ to get started.

.. _Composer documentation: https://getcomposer.org
.. _Composer: https://getcomposer.org/
.. _standard DBAL parameters: http://doctrine-orm.readthedocs.org/projects/doctrine-dbal/en/latest/reference/configuration.html
