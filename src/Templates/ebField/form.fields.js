EB.model['{$gridClass}'].window.config =  {

	// Override the anchor since this is a larger form
	width: '800',
	autoHeight: true,
	
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
			id: '{$gridClass|lower}-classKey',
			anchor: '100%',
			hidden: true,
			value: '{$gridClass}'
		},
		{
			xtype: 'textfield',
			name: 'object',
			id: '{$gridClass|lower}-object',
			anchor: '100%',
			hidden: true,
			listeners: {
				added: function(_this, ownerCt, index) {
					_this.setValue(EB.model['{$classDetail["parentClass"]}'].data.selectedId);
				}
			}
		},
		{
			xtype: '{$gridClass|lower}-combo-quickselect',
			fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.quick_select"),
			name: 'quick_select',
			id: '{$gridClass|lower}-quick_select',
			anchor: '100%',
			emptyText: _("{$namespace|lower}.{$gridClass|lower}.quick_select_placeholder"),
			submitValue: false,
			listeners: {
				select: function(_this, record, index) {
					// Parse the json data
					var data = JSON.parse(record.data.value);

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
                columnWidth:.5,
                border: false,
                defaults: {
                    msgTarget: 'under',
                    anchor: '100%'
                },
                items: [
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.column_name"),
						name: 'column_name',
						id: '{$gridClass|lower}-column_name',
						anchor: '90%'
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.dbtype"),
						name: 'dbtype',
						id: '{$gridClass|lower}-dbtype',
						anchor: '90%'
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.precision"),
						name: 'precision',
						id: '{$gridClass|lower}-precision',
						anchor: '90%'
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.phptype"),
						name: 'phptype',
						id: '{$gridClass|lower}-phptype',
						anchor: '90%'
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.allownull"),
						name: 'allownull',
						id: '{$gridClass|lower}-allownull',
						anchor: '90%'
					}
				]
            },{
                columnWidth: .5,
                border: false,
                defaults: {
                    msgTarget: 'under',
                    anchor: '100%'
                },
                items: [
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.default"),
						name: 'default',
						id: '{$gridClass|lower}-default',
						anchor: '90%'
					},
					{
						xtype: '{$gridClass|lower}-combo-indexselect',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.index"),
						name: 'index',
						id: '{$gridClass|lower}-index',
						anchor: '90%'
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.attributes"),
						name: 'attributes',
						id: '{$gridClass|lower}-attributes',
						anchor: '90%'
					},
					{
						xtype: 'textfield',
						fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.sortorder"),
						name: 'sortorder',
						id: '{$gridClass|lower}-sortorder',
						anchor: '90%'
					}
				]
            }]
        },
		{
			xtype: 'label',
			html: "<hr style='width:50%;text-align:center;margin:40px auto;' /><h3>" + _("{$namespace|lower}.{$gridClass|lower}.less_common") + "</h3>"
		},
		{
			xtype: 'textfield',
			fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.generated"),
			name: 'generated',
			id: '{$gridClass|lower}-generated',
			anchor: '90%'
		},
		{
			xtype: 'textfield',
			fieldLabel: _("{$namespace|lower}.{$gridClass|lower}.extra"),
			name: 'extra',
			id: '{$gridClass|lower}-extra',
			anchor: '90%'
		}
	]

};

// Quick select dropdown
EB.model['{$gridClass}'].window.quickSelectConfig = {

	store: new Ext.data.ArrayStore({
		id: 0,
		fields: ['display', 'value'],
		data: [
			[
				'Small String 20 (varchar/string, 20, true, none)',
				JSON.stringify({
					dbtype: 'varchar',
					precision: 20,
					phptype: 'string',
					allownull: 'true'
				})
			],
			[
				'Medim String 50 (varchar/string, 50, true, none)',
				JSON.stringify({
					dbtype: 'varchar',
					precision: 50,
					phptype: 'string',
					allownull: 'true'
				})
			],
			[
				'Long String (varchar, 191, true, none)',
				JSON.stringify({
					dbtype: 'varchar',
					precision: 191,
					phptype: 'string',
					allownull: 'true'
				})
			],
			[
				'Positive Int [Foreign Key IDs, Sorting] (int/int, 10, false, none)',
				JSON.stringify({
					dbtype: 'int',
					precision: 10,
					phptype: 'integer',
					allownull: 'false',
					attributes: 'unsigned'
				})
			],
			[
				'Timestamp (int/timestamp, 20, false, 0)',
				JSON.stringify({
					dbtype: 'int',
					precision: 20,
					phptype: 'timestamp',
					allownull: 'false',
					default: 0
				})
			],
			[
				'Text [64KB] (text/string, true, none)',
				JSON.stringify({
					dbtype: 'text',
					phptype: 'string',
					allownull: 'true'
				})
			],
			[
				'Medium Text [16MB] (mediumtext/string, true, none)',
				JSON.stringify({
					dbtype: 'mediumtext',
					phptype: 'string',
					allownull: 'true'
				})
			],
			[
				'True/False [0/1] (tinyint/boolean, 1, false, 0)',
				JSON.stringify({
					dbtype: 'int',
					precision: 1,
					phptype: 'boolean',
					allownull: 'false',
					attributes: 'unsigned',
					default: 0
				})
			],
			[
				'Datetime (datetime, true, NULL)',
				JSON.stringify({
					dbtype: 'datetime',
					phptype: 'datetime',
					allownull: 'true',
					default: 'NULL'
				})
			],
			[
				'Array Data (text/array, true, none)',
				JSON.stringify({
					dbtype: 'text',
					phptype: 'array',
					allownull: 'true'
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
	'quickSelect', 
	'{$gridClass|lower}-combo-quickselect', 
	EB.model['{$gridClass}'].window.quickSelectConfig,
	MODx.combo.ComboBox
);

// Define the relationship type drop down
EB.model['{$gridClass}'].window.indexSelectConfig = {

	store: new Ext.data.ArrayStore({
		idIndex: 0,
		fields: ['value', 'display'],
		data: [
			[
				'',
				'-- Select --'
			],
			[
				'BTREE',
				'BTREE'
			],
			[
				'FULLTEXT',
				'FULLTEXT'
			]
		]
	}),
	mode: 'local',
	displayField: 'display',
	valueField: 'value',
	allowNull: true
}

// Create, register, extend the combo box
EB.constructExtendRegister(
	{$namespace}.combo, 
	'reltype', 
	'{$gridClass|lower}-combo-indexselect', 
	EB.model['{$gridClass}'].window.indexSelectConfig,
	MODx.combo.ComboBox
);

Ext.apply(EB.model['{$gridClass}'].window.create, EB.model['{$gridClass}'].window.config);
Ext.apply(EB.model['{$gridClass}'].window.update, EB.model['{$gridClass}'].window.config);