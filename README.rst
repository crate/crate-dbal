===================
CrateDB DBAL Driver
===================

.. image:: https://github.com/crate/crate-dbal/workflows/Tests/badge.svg
    :target: https://github.com/crate/crate-dbal/actions?workflow=Tests
    :alt: Build status (code)

.. image:: https://github.com/crate/crate-dbal/workflows/Docs/badge.svg
    :target: https://github.com/crate/crate-dbal/actions?workflow=Docs
    :alt: Build status (documentation)

.. image:: https://codecov.io/gh/crate/crate-dbal/branch/main/graph/badge.svg
    :target: https://app.codecov.io/gh/crate/crate-dbal
    :alt: Coverage

.. image:: https://scrutinizer-ci.com/g/crate/crate-dbal/badges/quality-score.png?b=main
    :target: https://scrutinizer-ci.com/g/crate/crate-dbal
    :alt: Quality

.. image:: https://poser.pugx.org/crate/crate-dbal/v/stable
    :target: https://packagist.org/packages/crate/crate-dbal
    :alt: Latest stable version

.. image:: https://img.shields.io/badge/PHP-7.2%2C%207.3%2C%207.4%2C%208.0%2C%208.1-green.svg
    :target: https://packagist.org/packages/crate/crate-dbal
    :alt: Supported PHP versions

.. image:: https://poser.pugx.org/crate/crate-dbal/d/monthly
    :target: https://packagist.org/packages/crate/crate-dbal
    :alt: Monthly downloads

.. image:: https://poser.pugx.org/crate/crate-dbal/license
    :target: https://packagist.org/packages/crate/crate-dbal
    :alt: License

|

The CrateDB DBAL driver is an implementation of the `DBAL`_  abstraction layer
for CrateDB_.

`DBAL`_ is a PHP database abstraction layer that comes with database schema
introspection, schema management, and `PDO`_ abstraction.

Prerequisites
=============

You need to be using PHP and Composer_.

Installation
============

The CrateDB PDO adapter is available as a Composer package. Install it like::

    composer require crate/crate-dbal

See the `installation documentation`_ for more info.

Contributing
============

This project is primarily maintained by `Crate.io`_, but we welcome community
contributions!

See the `developer docs`_ and the `contribution docs`_ for more information.

Help
====

Looking for more help?

- Read the `project docs`_
- Check out our `support channels`_

.. _`DBAL`: http://www.doctrine-project.org/projects/dbal.html
.. _`PDO`: http://php.net/manual/en/book.pdo.php
.. _Composer: https://getcomposer.org/
.. _contribution docs: CONTRIBUTING.rst
.. _Crate.io: http://crate.io/
.. _CrateDB: https://github.com/crate/crate
.. _developer docs: DEVELOP.rst
.. _installation documentation: https://crate.io/docs/reference/dbal/installation.html
.. _project docs: https://crate.io/docs/reference/dbal/
.. _support channels: https://crate.io/support/
