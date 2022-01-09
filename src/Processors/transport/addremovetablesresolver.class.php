<?php

/**
 * Build the Transport
 *
 * @package extrabuilder
 * @subpackage processors.transport
 */
class ExtrabuilderAddTablesResolverTransportProcessor extends modObjectProcessor
{
    public $classKey = 'ebTransport';
    public $languageTopics = array('extrabuilder:default');
    public $objectType = 'extrabuilder.transport';

    /**
     * Override the process function
     *
     */
    public function process()
    {
        // Get the passed in Transport object or return failure
        $primaryKey = $this->getProperty($this->primaryKeyField, false);
        if (!$this->object = $this->modx->getObject($this->classKey, $primaryKey)) {
            return $this->failure('Unable to get the Transport object record');
        }

        // Get the related Package or return failure
        $this->package = $this->modx->getObject('ebPackage', $this->object->get('package'));
        if (!$this->package) {
            return $this->failure('Unable to get the related Package object record.');
        }

        // Copy the _build/resolvers/_example.php to the destination
        $resolversDir = $this->modx->extrabuilder->replaceCorePaths($this->package->get('core_path'), $this->package->get('package_key'));
        $resolversDir = $resolversDir . '_build/resolvers/uninstall/';

        // Check if the directory exists
        if (!is_dir($resolversDir)) {
            mkdir($resolversDir, 0755, true);
        }

        // Tables resolver path
        $tablesResolver = MODX_CORE_PATH . '/components/extrabuilder/_build/resolvers/uninstall/remove_tables.php';
        if (is_file($tablesResolver)) {
            $destinationFile = $resolversDir . 'remove_tables.php';
            if (!is_file($destinationFile)) {
                if (copy($tablesResolver, $destinationFile)) {
                    return $this->success("Resolver created successfully: {$resolversDir}tables.php <br/><u>Update the file directly to add your resolver code.</u>");
                } else {
                    return $this->failure("Unable to create file: {$resolversDir}tables.php <br/><u>Please validate permissions in the directory</u>.");
                }
            } else {
                return $this->failure('That file already exists. To avoid unintentional data loss, please remove manually.');
            }
        } else {
            return $this->failure('The source file appears to be missing from {core_path}components/extrabuilder/_build/resolver/tables.php');
        }
    }
}
return 'ExtrabuilderAddTablesResolverTransportProcessor';
