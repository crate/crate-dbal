.. _compatibility:

=============
Compatibility
=============

.. rubric:: Table of Contents

.. contents::
   :local:

.. _versions:

Version Notes
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
|                |                 | The `enterprise edition`_ of CrateDB      |
|                |                 | allows you to `create your own users`_.   |
|                |                 |                                           |
|                |                 | Prior versions of CrateDB do not support  |
|                |                 | this feature.                             |
+----------------+-----------------+-------------------------------------------+
| >= 1.0         | >= 2.3.x        |                                           |
+----------------+-----------------+-------------------------------------------+

.. _implementations:

Implementation Notes
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
| >= 1.1         | [BREAKING]: POD library requires PHP 7.2     | Supported. |
+                +----------------------------------------------+------------+
|                | Added SSL support                            | Supported. |
+                +----------------------------------------------+------------+
|                | Based on Doctrine 2.6.3                      | Supported. |
+----------------+----------------------------------------------+------------+

.. _create your own users: https://crate.io/docs/crate/reference/en/latest/admin/user-management.html
.. _enterprise edition: https://crate.io/products/cratedb-enterprise/
.. _query builder: https://www.doctrine-project.org/projects/doctrine-dbal/en/2.7/reference/query-builder.html#join-clauses
