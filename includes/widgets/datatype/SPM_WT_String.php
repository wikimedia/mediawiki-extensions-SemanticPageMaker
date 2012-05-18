<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 *
 */

class SPMWidgetStringType extends SPMWidgetDataType {
	public function __construct() {
		parent::__construct( SPM_WT_TYPE_STRING );
	}

	public function getSMWTypeID() {
		return '_str';
	}

	private function getUser() {
		global $wgUser;
		return ( $wgUser->isAnon() ) ? $wgUser->getName() : ( Title::newFromText( $wgUser->getName(), NS_USER )->getText() );
	}

	public function getSampleWiki( $proptitle = null, $default_val = '' ) {
		$wiki = parent::getSampleWiki( $proptitle, $default_val );

		return ( trim( $wiki ) == '__ALWAYS_CURRENT_USER__' ) ? $this->getUser() : $wiki;
	}

	public function getFieldParameters( &$params ) {
		$default = array_shift( $params );
		return array( '___default' => ( trim( $default ) == '__ALWAYS_CURRENT_USER__' ) ? $this->getUser() : $default );
	}

	public function getEditorUI( $title,
			$id, $label, $tmpl_name, $field_name, $current_value,
			Title $proptitle,
			$extra_semdata = null,
			$params = array() ) {

		if ( $this->getDefaultValue( $proptitle, $extra_semdata ) == '__ALWAYS_CURRENT_USER__' ) {
			global $wgParser, $wgTitle, $wgUser;
			$current_value = $this->getUser();
		}

		return parent::getEditorUI( $title,
			$id, $label, $tmpl_name, $field_name, $current_value,
			$proptitle, $extra_semdata, $params );
	}
}
