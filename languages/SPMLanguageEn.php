<?php
/**
 * @author ning
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	exit( 1 );
}

global $wgSPMIP;
include_once( $wgSPMIP . '/languages/SPMLanguage.php' );

class SPMLanguageEn extends SPMLanguage {

	protected $wContentMessages = array(

	);

	protected $m_Namespaces = array(
		NS_CATEGORY_WIDGET      => 'Category_widget',
		NS_CATEGORY_WIDGET_TALK => 'Category_widget_talk'
	);

	protected $m_SpecialProperties = array(
		// always start upper-case
		SPM_WF_SP_HAS_TEMPLATE    => 'SPM has template',
		SPM_WF_SP_HAS_MULTIPLE_TEMPLATE  => 'SPM has multiple template',

		SPM_WF_SP_HAS_DESCRIPTION => 'SPM has description',
		SPM_WF_SP_HAS_DEFAULT => 'SPM has default',
		SPM_WF_SP_ALLOWS_VALUE => 'SPM allows value',

		SPM_WF_SP_HAS_WIDGET_ATTRIBUTE => 'SPM has widget attribute',
		SPM_WF_SP_HAS_NUM_RANGE => 'SPM has number range',

		 SPM_WF_SP_HAS_RANGE_NAMESPACE => 'SPM has range namespace',
		 SPM_WF_SP_HAS_RANGE_CATEGORY => 'SPM has range category',
		 SPM_WF_SP_HAS_RANGE_PROPERTY => 'SPM has range property',

		 SPM_WF_SP_HAS_UID_PREFIX => 'SPM has UID prefix',

		 SPM_WF_SP_HAS_USER_ACL => 'SPM has user ACL',
	);

	protected $m_Datatypes = array(
		'image'  => 'Image',
		'media'  => 'Media',
		'widget' => 'Widget',
		'uid'    => 'Uid',
		'time'   => 'Datetime',
	);
	protected $wUserMessages = array(
	/*Messages for Object Model*/
		'objecteditor' => 'Object Editor',
		'widgetassembler' => 'Widget Assembler',
		'spm_editor' => 'Object Model',

		'spm_error_nowom' => 'WOM extension has to be installed, please install it first.',

		'spm_ajax_success' => 'Success!',
		'spm_ajax_fail' => 'Failed',

		'wiedit_tab'  => 'Inline Edit',

		'nstab-category_widget' => 'Category widget',
		'wfedit_tab' => 'Edit widget',
		'wcedit_tab' => 'Edit connector',
		'wf_editor' => 'Widget Editor',

		'wf_spm_connector' => 'Action connector(s)',
//		'wf_spm_connector_edit' => '<span class="editsection">[<a href="$1">edit</a>]</span>',
		'wf_spm_connector_edit' => ' [<a href="$1">edit</a>]',
		'wf_spm_preview' => 'Preview',
		'wf_spm_src_template' => "\n\n''click [$1 here] to edit the underlying template directly.''",
		'wf_spm_freetext' => "\n\n''Can add free text in page if necessary''",
		'wf_spm_parent' => "\n====== From parent widget [$1 $2] (not editable) ======\n",
		'wf_spm_current' => "\n====== Current widget ======\n",
		'wf_spm_create_widget' => "\n\nNo widget bound, just [$1 create one].",

		'wf_spm_hint_wfinput' => "''Input article name below to create a new article or to edit it if it already exists.''",

		'wf_wd_hint_wfedit' => "''You are about to edit the widget definition page for the [[:$1]].<br/>Please click on a field (an area with content) below edit it, or click on \"'''append new content'''\" to add a new field.''",

		'wf_spm_field_link' => "[[$1|$2]]$3",
		'wf_spm_field_description' => "$1",
		'wf_spm_hint_field_description' => "type: $1 . ",
		'wf_spm_hint_field_multiple' => "multiple. ",
		'wf_spm_hint_field_optional' => "optional. ",

		'wf_spm_err_widget_not_defined' => "widget is not defined, please specify the widget name [[$1|here]]. ",
		'wf_spm_err_not_support' => "not support for now, sorry",

		'wf_wc_html_exp' => '
<li class="spm_wf_exp"><b>Target:</b><br/>
<ul><li class="spm_wf_exp_target"><span>$1</span></li></ul>
<b>Expression:</b><br/>
<div class="spm_wf_exp_exp"><span>
$2
</span></div>
<b>Sources:</b> <br/>
<ol class="spm_wf_exp_srcs">$3
</ol>
</li>
		',
		'wf_wc_html_exp_src' => '
<li class="spm_wf_exp_src"><span>$1</span></li>',


		'spm_wd_dt_default' => 'Default value',
		'spm_wd_dt_default_help' => 'Default value of field',
		'spm_wd_dt_possible' => 'Possible values',
		'spm_wd_dt_possible_help' => 'Each line stands for one value',

		'spm_wd_v_optional' => 'Optional',
		'spm_wd_v_optional_help' => 'Is this element required?',
		'spm_wd_v_multiple' => 'Multiple',
		'spm_wd_v_multiple_help' => 'Multiple value select?',
		'spm_wd_v_editidx' => 'Editing index',
		'spm_wd_v_editidx_help' => 'Index to show in editor, start from "1"',

		'spm_wd_' => '',

		'wf_title' => 'Please fill in the details of $1',
	);
}


