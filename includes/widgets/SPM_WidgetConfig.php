<?php

global $wgSPMHideEditTab, $wgSPMShowConnectorEditTab;
$wgSPMHideEditTab = true;
$wgSPMShowConnectorEditTab = false;


global $wgSPMIP, $wgAutoloadClasses;

// constants for special properties
define( 'SPM_WF_SP_HAS_TEMPLATE', 1 );
define( 'SPM_WF_SP_HAS_MULTIPLE_TEMPLATE', 2 );

define( 'SPM_WF_SP_HAS_DESCRIPTION', 5 );
define( 'SPM_WF_SP_HAS_DEFAULT', 6 );
define( 'SPM_WF_SP_ALLOWS_VALUE', 7 );

define( 'SPM_WF_SP_HAS_WIDGET_ATTRIBUTE', 3 );
define( 'SPM_WF_SP_HAS_NUM_RANGE', 10 );

define( 'SPM_WF_SP_HAS_RANGE_NAMESPACE', 11 );
define( 'SPM_WF_SP_HAS_RANGE_CATEGORY', 12 );
define( 'SPM_WF_SP_HAS_RANGE_PROPERTY', 13 );

define( 'SPM_WF_SP_HAS_UID_PREFIX', 14 );

define( 'SPM_WF_SP_HAS_USER_ACL', 15 );

// define( 'SPM_WF_SP_HAS_VIEW_NAME', 4 );

global $wgSPMWidgetDataTypes, $wgSPMWidgetViews, $wgSPMWidgetExtras;
$wgAutoloadClasses['SPMWidgetDataType']       =  $wgSPMIP . '/includes/widgets/datatype/SPMWidgetDataType.php';

$wgAutoloadClasses['SPMWidgetPageType']       =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_Page.php';
$wgAutoloadClasses['SPMWidgetFileType']       =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_File.php';
$wgAutoloadClasses['SPMWidgetMediaType']      =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_Media.php';
$wgAutoloadClasses['SPMWidgetStringType']     =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_String.php';
$wgAutoloadClasses['SPMWidgetEmailType']      =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_Email.php';
$wgAutoloadClasses['SPMWidgetURLType']        =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_URL.php';
$wgAutoloadClasses['SPMWidgetTextType']       =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_Text.php';
$wgAutoloadClasses['SPMWidgetDateType']       =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_Date.php';
$wgAutoloadClasses['SPMWidgetDateTimeType']   =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_DateTime.php';
$wgAutoloadClasses['SPMWidgetNumberType']     =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_Number.php';
$wgAutoloadClasses['SPMWidgetUidType']        =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_UID.php';
$wgAutoloadClasses['SPMWidgetWidgetType']     =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_Widget.php';

$wgAutoloadClasses['SMWPageDatatypeWikiPageValue']       =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_Page.php';
$wgAutoloadClasses['SMWFileDatatypeWikiPageValue']       =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_File.php';
$wgAutoloadClasses['SMWMediaDatatypeWikiPageValue']      =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_Media.php';
$wgAutoloadClasses['SMWWidgetDatatypeWikiPageValue']     =  $wgSPMIP . '/includes/widgets/datatype/SPM_WT_Widget.php';

$wgSPMWidgetDataTypes = array(
	'SPMWidgetPageType',
	'SPMWidgetFileType',
	'SPMWidgetMediaType',
	'SPMWidgetStringType',
	'SPMWidgetEmailType',
	'SPMWidgetURLType',
	'SPMWidgetTextType',
	'SPMWidgetDateType',
	'SPMWidgetDateTimeType',
	'SPMWidgetNumberType',
	'SPMWidgetUidType',
	'SPMWidgetWidgetType',
);
// Definitions
define( 'SPM_WT_TYPE_PAGE'           , 'page' );
define( 'SPM_WT_TYPE_FILE'           , 'file' );
define( 'SPM_WT_TYPE_MEDIA'          , 'media' );
define( 'SPM_WT_TYPE_STRING'         , 'string' );
define( 'SPM_WT_TYPE_EMAIL'          , 'email' );
define( 'SPM_WT_TYPE_URL'            , 'url' );
define( 'SPM_WT_TYPE_TEXT'           , 'text' );
define( 'SPM_WT_TYPE_DATE'           , 'date' );
define( 'SPM_WT_TYPE_DATETIME'       , 'datetime' );
define( 'SPM_WT_TYPE_UID'            , 'uid' );
define( 'SPM_WT_TYPE_NUMBER'         , 'number' );
define( 'SPM_WT_TYPE_WIDGET'         , 'widget' );

$wgAutoloadClasses['SPMWidgetView']         =  $wgSPMIP . '/includes/widgets/view/SPMWidgetView.php';
$wgAutoloadClasses['SPMWidgetTemplateView'] =  $wgSPMIP . '/includes/widgets/view/SPM_WV_Template.php';

$wgAutoloadClasses['SPMWidgetTableRowView']   =  $wgSPMIP . '/includes/widgets/view/SPM_WV_Tablerow.php';
$wgAutoloadClasses['SPMWidgetSectionView']    =  $wgSPMIP . '/includes/widgets/view/SPM_WV_Section.php';

$wgSPMWidgetViews = array(
	'SPMWidgetSectionView',
	'SPMWidgetTableRowView',
);
// Definitions
define( 'SPM_WV_TABLEROW'          , 'table row' );
define( 'SPM_WV_SECTION'           , 'section' );


$wgAutoloadClasses['SPMWidgetExtra']   =  $wgSPMIP . '/includes/widgets/extra/SPMWidgetExtra.php';

$wgAutoloadClasses['SPMWidgetExtraPlain']   =  $wgSPMIP . '/includes/widgets/extra/SPM_WG_Plain.php';
$wgSPMWidgetExtras = array(
	'SPMWidgetExtraPlain',
);
define( 'SPM_WG_PLAIN', 'plain' );
