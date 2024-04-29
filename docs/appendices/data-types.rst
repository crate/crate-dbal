.. _data-types:

==========
Data types
==========

.. _type-map:

Type map
========

This driver maps CrateDB types to the following PHP types:

.. csv-table::
   :header: "CrateDB Type", "PHP Type"

   "`boolean`__", "`boolean`__"
   "`byte`__", "`integer`__"
   "`short`__", "`integer`__"
   "`integer`__", "`integer`__",
   "`long`__", "`string`__"
   "`float`__", "`float`__"
   "`double`__", "`float`__"
   "`string`__", "`string`__"
   "`ip`__", "`string`__"
   "`timestamp`__", "`DateTime`__"
   "`geo_point`__", "`array`__"
   "`geo_shape`__", "`array`__"
   "`object`__", "`array`__"
   "`array`__", "`array`__"

__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#boolean
__ https://www.php.net/manual/en/language.types.boolean.php
__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data
__ https://www.php.net/manual/en/language.types.integer.php
__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data
__ https://www.php.net/manual/en/language.types.integer.php
__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data
__ https://www.php.net/manual/en/language.types.integer.php
__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data
__ https://www.php.net/manual/en/language.types.string.php
__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data
__ https://www.php.net/manual/en/language.types.float.php
__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data
__ https://www.php.net/manual/en/language.types.float.php
__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#character-data
__ https://www.php.net/manual/en/language.types.string.php
__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#ip
__ https://www.php.net/manual/en/language.types.string.php
__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#dates-and-times
__ https://www.php.net/manual/en/class.datetime.php
__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#geo-point
__ https://www.php.net/manual/en/language.types.array.php
__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#geo-shape
__ https://www.php.net/manual/en/language.types.array.php
__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#object
__ https://www.php.net/manual/en/language.types.array.php
__ https://cratedb.com/docs/crate/reference/en/latest/general/ddl/data-types.html#array
__ https://www.php.net/manual/en/language.types.array.php

.. _column-type-definitions:

Column type definitions
=======================

When defining CrateDB `timestamp`_, `object`_ or `array`_ type columns, you
must construct the DBAL column definition programatically, using the types and
attributes provided by the CrateDB DBAL driver.

Primitive column types (e.g. string, integer, and so on) can be defined in
the regular DBAL way.

The custom type objects provided by the CrateDB DBAL driver are:

- `TimestampType`_
- `MapType`_
- `ArrayType`_

Here's an example of how the ``MapType`` can be used:

.. code-block:: php

    use Doctrine\DBAL\Schema\Column;
    use Doctrine\DBAL\Schema\Table;
    use Doctrine\DBAL\Types\Type;
    use Crate\DBAL\Types\MapType;

    $table = new Table('test_table');
    $objDefinition = array(
      'type' => MapType::STRICT,
       'fields' => array(
         new Column('id',  Type::getType('integer'), array()),
         new Column('name',  Type::getType('string'), array()),
         ),
       );
    $table->addColumn(
        'object_column', MapType::NAME,
        array('platformOptions'=>$objDefinition));
    $schemaManager->createTable($table);

Here, the ``MapType`` class being used to model a CrateDB object. Standard DBAL
types, like ``string``, are being used to construct the schema of the object,
via calls to the the use of the ``Column`` class and calls to the
``Type::getType`` static method, and so on.

.. SEEALSO::

   The Doctrine `ORM documentation`_ has more about type mapping.

.. _array: https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#array
.. _ArrayType: https://github.com/crate/crate-dbal/blob/main/src/Crate/DBAL/Types/ArrayType.php
.. _MapType: https://github.com/crate/crate-dbal/blob/main/src/Crate/DBAL/Types/MapType.php
.. _object: https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#object
.. _ORM documentation: https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html
.. _timestamp: https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#dates-and-times
.. _TimestampType: https://github.com/crate/crate-dbal/blob/main/src/Crate/DBAL/Types/TimestampType.php
