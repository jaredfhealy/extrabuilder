// Define the shared window config
EB.model['{$gridClass}'].window.config = {

	// This is a wider form, override default 600 width
	width: '90%',
	maxBoxWidth: 1200,

	// Fields to apply to our form
	fields: [
		{
			xtype: 'textfield',
			name: 'id',
			anchor: '100%',
			hidden: true
		},
		{
			xtype: 'textfield',
			name: 'classKey',
			anchor: '100%',
			hidden: true,
			value: '{$gridClass}'
		},
		{
			xtype: 'label',
			html: _('{$namespace|lower}.{$gridClass|lower}.html_desc')
		},
		{
			layout: 'column',
			border: false,
			anchor: '100%',
			defaults: {
				layout: 'form',
				displayAlign: 'top',
				displaySeparator: '',
				anchor: '100%',
				border: false
			},
			items: [
				{
					columnWidth: 0.4,
					border: false,
					defaults: {
						msgTarget: 'under',
						anchor: '100%'
					},
					items: [
						{
							xtype: '{$gridClass|lower}-combo-package',
							id: '{$gridClass|lower}-combo-package',
							fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.package"),
							name: 'package',
							anchor: '90%',
							allowBlank: false
						}
					]
				},
				{
					columnWidth: 0.6,
					border: false,
					defaults: {
						msgTarget: 'under',
						anchor: '100%'
					},
					items: [
						{
							xtype: '{$gridClass|lower}-combo-category',
							id: '{$gridClass|lower}-combo-category',
							fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.category"),
							name: 'category',
							anchor: '90%',
							allowBlank: false
						},
						{
							xtype: 'label',
							html: _("{$namespace|lower}.{$gridClass|lower}.category_desc"),
							cls: 'desc-under'
						}
					]
				}
			]
		},
		{
			xtype: 'label',
			html: '<hr/><h2>' + _("{$namespace|lower}.{$gridClass|lower}.version_section_label") + '</h2>'
		},
		{
			layout: 'column',
			border: false,
			anchor: '100%',
			defaults: {
				layout: 'form',
				displayAlign: 'top',
				displaySeparator: '',
				anchor: '100%',
				border: false
			},
			items: [
				{
					columnWidth: 0.18,
					border: false,
					defaults: {
						msgTarget: 'under',
						anchor: '100%'
					},
					items: [
						{
							xtype: 'textfield',
							id: '{$gridClass|lower}-major',
							fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.major_short"),
							name: 'major',
							anchor: '90%',
							allowBlank: false
						},
						{
							xtype: 'label',
							text: _("{$namespace|lower}.{$gridClass|lower}.major_desc"),
							cls: 'desc-under'
						}
					]
				},
				{
					columnWidth: 0.18,
					border: false,
					defaults: {
						msgTarget: 'under',
						anchor: '100%'
					},
					items: [
						{
							xtype: 'textfield',
							id: '{$gridClass|lower}-minor',
							fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.minor_short"),
							name: 'minor',
							anchor: '90%',
							allowBlank: false
						},
						{
							xtype: 'label',
							text: _("{$namespace|lower}.{$gridClass|lower}.minor_desc"),
							cls: 'desc-under'
						}
					]
				},
				{
					columnWidth: 0.18,
					border: false,
					defaults: {
						msgTarget: 'under',
						anchor: '100%'
					},
					items: [
						{
							xtype: 'textfield',
							id: '{$gridClass|lower}-patch',
							fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.patch_short"),
							name: 'patch',
							anchor: '90%',
							allowBlank: false
						},
						{
							xtype: 'label',
							text: _("{$namespace|lower}.{$gridClass|lower}.patch_desc"),
							cls: 'desc-under'
						}
					]
				},
				{
					columnWidth: 0.28,
					border: false,
					defaults: {
						msgTarget: 'under',
						anchor: '100%'
					},
					items: [
						{
							xtype: '{$namespace|lower}-combo-release',
							id: '{$gridClass|lower}-release',
							fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.release_short"),
							name: 'release',
							anchor: '90%',
							allowBlank: false
						},
						{
							xtype: 'label',
							text: _("{$namespace|lower}.{$gridClass|lower}.release_desc"),
							cls: 'desc-under'
						}
					]
				},
				{
					columnWidth: 0.18,
					border: false,
					defaults: {
						msgTarget: 'under',
						anchor: '100%'
					},
					items: [
						{
							xtype: 'textfield',
							id: '{$gridClass|lower}-release_index',
							fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.release_index"),
							name: 'release_index',
							anchor: '90%',
							allowBlank: false
						}
					]
				}
			]
		},
		{
			xtype: 'label',
			html: '<p style="margin-bottom: 40px">&nbsp;</p>'
		}
	]

};

// Define the package type drop down
EB.model['{$gridClass}'].window.packageComboConfig = {

	url: {$namespace}.config.connectorUrl,
	baseParams: {
		classKey: 'ebPackage',
		action: 'ExtraBuilder\\Processors\\GetList',
		type: 'combo'
	},
	fields: [
		'id', 'package_key', 'display'
	],
	displayField: 'display',
	valueField: 'id',
	hiddenName: 'package',
	pageSize: 20,
	mode: 'remote',
	editable: true
}

// Create, register, extend the combo box
EB.constructExtendRegister(
	{$namespace}.combo,
	'packageComboConfig',
	'{$gridClass|lower}-combo-package',
	EB.model['{$gridClass}'].window.packageComboConfig,
	MODx.combo.ComboBox
);

// Define the category type drop down
EB.model['{$gridClass}'].window.categoryComboConfig = {

	url: {$namespace}.config.connectorUrl,
	baseParams: {
		classKey: 'MODX\\Revolution\\modCategory',
		action: 'ExtraBuilder\\Processors\\GetList',
		type: 'combo'
	},
	fields: [
		'id', 'category', 'parent', 'rank'
	],
	displayField: 'category',
	valueField: 'category',
	hiddenName: 'category',
	pageSize: 20,
	mode: 'remote',
	editable: true
}

// Create, register, extend the combo box
EB.constructExtendRegister(
	{$namespace}.combo,
	'categoryComboConfig',
	'{$gridClass|lower}-combo-category',
	EB.model['{$gridClass}'].window.categoryComboConfig,
	MODx.combo.ComboBox
);

// Define the release drop down
EB.model['{$gridClass}'].window.releaseComboConfig = {

	store: new Ext.data.ArrayStore({
		fields: ['value', 'display'],
		data: [
			[
				'pl',
				'pl - Patch Level'
			],
			[
				'rc',
				'rc - Release Candidate'
			],
			[
				'beta',
				'beta - Beta'
			],
			[
				'alpha',
				'alpha - Alpha'
			],
			[
				'dev',
				'dev - Dev'
			]
		]
	}),
	mode: 'local',
	displayField: 'display',
	valueField: 'value',
	hiddenName: 'release'
}

// Create, register, extend the combo box
EB.constructExtendRegister(
	{$namespace}.combo, 
	'releaseComboConfig', 
	'{$namespace|lower}-combo-release', 
	EB.model['{$gridClass}'].window.releaseComboConfig,
	MODx.combo.ComboBox
);

// Apply the config to both window types
Ext.apply(EB.model['{$gridClass}'].window.create, EB.model['{$gridClass}'].window.config);
Ext.apply(EB.model['{$gridClass}'].window.update, EB.model['{$gridClass}'].window.config);