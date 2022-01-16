// Define the import schema window configuration
EB.window.schema = {};
EB.window.schema.importConfig = {
	title: _("{$namespace|lower}.schema_import"),
	id: '{$namespace|lower}-schema_import',
	url: {$namespace}.config.connectorUrl,
	width: '90%',
	boxMaxWidth: 1200,
	baseParams: {
		action: '{$namespace}\\Processors\\ebPackage\\ImportSchema',
	},
	fields: [
		{
			xtype: 'textfield',
			fieldLabel: _('{$namespace|lower}.schema_file_path'),
			anchor: '100%',
			name: 'schema_file_path'
		},
		{
			xtype: 'label',
			html: _('{$namespace|lower}.schema_file_path_desc'),
			cls: 'desc-under'
		},
		{
			xtype: 'label',
			html: '<hr/><h2 style="text-align:center;">-- OR --</h2></hr>'
		},
		{
			xtype: 'textarea',
			fieldLabel: _('{$namespace|lower}.schema_xml'),
			anchor: '100%',
			name: 'schema_xml',
			height: 500
		}
	],
	modal: true,
	stateful: false,
	closeAction: 'close'
};

EB.constructExtendRegister(
	{$namespace}.window, 
	'ImportSchema', 
	'{$namespace|lower}-window-importschema', 
	EB.window.schema.importConfig, 
	MODx.Window
);

// Add to the buttons for ebPackage only
var packageTbar = EB.model.ebPackage.grid.config.tbar;
var createBtn = packageTbar.shift();
var importBtn = {
	text: _("{$namespace|lower}.import_button"),
	cls: '',
	handler: function(btn, e) {
		var grid = Ext.getCmp("{$namespace|lower}-grid-ebpackage");
		if (grid) {
			grid.openWindow('import', 'ebPackage', '{$namespace|lower}-window-importschema', btn, e);
		}
	}
};

// Add the two buttons back to the front of the toolbar
EB.model.ebPackage.grid.config.tbar.splice(0,0,createBtn,importBtn);