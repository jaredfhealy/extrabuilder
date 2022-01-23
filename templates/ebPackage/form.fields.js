// Define the shared window config
EB.model['{$gridClass}'].window.config = {

	// This is a wider form, override default 600 width
	width: 800,
	
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
            html: _('{$cmpNamespace}.{$gridClass|lower}.html_desc')
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
            items: [{
                columnWidth: 0.5,
                border: false,
                defaults: {
                    msgTarget: 'under',
                    anchor: '100%'
                },
                items: [
					{
						xtype: 'textfield',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.display"),
						name: 'display',
						anchor: '90%',
						value: EB.model['{$gridClass}'].fieldDefaults.display
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.package_key"),
						name: 'package_key',
						anchor: '90%',
						allowBlank: false,
						value: EB.model['{$gridClass}'].fieldDefaults.package_key
					},
					{
						xtype: 'label',
						html: _("{$cmpNamespace}.{$gridClass|lower}.package_desc"),
						cls: 'desc-under'
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.base_class"),
						name: 'base_class',
						anchor: '90%',
						allowBlank: false,
						value: EB.model['{$gridClass}'].fieldDefaults.base_class
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.platform"),
						name: 'platform',
						anchor: '90%',
						allowBlank: false,
						value: EB.model['{$gridClass}'].fieldDefaults.platform
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.default_engine"),
						name: 'default_engine',
						anchor: '90%',
						allowBlank: false,
						value: EB.model['{$gridClass}'].fieldDefaults.default_engine
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.version"),
						name: 'version',
						anchor: '90%',
						allowBlank: false,
						value: EB.model['{$gridClass}'].fieldDefaults.version
					}
				]
            },{
                columnWidth: 0.5,
                border: false,
                defaults: {
                    msgTarget: 'under',
                    anchor: '100%'
                },
                items: [
					{
						xtype: 'textfield',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.sortorder"),
						name: 'sortorder',
						anchor: '90%',
						value: 0
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.phpdoc_package"),
						name: 'phpdoc_package',
						anchor: '90%'
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.phpdoc_subpackage"),
						name: 'phpdoc_subpackage',
						anchor: '90%'
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.core_path"),
						name: 'core_path',
						anchor: '90%',
						value: EB.model['{$gridClass}'].fieldDefaults.core_path
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.assets_path"),
						name: 'assets_path',
						anchor: '90%',
						value: EB.model['{$gridClass}'].fieldDefaults.assets_path
					},
					{
						xtype: 'xcheckbox',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.lexicon"),
						name: 'lexicon',
						anchor: '90%',
					}
				]
            }]
        }
	]

};

// Apply the config to both window types
Ext.apply(EB.model['{$gridClass}'].window.create, EB.model['{$gridClass}'].window.config);
Ext.apply(EB.model['{$gridClass}'].window.update, EB.model['{$gridClass}'].window.config);