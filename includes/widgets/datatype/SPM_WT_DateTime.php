<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 *
 */

class SPMWidgetDateTimeType extends SPMWidgetDataType {
	public function __construct() {
		parent::__construct( SPM_WT_TYPE_DATETIME );
	}

	public function smwInitDatatypes() {
		global $wgSPMContLang;
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			SMWDataValueFactory::registerDatatype( '___dtm', 'SMWTimeValue', SMWDataItem::TYPE_TIME, $wgSPMContLang->getDatatype( 'time' ) );
			// in SMW 1.6, have to register default _str back, to SMWDataValueFactory::$mNewDataItemIds
			// could be a bug in SMW 1.6
			SMWDataValueFactory::registerDatatype( '_dat', 'SMWTimeValue', SMWDataItem::TYPE_TIME );
		} else {
			SMWDataValueFactory::registerDatatype( '___dtm', 'SMWTimeValue', $wgSPMContLang->getDatatype( 'time' ) );
		}
	}

	public function getSMWTypeID() {
		return '___dtm';
	}

	protected function getSampleWikiOnEmpty() {
		return '2012/12/22T00:00';
	}

	public function getSampleWiki( $proptitle = null, $default_val = '' ) {
		$wiki = parent::getSampleWiki( $proptitle, $default_val );

		return ( trim( $wiki ) == '__ALWAYS_NOW__' ) ? '{{CURRENTYEAR}}/{{CURRENTMONTH}}/{{CURRENTDAY2}}T{{CURRENTTIME}}' : $wiki;
	}

	public function getFieldParameters( &$params ) {
		$default = array_shift( $params );
		return array( '___default' => ( trim( $default ) == '__ALWAYS_NOW__' ) ? '{{CURRENTYEAR}}/{{CURRENTMONTH}}/{{CURRENTDAY2}}T{{CURRENTTIME}}' : $default );
	}

	public function getEditorUI( $title,
			$id, $label, $tmpl_name, $field_name, $current_value,
			Title $proptitle,
			$extra_semdata = null,
			$params = array() ) {

		if ( $this->getDefaultValue( $proptitle, $extra_semdata ) == '__ALWAYS_NOW__' ) {
			global $wgParser, $wgUser;
			$current_value = $wgParser->preprocess(
				'{{CURRENTYEAR}}/{{CURRENTMONTH}}/{{CURRENTDAY2}}T{{CURRENTTIME}}',
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
	type : "time",
	params : []
} );';

		return $html;
	}
}
