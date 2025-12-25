.. _compatibility:

=============
Compatibility
=============

.. _versions:

Version notes
=============

.. _cratedb-versions:

CrateDB
-------

Consult the following table for CrateDB version compatibility notes:

+----------------+-----------------+-------------------------------------------+
| Driver Version | CrateDB Version | Notes                                     |
+================+=================+===========================================+
| Any            | >= 2.1.x        | Client needs to connect with a valid      |
|                |                 | database user to access CrateDB.          |
|                |                 |                                           |
|                |                 | The default CrateDB user is ``crate`` and |
|                |                 | has no password is set.                   |
|                |                 |                                           |
+----------------+-----------------+-------------------------------------------+
| >= 1.0         | >= 2.3.x        |                                           |
+----------------+-----------------+-------------------------------------------+
| >= 2.0         | >= 4.0.x        |                                           |
+----------------+-----------------+-------------------------------------------+

.. _implementations:

Implementation notes
====================

.. _dbal-implementation:

DBAL
----

+----------------+----------------------------------------------+------------+
| Driver Version | Feature                                      | Notes      |
+================+==============================================+============+
| >= 0.2.0       | HTTP basic auth.                             | Supported. |
+----------------+----------------------------------------------+------------+
| >= 0.2.1       | PHP 7.                                       | Supported. |
+----------------+----------------------------------------------+------------+
| >= 0.3         | Exposed PDO methods:                         | Supported. |
|                |                                              |            |
|                | - ``getServerVersion()``                     |            |
|                | - ``getServerInfo()``                        |            |
+                +----------------------------------------------+------------+
|                | Connection to multiple CrateDB nodes.        | Supported. |
+                +----------------------------------------------+------------+
|                | Default schema selection.                    | Supported. |
+                +----------------------------------------------+------------+
|                | CrateDB SQL features:                        | Supported. |
|                |                                              |            |
|                | - Joins (with or without a `query builder`_) |            |
|                | - Fulltext indexes (without a query builder) |            |
+----------------+----------------------------------------------+------------+
| >= 1.1         | [BREAKING]: PDO library requires PHP 7.2     | Supported. |
+                +----------------------------------------------+------------+
|                | Added SSL support                            | Supported. |
+                +----------------------------------------------+------------+
|                | Based on Doctrine 2.6.3                      | Supported. |
+----------------+----------------------------------------------+------------+
| >= 2.0         | Based on Doctrine 2.9                        | Supported. |
+----------------+----------------------------------------------+------------+
| >= 2.2         | Based on Doctrine 2.10                       | Supported. |
+----------------+----------------------------------------------+------------+

.. _query builder: https://www.doctrine-project.org/projects/doctrine-dbal/en/3.6/reference/query-builder.html#join-clauses
