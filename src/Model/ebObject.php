<?php
namespace ExtraBuilder\Model;

use xPDO\xPDO;

/**
 * Class ebObject
 *
 * @property string $class
 * @property string $table_name
 * @property string $extends
 * @property integer $package
 * @property integer $sortorder
 * @property string $raw_xml
 *
 * @property \ExtraBuilder\Model\ebField[] $Fields
 * @property \ExtraBuilder\Model\ebRel[] $Rels
 *
 * @package ExtraBuilder\Model
 */
class ebObject extends \xPDO\Om\xPDOSimpleObject
{
}
