// Define configuration
EB.panel.config = {
	id: '{$cmpNamespace}-panel-{$a|lower}',
	border: true,
	baseCls: 'modx-formpanel',
	cls: 'container',
	items: [
		{
			html: {if !$isV3}'<h3 style="font-weight:normal;font-size:18px;">'+{/if}_('{$cmpNamespace}.transport_home_title'){if !$isV3}+'</h3>'{/if},
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
	{$phpNamespace}.panel, 
	'{$a}', 
	'{$cmpNamespace}-panel-{$a|lower}', 
	EB.panel.config,
	MODx.Panel,
	EB.panel.overrides
);