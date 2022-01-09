<?php
namespace ExtraBuilder\Model;

use xPDO\xPDO;

/**
 * Class ebPackage
 *
 * @property string $display
 * @property string $package_key
 * @property string $base_class
 * @property string $platform
 * @property string $default_engine
 * @property string $phpdoc_package
 * @property string $phpdoc_subpackage
 * @property string $version
 * @property integer $sortorder
 * @property string $core_path
 * @property string $assets_path
 * @property string $vuecmp
 * @property string $lexicon
 *
 * @property \ExtraBuilder\Model\ebObject[] $Objects
 * @property \ExtraBuilder\Model\ebTransport[] $Transports
 *
 * @package ExtraBuilder\Model
 */
class ebPackage extends \xPDO\Om\xPDOSimpleObject
{
}
