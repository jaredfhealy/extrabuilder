<?php
namespace ExtraBuilder\Model\mysql;

use xPDO\xPDO;

class ebPackage extends \ExtraBuilder\Model\ebPackage
{

    public static $metaMap = array (
        'package' => 'ExtraBuilder\\Model\\',
        'version' => '3.0',
        'table' => 'extrabuilder_packages',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'display' => '',
            'package_key' => '',
            'base_class' => 'xPDO\\Om\\xPDOObject',
            'platform' => 'mysql',
            'default_engine' => 'InnoDB',
            'phpdoc_package' => '',
            'phpdoc_subpackage' => '',
            'version' => '',
            'sortorder' => 0,
            'core_path' => '',
            'assets_path' => '',
            'vuecmp' => '',
            'lexicon' => '',
        ),
        'fieldMeta' => 
        array (
            'display' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'package_key' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'base_class' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => 'xPDO\\Om\\xPDOObject',
            ),
            'platform' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => true,
                'default' => 'mysql',
            ),
            'default_engine' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => 'InnoDB',
            ),
            'phpdoc_package' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'phpdoc_subpackage' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'version' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
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
            'core_path' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '191',
                'phptype' => 'string',
                'null' => true,
                'default' => '',
            ),
            'assets_path' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '191',
                'phptype' => 'string',
                'null' => true,
                'default' => '',
            ),
            'vuecmp' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '20',
                'phptype' => 'string',
                'null' => true,
                'default' => '',
            ),
            'lexicon' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '20',
                'phptype' => 'string',
                'null' => true,
                'default' => '',
            ),
        ),
        'composites' => 
        array (
            'Objects' => 
            array (
                'class' => 'ExtraBuilder\\Model\\ebObject',
                'local' => 'id',
                'foreign' => 'package',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
            'Transports' => 
            array (
                'class' => 'ExtraBuilder\\Model\\ebTransport',
                'local' => 'id',
                'foreign' => 'package',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
        ),
    );

}
