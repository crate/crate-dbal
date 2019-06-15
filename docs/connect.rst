.. _connect:

==================
Connect to CrateDB
==================

.. rubric:: Table of contents

.. contents::
   :local:

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

    $params = array(
        'driverClass' => 'Crate\DBAL\Driver\PDOCrate\Driver',
        'user' => 'crate'
        'host' => 'localhost',
        'port' => 4200
    );
    $connection = \Doctrine\DBAL\DriverManager::getConnection($params);
    $schemaManager = $connection->getSchemaManager();

With these connection parameters, the ``DriverManager`` will attempt to
authenticate as ``crate`` with a CrateDB node listening on ``localhost:4200``.

.. SEEALSO::

   For more help using DBAL, consult the `DBAL documentation`_.

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

Use the standard the `DBAL`_ or `Doctrine ORM`_ documentation for the rest of
your setup process.

.. _database user: https://crate.io/docs/crate/reference/en/latest/admin/user-management.html
.. _DBAL documentation: https://www.doctrine-project.org/projects/doctrine-dbal/en/2.7/index.html
.. _DBAL: https://www.doctrine-project.org/projects/doctrine-dbal/en/2.7/index.html
.. _Doctrine provided example: https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/configuration.html#obtaining-an-entitymanager
.. _Object-Relational Mapping: https://www.doctrine-project.org/projects/orm.html
.. _ORM documentation: https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/index.html
.. _Doctrine ORM: https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/index.html
.. _standard DBAL parameters: http://doctrine-orm.readthedocs.org/projects/doctrine-dbal/en/latest/reference/configuration.html
