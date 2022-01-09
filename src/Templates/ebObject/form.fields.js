// Define the shared window config
EB.window.config = {
	
	// Title to override the missing message
	title: _("{$namespace|lower}.create_window_title"),

	// This is a wider form, override default 600 width
	width: 800,
	
	// Fields to apply to our form
	fields: [
		{
			xtype: 'textfield',
			name: 'id',
			id: '{$gridClass|lower}-id',
			anchor: '100%',
			hidden: true
		},
		{
			xtype: 'textfield',
			name: 'classKey',
			id: '{$gridClass|lower}-classKey',
			anchor: '100%',
			hidden: true,
			value: '{$gridClass}'
		},
		{
			xtype: 'textfield',
			name: 'package',
			id: '{$gridClass|lower}-package',
			anchor: '100%',
			hidden: true,
			listeners: {
				added: function(_this, ownerCt, index) {
					_this.setValue(EB.model['{$classDetail["parentClass"]}'].data.selectedId);
				}
			}
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
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.class"),
						name: 'class',
						id: '{$gridClass|lower}-class',
						anchor: '90%',
						allowBlank: false
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.table_name"),
						name: 'table_name',
						id: '{$gridClass|lower}-table_name',
						anchor: '90%',
						allowBlank: false
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.extends"),
						name: 'extends',
						id: '{$gridClass|lower}-extends',
						anchor: '90%',
						allowBlank: false,
						value: EB.model['{$gridClass}'].fieldDefaults.extends
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
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.sortorder"),
						name: 'sortorder',
						anchor: '90%',
						value: EB.model['{$gridClass}'].fieldDefaults.sortorder
					}
				]
            }]
        },
		{
			xtype: 'textarea',
			fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.raw_xml"),
			name: 'raw_xml',
			anchor: '90%'
		}
	]

};

// Apply the config to both window types
Ext.apply(EB.model['{$gridClass}'].window.create, EB.window.config);
Ext.apply(EB.model['{$gridClass}'].window.update, EB.window.config);