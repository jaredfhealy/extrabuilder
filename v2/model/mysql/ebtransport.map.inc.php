<?php
$xpdo_meta_map['ebTransport']= array (
  'package' => 'extrabuilder.v2.model',
  'version' => '3.0',
  'table' => 'extrabuilder_transport',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'category' => '',
    'attributes' => '',
    'package' => 0,
    'major' => 0,
    'release' => '',
    'release_index' => 0,
    'minor' => 0,
    'patch' => 0,
    'sortorder' => 0,
  ),
  'fieldMeta' => 
  array (
    'category' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'attributes' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => true,
      'default' => '',
    ),
    'package' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'major' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'release' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '20',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'release_index' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'minor' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'patch' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'sortorder' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
  ),
  'aggregates' => 
  array (
    'Package' => 
    array (
      'class' => 'ebPackage',
      'local' => 'package',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
