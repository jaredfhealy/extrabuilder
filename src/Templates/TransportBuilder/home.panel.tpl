// Define configuration
EB.panel.config = {
	id: '{$namespace|lower}-panel-{$a|lower}',
	border: true,
	baseCls: 'modx-formpanel',
	cls: 'container',
	items: [
		{
			html: _('{$namespace|lower}.transport_home_title'),
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
			items: [
				EB.panel.tabs[0]
			]
		}
	]
};

{* Include our panel overrides before registering *}
{include file="{$gridClass}/home.panel.overrides.js"}

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