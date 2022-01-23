// Define the import schema window configuration
EB.window.schema = {};
EB.window.schema.previewConfig = {
	title: _("{$cmpNamespace}.schema_preview"),
	id: '{$cmpNamespace}-window-schema',
	width: '90%',
	boxMaxWidth: 1200,
	fields: [
		{
			xtype: 'textfield',
			fieldLabel: _('{$cmpNamespace}.preview_assets_path'),
			anchor: '100%',
			name: 'assets_path',
			disabled: true,
			disabledClass: 'eb-disabled'
		},
		{
			xtype: 'textfield',
			fieldLabel: _('{$cmpNamespace}.preview_core_path'),
			anchor: '100%',
			name: 'core_path',
			disabled: true,
			disabledClass: 'eb-disabled'
		},
		{
			xtype: 'textarea',
			fieldLabel: _('{$cmpNamespace}.schema_xml_out'),
			submitValue: false,
			name: 'schema',
			disabled: true,
			disabledClass: 'eb-disabled',
			anchor: '100%',
			height: 300
		},
		{
			xtype: 'textarea',
			fieldLabel: _('{$cmpNamespace}.schema_log'),
			submitValue: false,
			name: 'messages',
			disabled: true,
			disabledClass: 'eb-disabled',
			anchor: '100%',
			height: 300
		}
	],
	modal: true,
	blankValues: false,
	closeAction: 'close',
	stateful: false,
	buttons: [
		{
            text: _('ok'),
			cls: 'primary-button',
            handler: function() {
                EB.window.schema.preview.close();
            }
        }
	]
};

EB.constructExtendRegister(
	{$phpNamespace}.window, 
	'PreviewSchema', 
	'{$cmpNamespace}-window-schema', 
	EB.window.schema.previewConfig, 
	MODx.Window
);