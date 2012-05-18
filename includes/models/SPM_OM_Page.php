<?php
/**
 * This model implements Page models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMPageModel extends SPMObjectModelCollection {
	public function __construct() {
		parent::__construct( WOM_TYPE_PAGE );
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		if ( !( $obj instanceof WOMPageModel ) ) return '';

		return $this->getSubInlineEditText( $obj, $prefix );
	}
}