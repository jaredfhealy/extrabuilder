<?php
namespace ExtraBuilder\Model\mysql;

use xPDO\xPDO;

class ebObject extends \ExtraBuilder\Model\ebObject
{

    public static $metaMap = array (
        'package' => 'ExtraBuilder\\Model\\',
        'version' => '3.0',
        'table' => 'extrabuilder_objects',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'class' => '',
            'table_name' => '',
            'extends' => 'xPDO\\Om\\xPDOSimpleObject',
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
                'default' => 'xPDO\\Om\\xPDOSimpleObject',
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
                'class' => 'ExtraBuilder\\Model\\ebField',
                'local' => 'id',
                'foreign' => 'object',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
            'Rels' => 
            array (
                'class' => 'ExtraBuilder\\Model\\ebRel',
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
                'class' => 'ExtraBuilder\\Model\\ebPackage',
                'local' => 'package',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
