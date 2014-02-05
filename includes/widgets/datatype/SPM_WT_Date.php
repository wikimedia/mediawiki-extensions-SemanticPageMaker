<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 *
 */

class SPMWidgetDateType extends SPMWidgetDataType {
	public function __construct() {
		parent::__construct( SPM_WT_TYPE_DATE );
	}

	public function getSMWTypeID() {
		return '_dat';
	}

//	public function getDesignerHtml($title_name) {
//		return '
//          <tr>
//            <td>
//              <label style="width:200px;text-align: left; margin-left: 10px;">Default value
//              </label>
//              <textarea id="spm_wf_field_default" style="margin: 2px 0px 0px 10px;"></textarea>
//              <input type="hidden" id="spm_wf_prop_allows"/>
//              <hr size="1" color="#b7ddf2" />
//              <label style="text-align:left;">
//              	<span style="margin-left:10px;">Always now</span>
//                <span class="small">Set date time now on editing</span>
//              </label>
//              <input type="checkbox" id="spm_wf_field_always_now" style="margin: 2px 0px 0px 10px;width:30px;"/>
//              <div style="clear:both;"></div>
//            </td>
//          </tr> ';
//	}

	protected function getSampleWikiOnEmpty() {
		return '2012/12/22';
	}

	public function getSampleWiki( $proptitle = null, $default_val = '' ) {
		$wiki = parent::getSampleWiki( $proptitle, $default_val );

		return ( trim( $wiki ) == '__ALWAYS_NOW__' ) ? '{{CURRENTYEAR}}/{{CURRENTMONTH}}/{{CURRENTDAY2}}' : $wiki;
	}

	public function getFieldParameters( &$params ) {
		$default = array_shift( $params );
		return array( '___default' => ( trim( $default ) == '__ALWAYS_NOW__' ) ? '{{CURRENTYEAR}}/{{CURRENTMONTH}}/{{CURRENTDAY2}}' : $default );
	}

	public function getEditorUI( $title,
			$id, $label, $tmpl_name, $field_name, $current_value,
			Title $proptitle,
			$extra_semdata = null,
			$params = array() ) {

		if ( $this->getDefaultValue( $proptitle, $extra_semdata ) == '__ALWAYS_NOW__' ) {
			global $wgParser, $wgUser;
			$current_value = $wgParser->preprocess(
				'{{CURRENTYEAR}}/{{CURRENTMONTH}}/{{CURRENTDAY2}}',
				$title,
				ParserOptions::newFromUser( $wgUser ) );
		}

		return parent::getEditorUI( $title,
			$id, $label, $tmpl_name, $field_name, $current_value,
			$proptitle, $extra_semdata, $params );
	}

	protected function renderNonPossibleValuesField(
		&$js,
		$name, $current_value, $possible_values,
		Title $proptitle,
		$extra_semdata = null, $params = array() ) {

		$html = parent::renderNonPossibleValuesField( $js,
			$name, $current_value, $possible_values,
			$proptitle, $extra_semdata, $params );

		$js .= '
spm_wf_field.data.push( {
	name : "' . $name . '",
	type : "date",
	params : []
} );';

		return $html;
	}
}
