<?php
namespace ExtraBuilder\Model\mysql;

use xPDO\xPDO;

class ebRel extends \ExtraBuilder\Model\ebRel
{

    public static $metaMap = array (
        'package' => 'ExtraBuilder\\Model\\',
        'version' => '3.0',
        'table' => 'extrabuilder_rel',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'alias' => '',
            'class' => '',
            'local' => '',
            'foreign' => '',
            'cardinality' => '',
            'owner' => '',
            'object' => 0,
            'relation_type' => '',
            'sortorder' => 0,
        ),
        'fieldMeta' => 
        array (
            'alias' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '20',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'class' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '50',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'local' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '50',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'foreign' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '50',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'cardinality' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '20',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'owner' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '20',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'object' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'relation_type' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '20',
                'phptype' => 'string',
                'null' => false,
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
