// START: New Grid
// Define all config objects and overrides for this grid
EB.model['{$gridClass}'].grid.config = {
	id: "{$cmpNamespace}-grid-{$gridClass|lower}",
	classKey: "{$gridClass}",
	url: {$phpNamespace}.config.connectorUrl,
	baseParams: { 
		action: "{if $isV3}{$phpNamespace}\\Processors\\{/if}ebGetList",
		classKey: "{$gridClass}",
		parentId: 0
	},
	save_action: '{if $isV3}{$phpNamespace}\\Processors\\{/if}ebUpdate',
	saveParams: {
		classKey: "{$gridClass}",
		parentId: 0
	},
	paging: true,
	autosave: true,
	fields: {array_keys($classDetail['fieldMeta'])|@json_encode nofilter},
	remoteSort: true,
	storeId: "{$cmpNamespace}-store-{$gridClass|lower}",
	columns: [],
	menuItems: [
		{
			text: _("{$cmpNamespace}.update_window_title"),
			handler:  function (btn, e) {
				var grid = Ext.getCmp("{$cmpNamespace}-grid-{$gridClass|lower}");
				if (grid) {
					grid.openWindow('update', '{$gridClass}', '{$cmpNamespace}-window-update-{$gridClass|lower}', btn, e);
				}
			}
		},'-','-',
		{
			text: _("{$cmpNamespace}.delete_record_menu"),
			handler: function() {
				var grid = Ext.getCmp("{$cmpNamespace}-grid-{$gridClass|lower}");
				if (grid) {
					grid.deleteRecord();
				}
			}
		}
	],
	tbar: [
		{
			text: _("{$cmpNamespace}.create_button"),
			cls: 'primary-button',
			handler: function (btn, e) {
				var grid = Ext.getCmp("{$cmpNamespace}-grid-{$gridClass|lower}");
				if (grid) {
					grid.openWindow('create', '{$gridClass}', '{$cmpNamespace}-window-create-{$gridClass|lower}', btn, e);
				}
			}
		},
		'->',
		{
			xtype: 'textfield',
			id: '{$cmpNamespace}-{$gridClass|lower}-search-input',
			emptyText: _("{$cmpNamespace}.search_general"),
			listeners: {
				change: function(_this, newValue, oldValue) {
					_this.scope.search(newValue);
				},
				render: function (_this) {
					// Add a KeyMap listener (html element, config, scope)
					new Ext.KeyMap(_this.getEl(), {
						key: Ext.EventObject.ENTER,
						fn: function() { return this.blur(); },
						scope: _this
					});
				}
			}
		},
		{
			text: _("{$cmpNamespace}.clear_search"),
			handler: function() {
				this.clearSearch();
			}
		}
	]
};

EB.model['{$gridClass}'].grid.overrides = {
	
    // Add a search function
    search: function (newValue) {
        var s = this.getStore();
        s.baseParams.search = newValue;
        this.getBottomToolbar().changePage(1);
    },

    // Function to clear search
    clearSearch: function() {
        Ext.getCmp('{$cmpNamespace}-{$gridClass|lower}-search-input').setValue("");
        var s = this.getStore();
        s.baseParams.search = "";
		s.lastOptions.params.search = "";
		s.lastOptions.params.start = 0;
		this.refresh();
    },

    // Define our context menu
    getMenu: function(_this) {
		// Register the context menu
		_this.addContextMenuItem(_this.menuItems);
    },

    // Delete a list and all child tasks
    deleteRecord: function() {
        // If we do not have a record, return
        if (!this.menu.record)
            return;

        // Confirm the action with a warning
        MODx.msg.confirm({
            title: _("{$cmpNamespace}.delete_record_title"),
            text: _("{$cmpNamespace}.delete_record_warning"),
            url: this.config.url,
            params: {
                action: "{if $isV3}{$phpNamespace}\\Processors\\{/if}ebDelete",
                id: this.menu.record.id,
				classKey: '{$gridClass}'
            },
            listeners: {
                success: {
                    fn: this.refresh,
                    scope: this
                }
            }
        });
    },

	// Open an update or create window
	openWindow: function (action, classKey, xtypeString, btn, e) {
		// Define the record data to show
		var record = {};
		if (this.menu.id && action == 'update') {
			var record = this.menu.record;
			record.classKey = classKey;
		}

		// Use the MODx object to load the window
		var ebModal = MODx.load({ 
			xtype: xtypeString,
			listeners: {
                success: { 
					fn:function() { 
						this.refresh(); 
					},
					scope:this 
				}
            }
		});
		
		// Clear the form if creating
		if (action === 'update' && this.menu.id) {
			ebModal.fp.getForm().reset();
			ebModal.fp.getForm().setValues(record);
		}

		// Show the window
		ebModal.show(e.target).center();
	}
};

{* Include our column overrides that are class specific *}
{include file="$gridClass/grid.columns.js"}

// Add all tab configs to an array
// Tabs after ebPackage are hidden
EB.panel.tabs.push({
    title: _('{$cmpNamespace}.{$gridClass|lower}.tab_title'),
	id: '{$cmpNamespace}-grid-{$gridClass|lower}-tab',
	classKey: '{$gridClass}',
    defaults: { autoHeight: true },
	{if $gridClass !== 'ebPackage' && $gridClass !== 'ebTransport'}tabCls: 'x-hide-display',{/if}
    items: [
        {
            html: '<p>'+_('{$cmpNamespace}.general_edit_msg')+'</p>',
            border: false,
            bodyCssClass: 'panel-desc'
        },
        {
            xtype: "{$cmpNamespace}-grid-{$gridClass|lower}",
            cls: "main-wrapper",
            preventRender: true
        }
    ]
});

EB.model['{$gridClass}'].window.create = {
	title: _("{$cmpNamespace}.create_window_title"),
	id: '{$cmpNamespace}-window-create-{$gridClass|lower}',
	//classKey: '{$gridClass}',
	url: {$phpNamespace}.config.connectorUrl,
	y: 130,
	modal: true,
	baseParams: {
		action: '{if $isV3}{$phpNamespace}\\Processors\\{/if}ebCreate',
	},
	fields: [
		{
			xtype: 'label',
			anchor: '100%',
			html: '<h3 style="text-align:center; margin:15px;">'
			+_("{$cmpNamespace}.window_missing_fields_html")+'</h3>'
		}
	],
	stateful: false,
	closeAction: 'close'
};

EB.model['{$gridClass}'].window.update = {
	title: _("{$cmpNamespace}.update_window_title"),
	id: '{$cmpNamespace}-window-update-{$gridClass|lower}',
	//classKey: '{$gridClass}',
	url: {$phpNamespace}.config.connectorUrl,
	y: 130,
	modal: true,
	baseParams: {
		action: '{if $isV3}{$phpNamespace}\\Processors\\{/if}ebUpdate',
	},
	fields: [
		{
			xtype: 'label',
			anchor: '100%',
			html: '<h3 style="text-align:center; margin:15px;">'
			+_("{$cmpNamespace}.window_missing_fields_html")+'</h3>'
		}
	],
	stateful: false,
	closeAction: 'close'
};

{* Include our create/update window fields that apply to both windows *}
{include file="$gridClass/form.fields.js"}

/**
 * Use our wrapper function to simplify loading and 
 * extending of each component.
 */
EB.load.{if $gridClass == 'ebPackage' || $gridClass == 'ebTransport'}extOnReady{else}asyncAfterReady{/if}.push(function () {
	// Grid
	EB.constructExtendRegister(
		{$phpNamespace}.grid, 
		'{$gridClass}', 
		'{$cmpNamespace}-grid-{$gridClass|lower}', 
		EB.model['{$gridClass}'].grid.config, 
		MODx.grid.Grid, 
		EB.model['{$gridClass}'].grid.overrides
	);

	// Create window
	EB.constructExtendRegister(
		{$phpNamespace}.window, 
		'create_{$gridClass}', 
		'{$cmpNamespace}-window-create-{$gridClass|lower}', 
		EB.model['{$gridClass}'].window.create, 
		MODx.Window
	);

	// Update window
	EB.constructExtendRegister(
		{$phpNamespace}.window, 
		'update_{$gridClass}', 
		'{$cmpNamespace}-window-update-{$gridClass|lower}', 
		EB.model['{$gridClass}'].window.update, 
		MODx.Window
	);
});
// END: Grid model