===================
CrateDB DBAL Driver
===================

.. image:: https://github.com/crate/crate-dbal/workflows/Tests/badge.svg
    :target: https://github.com/crate/crate-dbal/actions?workflow=Tests
    :alt: Build status

.. image:: https://scrutinizer-ci.com/g/crate/crate-dbal/badges/coverage.png?b=main
    :target: https://scrutinizer-ci.com/g/crate/crate-dbal
    :alt: Coverage

.. image:: https://scrutinizer-ci.com/g/crate/crate-dbal/badges/quality-score.png?b=main
    :target: https://scrutinizer-ci.com/g/crate/crate-dbal
    :alt: Quality

.. image:: https://poser.pugx.org/crate/crate-dbal/v/stable
    :target: https://packagist.org/packages/crate/crate-dbal
    :alt: Latest stable version

.. image:: https://poser.pugx.org/crate/crate-dbal/downloads
    :target: https://packagist.org/packages/crate/crate-dbal
    :alt: Total downloads

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
```bash
composer add crate/crate-dbal
```
The CrateDB PDO adapter is available as a Composer package.

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
