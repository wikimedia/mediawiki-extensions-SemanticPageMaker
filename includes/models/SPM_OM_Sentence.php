<?php
/**
 * This model implements Sentence models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMSentenceModel extends SPMObjectModelCollection {

	public function __construct() {
		parent::__construct( WOM_TYPE_SENTENCE );
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		if ( !( $obj instanceof WOMSentenceModel ) ) return '';

		// special case, \n served as enter in Wiki
		$text = $this->getSubInlineEditText( $obj, $prefix );
		$ret = '';
		$r = preg_match( '/\s+$/', $text, $m );
		if ( $r ) {
			$ret = $m[0];
			$text = substr( $text, 0, strlen( $text ) - strlen( $ret ) );
		}
		return ( $text == '' ) ? $ret : "<div class='spm_inline_div' id='spm_inline_{$prefix}{$obj->getObjectID()}'>{$text}</div>{$ret}";
	}
}
