<?php
/**
 * This model implements ListItem models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMListItemModel extends SPMObjectModelCollection {
	public function __construct() {
		parent::__construct( WOM_TYPE_LISTITEM );
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		if ( !( $obj instanceof WOMListItemModel ) ) return '';

		return $obj->getHeader() . $this->getSubInlineEditText( $obj, $prefix );
	}
}
