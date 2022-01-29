<?php
namespace ExtraBuilder\Model\mysql;

use xPDO\xPDO;

class ebField extends \ExtraBuilder\Model\ebField
{

    public static $metaMap = array (
        'package' => 'ExtraBuilder\\Model\\',
        'version' => '3.0',
        'table' => 'extrabuilder_fields',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'column_name' => '',
            'dbtype' => '',
            'precision' => 0,
            'phptype' => '',
            'allownull' => '',
            'default' => '',
            'sortorder' => 0,
            'object' => 0,
            'index' => '',
            'attributes' => '',
            'generated' => '',
            'extra' => '',
            'index_attributes' => '',
        ),
        'fieldMeta' => 
        array (
            'column_name' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'dbtype' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'precision' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'phptype' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'allownull' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'default' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => true,
                'default' => '',
            ),
            'sortorder' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'object' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'index' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => true,
                'default' => '',
            ),
            'attributes' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '10',
                'phptype' => 'string',
                'null' => true,
                'default' => '',
            ),
            'generated' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '20',
                'phptype' => 'string',
                'null' => true,
                'default' => '',
            ),
            'extra' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '191',
                'phptype' => 'string',
                'null' => true,
                'default' => '',
            ),
            'index_attributes' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '191',
                'phptype' => 'string',
                'null' => true,
                'default' => '',
            ),
        ),
        'aggregates' => 
        array (
            'Object' => 
            array (
                'class' => 'ExtraBuilder\\Model\\ebObject',
                'local' => 'object',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
