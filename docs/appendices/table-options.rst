.. _table-options:

================================
 CrateDB specific table options
================================

CrateDB supports a custom ``CREATE TABLE`` syntax for adjusting the table
configuration incl. ``SHARDING`` and ``PARTITIONING`` of the table.
See `CrateDB CREATE TABLE Documentation`_ for all supported configuration
options.

All CrateDB specific table options must be passed in using the ``$options``
argument of the DBAL ``Table()`` constructor.

Example:

.. code-block:: php

   $options = [];
   $options['sharding_shards'] = 5;
   $myTable = new Table('my_table', [], [], [], 0, $options);


Sharding Options
================

Following table options can used to adjust the sharding of a table.
See also `CrateDB Sharding Documentation`_.

.. list-table::
   :header-rows: 1

   * - Name
     - Description
   * - ``$options['sharding_num_shards']``
     - Specifies the number of shards a table is stored in. Must be greater than 0.
   * - ``$options['sharding_routing_column']``
     - Allows to explicitly specify a column or field on which rows are sharded by.


Partitioning Options
====================

Following table options can used to define the partitioning columns of a table.
See also `CrateDB Partitioned Tables Documentation`_.


.. list-table::
   :header-rows: 1

   * - Name
     - Description
   * - ``$options['partition_columns']``
     - Specifies the columns on which partitions should be created on.

General Options
===============

All general CrateDB specific table options used inside the ``WITH (...)`` clause
can be configured under the ``table_options`` option key.

Example on how to adjust the replicas:

.. code-block:: php

   $options = [];
   $options['table_options'] = [];
   $options['table_options']['number_of_replicas'] = '2';
   $myTable = new Table('my_table', [], [], [], 0, $options);


.. _CrateDB CREATE TABLE Documentation: https://crate.io/docs/crate/reference/en/latest/sql/statements/create-table.html
.. _CrateDB Sharding Documentation: https://crate.io/docs/crate/reference/en/latest/general/ddl/sharding.html
.. _CrateDB Partitioned Tables Documentation: https://crate.io/docs/crate/reference/en/latest/general/ddl/partitioned-tables.html
