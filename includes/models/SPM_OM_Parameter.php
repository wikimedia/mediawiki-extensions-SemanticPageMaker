<?php
/**
 * This model implements key value models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMParameterModel extends SPMObjectModelCollection {
	public function __construct() {
		parent::__construct( WOM_TYPE_PARAMETER );
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		if ( !( $obj instanceof WOMParameterModel ) ) return '';

		// don't know what happens in parameter, just return common wiki text
		return $obj->getWikiText();
	}
}
