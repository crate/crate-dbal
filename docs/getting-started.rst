===============
Getting Started
===============

Learn how to install and get started with the :ref:`CrateDB DBAL driver
<index>`.

.. rubric:: Table of Contents

.. contents::
   :local:

Prerequisites
=============

Your project must be using `Composer`_.

Set Up as a Dependency
======================

The driver is available as `a package`_.

Add the driver package to you project `composer.json`_ file, like this:

.. code-block:: json

    {
      "require": {
        "crate/crate-dbal":"~0.3.0"
      }
    }

If you're using `Doctrine ORM`_, you must add the ``doctrine/orm`` dependency
too. So the both additions together will look like this:

.. code-block:: json

   {
     "require": {
       "crate/crate-dbal":"~0.3.0",
       "doctrine/orm": "*"
     }
   }


Install
=======

Once the package has been configured as a dependency, you can install it, like
so::

    sh$ composer install

Afterwards, if you are not already doing so, you must require the Composer
`autoload.php`_ file. You can do this by adding a line like this to your PHP
application:

.. code-block:: php

    require __DIR__ . '/vendor/autoload.php';

.. SEEALSO::

    For more help with Composer, consult the `Composer documentation`_.

Next Steps
==========

Learn how to :ref:`connect to CrateDB <connect>`.

.. _Composer documentation: https://getcomposer.org
.. _Composer: https://getcomposer.org/
.. _autoload.php: https://getcomposer.org/doc/01-basic-usage.md#autoloading
.. _composer.json: https://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup
.. _a package: https://packagist.org/packages/crate/crate-dbal
.. _Doctrine ORM: https://www.doctrine-project.org/projects/orm.html