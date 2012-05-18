<?php
/**
 * This model implements Property models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMNestPropertyModel extends SPMObjectModel {
	public function __construct() {
		parent::__construct( WOM_TYPE_NESTPROPERTY );
	}

	public function getEditorHtml( WikiObjectModel $obj, $name_prefix = 'spm_obj', &$onSubmit = '' ) {
		if ( !( $obj instanceof WOMNestPropertyModel ) ) return '';

		$html = '
<table>
<tr><th>Property Name:</th><td><input name="' . $name_prefix . '[prop]" type="text" size="70" value="' . str_replace( '"', '\"', $obj->getPropertyName() ) . '"/></td></tr>
<tr><th>Caption:</th><td><input name="' . $name_prefix . '[cap]" type="text" size="70" value="' . str_replace( '"', '\"', $obj->getCaption() ) . '"/></td></tr>
<tr><th>Property Value:</th><td>';
//		$html .= '<textarea name="' . $name_prefix . '[val]" rows="25" cols="70">' . htmlspecialchars($obj->getPropertyValue()) . '</textarea>';
		$html .= '<input name="' . $name_prefix . '[val]" type="text" size="70" value="' . str_replace( '"', '\"', $obj->getPropertyValue() ) . '"/>';
		$html .= '</td></tr>
</table>';

		return $html;
	}

	public function updateValues( WikiObjectModel $obj, $values ) {
		if ( !( $obj instanceof WOMPropertyModel ) ) return;

		$prop = SMWPropertyValue::makeUserProperty( $values['prop'] );
		$obj->setProperty( $prop );

		if ( version_compare ( SMW_VERSION, '1.6', '>=' ) ) {
			$obj->setSMWDataValue( SMWDataValueFactory::newPropertyObjectValue(
				$prop->getDataItem(), $values['val'], $values['cap'] ) );
		} else {
			$obj->setSMWDataValue( SMWDataValueFactory::newPropertyObjectValue(
				$prop, $values['val'], $values['cap'] ) );
		}
	}
}
