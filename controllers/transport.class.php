<?php
class ExtrabuilderTransportManagerController extends modExtraManagerController {
    public function process(array $scriptProperties = array()) {
        $html = file_get_contents(dirname(dirname(__FILE__)) . '/templates/transport.html');
		$html .= str_replace('${iframe_src_path}', MODX_ASSETS_PATH.'components/extrabuilder/', $html);
		$html .= '<script type="text/x-template" id="transportjs">'.file_get_contents(dirname(dirname(__FILE__)) . '/templates/transport.js').'</script>';
        return $html;
    }
    
    /**
     * The pagetitle to put in the <title> attribute.
     * @return null|string
     */
    public function getPageTitle() {
        return 'Packages and Custom Tables';
    }
}