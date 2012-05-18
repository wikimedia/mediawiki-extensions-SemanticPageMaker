<?php
/**
 * This model implements Section models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMHTMLTagModel extends SPMObjectModelCollection {
	public function __construct() {
		parent::__construct( WOM_TYPE_HTMLTAG );
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		if ( !( $obj instanceof WOMHTMLTagModel ) ) return '';

//		return "<div class='spm_inline_div' id='spm_inline_{$prefix}{$obj->getObjectID()}'>{$obj->getWikiText()}</div>";
		$attr = '';
		foreach ( $obj->getAttributes() as $a => $v ) {
			$attr .= " {$a}={$v}";
		}

		return "<div class='spm_inline_div' id='spm_inline_{$prefix}{$obj->getObjectID()}'>" .
			"<{$obj->getName()}{$attr}>{$this->getSubInlineEditText( $obj, $prefix )}</{$obj->getName()}>" .
			"</div>";
	}
}
