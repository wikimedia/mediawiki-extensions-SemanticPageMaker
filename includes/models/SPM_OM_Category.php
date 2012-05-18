<?php
/**
 * This model implements Category models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMCategoryModel extends SPMObjectModel {
	public function __construct() {
		parent::__construct( WOM_TYPE_CATEGORY );
	}

	public function getEditorHtml( WikiObjectModel $obj, $name_prefix = 'spm_obj', &$onSubmit = '' ) {
		if ( !( $obj instanceof WOMCategoryModel ) ) return '';

		$wiki = str_replace( '"', '\"', $obj->getName() );
		$html = '
<input name="' . $name_prefix . '[val]" type="text" size="70" value="' . $wiki . '"/>';

		return $html;
	}

	public function updateValues( WikiObjectModel $obj, $values ) {
		if ( !( $obj instanceof WOMCategoryModel ) ) return;

		$obj->setName( $values['val'] );
	}
}
