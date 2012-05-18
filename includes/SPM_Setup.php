<?php

global $wgSPMIP, $wgAutoloadClasses;

// POM Type
$wgAutoloadClasses['SPMObjectModel']           =  $wgSPMIP . '/includes/models/SPM_ObjectModel.php';
$wgAutoloadClasses['SPMObjectModelCollection'] =  $wgSPMIP . '/includes/models/SPM_ObjectModelCollection.php';

$wgAutoloadClasses['SPMCategoryModel']         =  $wgSPMIP . '/includes/models/SPM_OM_Category.php';
$wgAutoloadClasses['SPMPageModel']             =  $wgSPMIP . '/includes/models/SPM_OM_Page.php';
$wgAutoloadClasses['SPMTemplateModel']         =  $wgSPMIP . '/includes/models/SPM_OM_Template.php';
$wgAutoloadClasses['SPMParserFunctionModel']   =  $wgSPMIP . '/includes/models/SPM_OM_ParserFunction.php';
$wgAutoloadClasses['SPMParameterModel']        =  $wgSPMIP . '/includes/models/SPM_OM_Parameter.php';
$wgAutoloadClasses['SPMParamValueModel']       =  $wgSPMIP . '/includes/models/SPM_OM_ParamValue.php';
$wgAutoloadClasses['SPMParagraphModel']        =  $wgSPMIP . '/includes/models/SPM_OM_Paragraph.php';
$wgAutoloadClasses['SPMTemplateFieldModel']    =  $wgSPMIP . '/includes/models/SPM_OM_TmplField.php';
$wgAutoloadClasses['SPMPropertyModel']         =  $wgSPMIP . '/includes/models/SPM_OM_Property.php';
$wgAutoloadClasses['SPMNestPropertyModel']     =  $wgSPMIP . '/includes/models/SPM_OM_NestProperty.php';
$wgAutoloadClasses['SPMTextModel']             =  $wgSPMIP . '/includes/models/SPM_OM_Text.php';
$wgAutoloadClasses['SPMLinkModel']             =  $wgSPMIP . '/includes/models/SPM_OM_Link.php';
$wgAutoloadClasses['SPMSectionModel']          =  $wgSPMIP . '/includes/models/SPM_OM_Section.php';
$wgAutoloadClasses['SPMSentenceModel']         =  $wgSPMIP . '/includes/models/SPM_OM_Sentence.php';
$wgAutoloadClasses['SPMListItemModel']         =  $wgSPMIP . '/includes/models/SPM_OM_ListItem.php';
$wgAutoloadClasses['SPMTableModel']            =  $wgSPMIP . '/includes/models/SPM_OM_Table.php';
$wgAutoloadClasses['SPMTableCellModel']        =  $wgSPMIP . '/includes/models/SPM_OM_TblCell.php';
$wgAutoloadClasses['SPMMagicWordModel']        =  $wgSPMIP . '/includes/models/SPM_OM_MagicWord.php';
$wgAutoloadClasses['SPMHTMLTagModel']          =  $wgSPMIP . '/includes/models/SPM_OM_HTMLTag.php';

// Definitions, reuse type definition from WOM extensions


global $wgSPMObjectModels;
$wgSPMObjectModels = array(
		'SPMCategoryModel',
		'SPMPageModel',
		'SPMTemplateModel',
		'SPMParserFunctionModel',
		'SPMParameterModel',
		'SPMParamValueModel',
		'SPMParagraphModel',
		'SPMTemplateFieldModel',
		'SPMPropertyModel',
		'SPMNestPropertyModel',
		'SPMTextModel',
		'SPMLinkModel',
		'SPMSectionModel',
		'SPMSentenceModel',
		'SPMListItemModel',
		'SPMTableModel',
		'SPMTableCellModel',
		'SPMMagicWordModel',
		'SPMHTMLTagModel',
);
