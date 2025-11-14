.. _connect:

==================
Connect to CrateDB
==================

Authentication
==============

.. NOTE::

   These examples authenticate as ``crate``, the default database user in
   CrateDB versions 2.1.x and later.

   If you are using CrateDB 2.1.x or later, you must supply a username. If you
   are using earlier versions of CrateDB, this parameter is not supported.

   See the :ref:`compatibility notes <cratedb-versions>` for more information.

   If you have not configured a custom `database user`_, you probably want to
   authenticate as the CrateDB superuser, which is ``crate``. The superuser
   does not have a password, so you can omit the ``password`` argument.

DBAL
====

If you plan to query CrateDB via DBAL, you can get a connection from the
``DriverManager`` class using `standard DBAL parameters`_, like so:

.. code-block:: php

    use Doctrine\DBAL\DriverManager;

    $params = array(
        'driverClass' => 'Crate\DBAL\Driver\PDOCrate\Driver',
        'user' => 'crate',
        'host' => 'localhost',
        'port' => 4200
    );
    $connection = DriverManager::getConnection($params);
    $schemaManager = $connection->createSchemaManager();

With these connection parameters, the ``DriverManager`` will attempt to
authenticate as ``crate`` with a CrateDB node listening on ``localhost:4200``.

.. SEEALSO::

   For more help using DBAL_, consult the `DBAL documentation`_.

Doctrine ORM
============

If you want to use the `Object-Relational Mapping`_ (ORM) features of Doctrine,
you must set up an ``EntityManager`` instance.

Here's a slightly modified version of the `Doctrine provided example`_:

.. code-block:: php

   use Doctrine\ORM\Tools\Setup;
   use Doctrine\ORM\EntityManager;

   $paths = array("/path/to/entity-files");
   $isDevMode = false;

   // the connection configuration
   $params = array(
       'driverClass' => 'Crate\DBAL\Driver\PDOCrate\Driver',
       'user' => 'crate'
       'host' => 'localhost',
       'port' => 4200
   );

   $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
   $entityManager = EntityManager::create($params, $config);

Here's what we changed in the above example:

- Specified the CrateDB driver class
- Specified the ``crate`` user
- Configured the connection for ``localhost:4200``

.. SEEALSO::

    The CrateDB DBAL driver provides three custom type objects. Consult the
    :ref:`data types <data-types>` appendix for more information about type
    maps and column type definitions.

Next steps
==========

Use the standard the `DBAL documentation`_ or `Doctrine ORM documentation`_ for the rest of
your setup process.

.. _database user: https://cratedb.com/docs/crate/reference/en/latest/admin/user-management.html
.. _DBAL: https://www.doctrine-project.org/projects/dbal.html
.. _DBAL documentation: https://www.doctrine-project.org/projects/doctrine-dbal/en/3.0/index.html
.. _Doctrine provided example: https://www.doctrine-project.org/projects/doctrine-orm/en/3.0/reference/configuration.html#obtaining-an-entitymanager
.. _Object-Relational Mapping: https://www.doctrine-project.org/projects/orm.html
.. _Doctrine ORM documentation: https://www.doctrine-project.org/projects/doctrine-orm/en/3.0/index.html
.. _standard DBAL parameters: https://www.doctrine-project.org/projects/doctrine-dbal/en/3.0/reference/configuration.html
