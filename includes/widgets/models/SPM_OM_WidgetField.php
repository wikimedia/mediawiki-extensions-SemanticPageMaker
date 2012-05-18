<?php
/**
 * This model implements Widget field models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectModels
 *
 */

class SPMWidgetFieldModel extends WikiObjectModel {
	protected $m_label;
	protected $m_field;
	protected $m_property_name;
	protected $m_type;
	protected $m_possible_values;
	protected $m_params;

	public function __construct( $label, $field, $property_name, $type, $possible_values, $params ) {
		parent::__construct( 'widget_field' );
		$this->m_label = $label;
		$this->m_field = $field;
		$this->m_property_name = $property_name;
		$this->m_type = $type;
		$this->m_possible_values = $possible_values ? $possible_values : array();
		$this->m_params = $params;
	}

	public function getLabel() {
		return $this->m_label;
	}

	public function getField() {
		return $this->m_field;
	}

	public function getPropertyName() {
		return $this->m_property_name;
	}

	public function getType() {
		return $this->m_type;
	}

	public function getPossibleValues() {
		return $this->m_possible_values;
	}

	public function getParams() {
		return $this->m_params;
	}

	public function getWikiText() {
		return '';
	}

	protected function getXMLContent() {
		$possible_values = implode( ',', $this->m_possible_values );
		$multiple = $this->m_params['multiple'] ? 'true' : 'false';
		$optional = $this->m_params['optional'] ? 'true' : 'false';
		return "
<label><![CDATA[{$this->m_label}]]></label>
<field><![CDATA[{$this->m_field}]]></field>
<property_name><![CDATA[{$this->m_property_name}]]></property_name>
<type><![CDATA[{$this->m_type}]]></type>
<possible_values><![CDATA[{$possible_values}]]></possible_values>
<multiple><![CDATA[{$multiple}]]></multiple>
<optional><![CDATA[{$optional}]]></optional>
";
	}
}
