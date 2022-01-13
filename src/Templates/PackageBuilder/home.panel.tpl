// Define configuration
EB.panel.config = {
	id: '{$namespace|lower}-panel-{$a|lower}',
	border: true,
	baseCls: 'modx-formpanel',
	cls: 'container',
	items: [
		{
			html: _('{$namespace|lower}.home_title'),
			border: false,
			cls: 'modx-page-header'
		},
		{
			// Include MODx.Tabs component in the panel
			xtype: 'modx-tabs', 
			id: '{$namespace|lower}-tabs',
			defaults: { 
				border: false, 
				autoHeight: true 
			},
			border: true,
			listeners: {
				tabchange: function(_this, newTab) {
					// If the action is opening a tab
					// Clear it and return
					if (EB.tabAction === 'open') {
						EB.tabAction = '';
						return;
					}

					// If this is not ebField or ebRel
					// Ids are lowercase
					if (newTab.id.indexOf('ebfield') !== -1 || newTab.id.indexOf('ebrel') !== -1) {
						// Just return
						return;
					}

					// Get the index of the active tab
					var index = _this.items.findIndex('id', newTab.id);
					var keys = _this.items.keys || [];
					if (index >= 0) {
						// Hide all higher indexes
						for (var i = index+1; i < keys.length; i++) {
							_this.hideTabStripItem(i);
						}
					}
				}
			},
			items: [
				EB.panel.tabs[0]
			]
		}
	]
};

{* Include our panel overrides *}
{include file="Global/home.panel.overrides.js"}

/**
 * Create, register and extend out Panel
 * 
 * ExtraBuilder.panel.Index
 * extrabuilder-panel-index
 */
EB.constructExtendRegister(
	{$namespace}.panel, 
	'{$a}', 
	'{$namespace|lower}-panel-{$a|lower}', 
	EB.panel.config,
	MODx.Panel,
	EB.panel.overrides
);