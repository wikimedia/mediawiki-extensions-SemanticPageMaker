<?php
/**
 * This model implements Section models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMSectionModel extends SPMObjectModelCollection {
	public function __construct() {
		parent::__construct( WOM_TYPE_SECTION );
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		if ( !( $obj instanceof WOMSectionModel ) ) return '';

		return "<div class='spm_inline_div' id='spm_inline_{$prefix}{$obj->getObjectID()}'>\n{$obj->getHeaderText()}{$this->getSubInlineEditText($obj, $prefix)}</div>";
	}

	public function getEditorHtml( WikiObjectModel $obj, $name_prefix = 'spm_obj', &$onSubmit = '' ) {
		if ( !( $obj instanceof WOMSectionModel ) ) return '';

		$html = '
<h2>Section instance</h2>
<table>
<tr><th>Name:</th><td><input name="' . $name_prefix . '[name]" type="text" size="70" value="' . str_replace( '"', '\"', $obj->getName() ) . '"/></td></tr>
<tr><th>Level:</th><td><input name="' . $name_prefix . '[level]" type="text" size="10" value="' . $obj->getLevel() . '"/> (1 - 6)</td></tr>
</table>
<textarea name="' . $name_prefix . '[body]" rows="20" cols="70">' . htmlspecialchars( $obj->getContent() ) . '</textarea>
';

		return $html;
	}

	public function updateValues( WikiObjectModel $obj, $values ) {
		if ( !( $obj instanceof WOMSectionModel ) ) return;

		$obj->setName( $values['name'] );
		$obj->setLevel( $values['level'] );

		$new_obj = WOMProcessor::parseToWOM( $obj->getHeaderText() . $values['body'] );
		$root = $obj;
		while ( $root->getParent() != null ) {
			$root = $root->getParent();
		}

		$root->updatePageObject( $new_obj, $obj->getObjectID() );
	}
}
