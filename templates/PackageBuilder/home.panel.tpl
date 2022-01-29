// Define configuration
EB.panel.config = {
	id: '{$cmpNamespace}-panel-{$a|lower}',
	border: true,
	baseCls: 'modx-formpanel',
	cls: 'container',
	items: [
		{
			html: {if !$isV3}'<h3 style="font-weight:normal;font-size:18px;">'+{/if}_('{$cmpNamespace}.home_title'){if !$isV3}+'</h3>'{/if},
			border: false,
			cls: 'modx-page-header'
		},
		{
			// Include MODx.Tabs component in the panel
			xtype: 'modx-tabs', 
			id: '{$cmpNamespace}-tabs',
			defaults: { 
				border: false, 
				autoHeight: true 
			},
			border: true,
			listeners: {
				tabchange: function(_this, newTab) {
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
	{$phpNamespace}.panel, 
	'{$a}', 
	'{$cmpNamespace}-panel-{$a|lower}', 
	EB.panel.config,
	MODx.Panel,
	EB.panel.overrides
);