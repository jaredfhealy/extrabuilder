<?php
$xpdo_meta_map['ebObject']= array (
  'package' => 'extrabuilder',
  'version' => '1.1',
  'table' => 'extrabuilder_objects',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'class' => '',
    'table_name' => '',
    'extends' => 'xPDOSimpleObject',
    'package' => 0,
    'sortorder' => 0,
    'raw_xml' => '',
  ),
  'fieldMeta' => 
  array (
    'class' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'table_name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'extends' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => 'xPDOSimpleObject',
    ),
    'package' => 
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
    'raw_xml' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
  ),
  'composites' => 
  array (
    'Fields' => 
    array (
      'class' => 'ebField',
      'local' => 'id',
      'foreign' => 'object',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Rels' => 
    array (
      'class' => 'ebRel',
      'local' => 'id',
      'foreign' => 'object',
      'cardinality' => 'many',
      'owner' => 'local',
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
