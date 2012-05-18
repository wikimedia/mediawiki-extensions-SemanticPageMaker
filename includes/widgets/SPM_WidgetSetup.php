<?php
global $wgSPMIP, $wgAutoloadClasses;
global $wgSPMWidgetDataTypes, $wgSPMWidgetViews, $wgSPMWidgetExtras;

if ( defined( 'SMW_HALO_VERSION' ) && version_compare( SMW_HALO_VERSION, '1.5', '>=' ) ) {
	$wgAutoloadClasses['SPMWidgetExtraAsk'] = $wgSPMIP . '/includes/widgets/extra/SPM_WG_Ask.php';
	$wgSPMWidgetExtras[] = 'SPMWidgetExtraAsk';
	define( 'SPM_WG_ASK', 'ask' );
}
if ( defined( 'NS_WIDGET' ) ) {
	$wgAutoloadClasses['SPMWidgetExtraWidget']  =  $wgSPMIP . '/includes/widgets/extra/SPM_WG_Widget.php';
	$wgSPMWidgetExtras[] = 'SPMWidgetExtraWidget';
	define( 'SPM_WG_WIDGET', 'widget' );
}
