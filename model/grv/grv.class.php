<?php
class Grv {
    public $modx;
    public $config = array();
    public function __construct(modX &$modx,array $config = array()) {
        $this->modx =& $modx;
        
        // Default paths
        $corePath = $this->modx->getOption('core_path');
        $assetsPath = $this->modx->getOption('assets_url');
        
        // Check for overrides
        $basePath = $this->modx->getOption('grv.core_path', $config, $corePath.'components/grv/');
        $assetsUrl = $this->modx->getOption('grv.assets_url', $config, $assetsPath.'components/grv/');
        $this->config = array_merge(array(
            'basePath' => $basePath,
            'corePath' => $basePath,
            'modelPath' => $basePath.'model/',
            'processorsPath' => $basePath.'processors/',
            'templatesPath' => $basePath.'templates/',
            'chunksPath' => $basePath.'elements/chunks/',
            'jsUrl' => $assetsUrl.'js/',
            'cssUrl' => $assetsUrl.'css/',
            'assetsUrl' => $assetsUrl,
            'connectorUrl' => $assetsUrl.'connector.php',
        ),$config);
        
        // Add the package
        $this->modx->addPackage('grv', $this->config['modelPath']);
    }
}