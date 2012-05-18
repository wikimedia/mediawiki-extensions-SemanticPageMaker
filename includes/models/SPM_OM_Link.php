<?php
/**
 * This model implements Link models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMLinkModel extends SPMObjectModel {
	public function __construct() {
		parent::__construct( WOM_TYPE_LINK );
	}

	public function getEditorHtml( WikiObjectModel $obj, $name_prefix = 'spm_obj', &$onSubmit = '' ) {
		if ( !( $obj instanceof WOMLinkModel ) ) return '';

		$html = '
<table>
<tr><th>Link:</th><td><input name="' . $name_prefix . '[val]" type="text" size="70" value="' . str_replace( '"', '\"', $obj->getLink() ) . '"/></td></tr>
<tr><th>Caption:</th><td><input name="' . $name_prefix . '[cap]" type="text" size="70" value="' . str_replace( '"', '\"', $obj->getCaption() ) . '"/></td></tr>
</table>';

		return $html;
	}

	public function updateValues( WikiObjectModel $obj, $values ) {
		if ( !( $obj instanceof WOMLinkModel ) ) return;

		$link = $values['val'];
		if ( preg_match( '/^(?:' . wfUrlProtocols() . ')/', $link ) ) {
			$link = str_replace( '[', '%5B', $link );
			$link = str_replace( ']', '%5D', $link );
			$link = str_replace( ' ', '%20', $link );
			$link = str_replace( '|', '%7C', $link );
		}
		$obj->setLink( $link );
		$obj->setCaption( $values['cap'] );
	}
}
