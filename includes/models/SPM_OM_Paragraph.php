<?php
/**
 * This model implements key value models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMParagraphModel extends SPMObjectModelCollection {
	public function __construct() {
		parent::__construct( WOM_TYPE_PARAGRAPH );
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		return "<div class='spm_inline_div' id='spm_inline_{$prefix}{$obj->getObjectID()}'>\n" .
			$this->getSubInlineEditText( $obj, $prefix ) .
			"\n</div>";
	}
}
