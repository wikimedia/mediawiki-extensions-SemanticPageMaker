<?php
/**
 * This model implements Table models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMTableModel extends SPMObjectModelCollection {
	public function __construct() {
		parent::__construct( WOM_TYPE_TABLE );
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		if ( !( $obj instanceof WOMTableModel ) ) return '';

		return "<div class='spm_inline_div' id='spm_inline_{$prefix}{$obj->getObjectID()}'>\n{| {$obj->getStyle()}\n {$this->getSubInlineEditText($obj, $prefix)}\n|}\n</div>";
	}
}
