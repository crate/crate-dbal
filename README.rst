===================
CrateDB DBAL Driver
===================

.. image:: https://poser.pugx.org/crate/crate-dbal/license
    :target: https://packagist.org/packages/crate/crate-dbal
    :alt: License

.. image:: https://poser.pugx.org/crate/crate-dbal/v/stable
    :target: https://packagist.org/packages/crate/crate-dbal
    :alt: Latest stable version

.. image:: https://poser.pugx.org/crate/crate-dbal/d/monthly
    :target: https://packagist.org/packages/crate/crate-dbal
    :alt: Monthly downloads

.. image:: https://img.shields.io/badge/PHP-8.0%2C%208.1%2C%208.2%2C%208.3%2C%208.4%2C%208.5-green.svg
    :target: https://packagist.org/packages/crate/crate-dbal
    :alt: Supported PHP versions

|

.. image:: https://github.com/crate/crate-dbal/workflows/Tests/badge.svg
    :target: https://github.com/crate/crate-dbal/actions?workflow=Tests
    :alt: Build status

.. image:: https://github.com/crate/crate-dbal/workflows/Docs/badge.svg
    :target: https://github.com/crate/crate-dbal/actions?workflow=Docs
    :alt: Build status (documentation)

.. image:: https://codecov.io/gh/crate/crate-dbal/branch/main/graph/badge.svg
    :target: https://app.codecov.io/gh/crate/crate-dbal
    :alt: Coverage

.. image:: https://scrutinizer-ci.com/g/crate/crate-dbal/badges/quality-score.png?b=main
    :target: https://scrutinizer-ci.com/g/crate/crate-dbal
    :alt: Quality

|

`DBAL`_ is a PHP database abstraction layer that comes with database schema
introspection, schema management, and `PDO`_ abstraction.

The `CrateDB DBAL Driver`_ is an implementation of the DBAL abstraction
layer for CrateDB_.

Installation
============

The CrateDB PDO Driver is available as a Composer_ package.
See the `installation documentation`_ for more information.

::

    composer require crate/crate-dbal

Documentation
=============

The documentation for the ``crate-dbal`` package
is available at https://cratedb.com/docs/dbal/.

Contributing
============

This project is primarily maintained by `Crate.io`_, but community
contributions are very much welcome.
See the `developer docs`_ and the `contribution docs`_ for more
information about how to get started and how to contribute.
If you need a different support contact for contributions or
requests other than GitHub, please choose one of our other
`support channels`_.


.. _`DBAL`: http://www.doctrine-project.org/projects/dbal.html
.. _`PDO`: http://php.net/manual/en/book.pdo.php
.. _Composer: https://getcomposer.org/
.. _contribution docs: CONTRIBUTING.rst
.. _Crate.io: http://cratedb.com/
.. _CrateDB: https://github.com/crate/crate
.. _CrateDB DBAL Driver: https://github.com/crate/crate-dbal
.. _developer docs: DEVELOP.rst
.. _installation documentation: https://cratedb.com/docs/reference/dbal/getting-started.html
.. _project docs: https://cratedb.com/docs/reference/dbal/
.. _support channels: https://cratedb.com/support/
