===============
Developer Guide
===============

Prerequisites
=============

You will need Vagrant_ and one of its providers.

We currently use VirtualBox_ but any provider should work just as well.

Installation
============

Clone the project::

    git clone git@github.com:crate/crate-dbal.git

Start up the Vagrant machine::

    $ cd crate-dbal
    $ vagrant up

When run for the first time, it will also run the needed provisioning.

If you are using IntelliJ or PhpStorm IDE you can follow the `IDE guide`_ to
set up your remote interpreter and test environment.

Running the Tests
=================

You can run the tests like so::

    $ vagrant ssh
    $ cd /vagrant
    $ ./vendor/bin/phpunit --coverage-html ./report

Archiving Docs Versions
=======================

Check the `versions hosted on ReadTheDocs`_.

We should only be hosting the docs for `latest`, the last three minor release
branches of the last major release, and the last minor release branch
corresponding to the last two major releases.

For example:

- ``latest``
- ``0.3``
- ``0.2``
- ``0.1``

Because this project has not yet had a major release, as of yet, there are no
major releases before `0` to include in this list.

To make changes to the RTD configuration (e.g., to activate or deactivate a
release version), please contact the `@crate/docs`_ team.

.. _@crate/docs: https://github.com/orgs/crate/teams/docs
.. _Composer: https://getcomposer.org
.. _Vagrant: https://www.vagrantup.com/downloads.html
.. _VirtualBox: https://www.virtualbox.org/
.. _IDE guide: https://gist.github.com/mikethebeer/d8feda1bcc6b6ef6ea59
.. _versions hosted on ReadTheDocs: https://readthedocs.org/projects/crate-dbal/versions/
