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
			name: 'object',
			anchor: '100%',
			listeners: {
				added: function(_this, ownerCt, index) {
					_this.setValue(EB.model['{$classDetail["parentClass"]}'].data.selectedId);
				}
			},
			hidden: true
		},
		{
			xtype: 'label',
            html: _('{$namespace|lower}.{$gridClass|lower}.html_desc')
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
						xtype: '{$namespace|lower}-combo-reltype',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.relation_type"),
						name: 'relation_type',
						hiddenName: 'relation_type',
						id: '{$gridClass|lower}-relation_type',
						anchor: '90%',
						emptyText: _("{$namespace|lower}.{$gridClass|lower}.relation_type_empty"),
						allowBlank: false,
						listeners: {
							select: function(_this, record, index) {
								// Parse the json data
								var data = JSON.parse(record.data.data);
								console.log(record);
			
								// Loop through the values and set the fields
								for (var key in data) {
									// Get the field
									var field = Ext.getCmp('{$gridClass|lower}-'+key);
									if (field) {
										field.setValue(data[key]);
									}
								}
							}
						}
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.alias"),
						name: 'alias',
						id: '{$gridClass|lower}-alias',
						anchor: '90%',
						allowBlank: false
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.local"),
						name: 'local',
						id: '{$gridClass|lower}-local',
						anchor: '90%',
						allowBlank: false
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.cardinality"),
						name: 'cardinality',
						id: '{$gridClass|lower}-cardinality',
						anchor: '90%',
						allowBlank: false
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
					},
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
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.foreign"),
						name: 'foreign',
						id: '{$gridClass|lower}-foreign',
						anchor: '90%',
						allowBlank: false
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.owner"),
						name: 'owner',
						id: '{$gridClass|lower}-owner',
						anchor: '90%',
						allowBlank: false
					}
				]
            }]
        }
	]

};

// Define the relationship type drop down
EB.model['{$gridClass}'].window.reltype = {

	store: new Ext.data.ArrayStore({
		idIndex: 0,
		fields: ['value', 'display', 'data'],
		data: [
			[
				'composite',
				'Composite (One to Many)',
				JSON.stringify({
					local: 'id',
					foreign: '',
					cardinality: 'many',
					owner: 'local',
				})
			],
			[
				'aggregate',
				'Aggregate (Many to One)',
				JSON.stringify({
					local: '',
					foreign: 'id',
					cardinality: 'one',
					owner: 'foreign',
				})
			]
		]
	}),
	mode: 'local',
	displayField: 'display',
	valueField: 'value'
}

// Create, register, extend
EB.constructExtendRegister(
	{$namespace}.combo, 
	'reltype', 
	'{$namespace|lower}-combo-reltype', 
	EB.model['{$gridClass}'].window.reltype,
	MODx.combo.ComboBox
);

// Apply the config to both window types
Ext.apply(EB.model['{$gridClass}'].window.create, EB.window.config);
Ext.apply(EB.model['{$gridClass}'].window.update, EB.window.config);