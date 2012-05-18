<?php
/**
 * This model implements Text models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMTextModel extends SPMObjectModel {
	public function __construct() {
		parent::__construct( WOM_TYPE_TEXT );
	}

	public function updateValues( WikiObjectModel $obj, $values ) {
		if ( !( $obj instanceof WOMTextModel ) ) return '';

		$obj->setText( $values['val'] );
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		if ( !( $obj instanceof WOMTextModel ) ) return '';

		return $obj->getWikiText();
	}
}
