// Define the shared window config
EB.model['{$gridClass}'].window.config = {

	// This is a wider form, override default 600 width
	width: '90%',
	boxMaxWidth: 1200,
	
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
            html: _('{$cmpNamespace}.{$gridClass|lower}.html_desc')
		},
		{
			xtype: 'label',
            html: '<hr style="width:60%;text-align:center;margin:20px auto;" />'
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
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.class"),
						name: 'class',
						id: '{$gridClass|lower}-class',
						anchor: '90%',
						allowBlank: false
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.table_name"),
						name: 'table_name',
						id: '{$gridClass|lower}-table_name',
						anchor: '90%'
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.extends"),
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
						fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.sortorder"),
						name: 'sortorder',
						anchor: '90%',
						value: EB.model['{$gridClass}'].fieldDefaults.sortorder
					}
				]
            }]
        },
		{
			xtype: 'textarea',
			fieldLabel: _("{$cmpNamespace}.{$gridClass|lower}.raw_xml"),
			name: 'raw_xml',
			anchor: '100%',
			height: 300
		},
		{
			xtype: 'label',
            html: '<br/><br/>'
		}
	]

};

// Apply the config to both window types
Ext.apply(EB.model['{$gridClass}'].window.create, EB.model['{$gridClass}'].window.config);
Ext.apply(EB.model['{$gridClass}'].window.update, EB.model['{$gridClass}'].window.config);