<?php
/**
 * This model implements Table models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMTableCellModel extends SPMObjectModelCollection {
	public function __construct() {
		parent::__construct( WOM_TYPE_TBL_CELL );
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		if ( !( $obj instanceof WOMTableCellModel ) ) return '';

		return "{$obj->getPrefix()}<div class='spm_inline_div' id='spm_inline_{$prefix}{$obj->getObjectID()}'>{$this->getSubInlineEditText($obj, $prefix)}</div>";
	}
}
