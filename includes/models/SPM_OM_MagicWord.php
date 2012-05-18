<?php
/**
 * This model implements magicword models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMMagicWordModel extends SPMObjectModel {
	public function __construct() {
		parent::__construct( WOM_TYPE_MAGICWORD );
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		if ( !( $obj instanceof WOMMagicWordModel ) ) return '';

		return "<div class='spm_inline_div' id='spm_inline_{$prefix}{$obj->getObjectID()}'>{$obj->getWikiText()}</div>";
	}

	public function getEditorHtml( WikiObjectModel $obj, $name_prefix = 'spm_obj', &$onSubmit = '' ) {
		if ( !( $obj instanceof WOMMagicWordModel ) ) return '';

		$html = '
<h2>MagicWord instance</h2>
<table>
<tr><th>Name:</th><td>
<select name="' . $name_prefix . '[magicword]">
';
		global $wgParser;
		if ( $wgParser->mVariables === null ) $wgParser->initialiseVariables();
		$magicwords = $wgParser->mVariables->getHash();
		$magicword = $obj->getMagicWord();
		foreach ( $magicwords[1] as $mw => $v ) {
			$html .= '<option value="' . str_replace( '"', '\"', $mw ) . '" ' . ( $magicword == $mw ? "selected" : "" ) . '>' . $mw . '</option>
';
		}
		$html .= '
</select>
</td></tr>
</table>
';

		return $html;
	}

	public function updateValues( WikiObjectModel $obj, $values ) {
		if ( !( $obj instanceof WOMMagicWordModel ) ) return;

		$obj->setMagicWord( $values['magicword'] );
	}
}
