<?php

namespace ExtraBuilder\Processors;

use \MODX\Revolution\Processors\Processor;
use ExtraBuilder\Model\ebPackage;
use ExtraBuilder\Model\ebObject;
use ExtraBuilder\Model\ebField;
use ExtraBuilder\Model\ebRel;
use ExtraBuilder\Model\ebTransport;

/**
 * Load 
 *
 * @package extrabuilder
 * @subpackage processors
 */
class ExtrabuilderGetDefaultsProcessor extends Processor
{
    public function process()
	{
		// Build the return array
        $objectFields = [
            'ebPackage' => $this->getFieldDefaults(ebPackage),
            'ebObject' => $this->getFieldDefaults(ebObject),
            'ebField' => $this->getFieldDefaults(ebField),
            'ebRel' => $this->getFieldDefaults(ebRel),
            'ebTransport' => $this->getFieldDefaults(ebTransport)
        ];
        return $this->success('', $objectFields);
	}

    /**
     * Get the field defaults and remove 'null'
     */
    public function getFieldDefaults($class)
    {
        // Use the modx function
        $fields = $this->modx->getFields($class);

        // Loop through values
        foreach ($fields as $field => $value) {
            if ($value === null) {
                // Pass an empty string instead
                $fields[$field] = "";
            }
        }

        // Return the JSON string encoded defaults for this class
        return json_encode($fields);
    }

	public function checkPermissions()
    {
        return !empty($this->permission) ? $this->modx->hasPermission($this->permission) : true;
    }

    public function getLanguageTopics()
    {
        return ['ExtraBuilder:default'];
    }
}