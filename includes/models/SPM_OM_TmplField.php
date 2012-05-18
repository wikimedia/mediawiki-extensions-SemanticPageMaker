<?php
/**
 * This model implements Template Field models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMTemplateFieldModel extends SPMParameterModel {
	public function __construct() {
		parent::__construct();

		$this->m_typeid = WOM_TYPE_TMPL_FIELD;
	}

//	public function getInlineEditText(WikiObjectModel $obj, $prefix = '') {
//		if(!($obj instanceof WOMTemplateFieldModel)) return '';
//
//		$key = $obj->getKey();
//		return ( $key == '' ? "" : ($key . '=') ) .
//			$this->getSubInlineEditText($obj, $prefix) .
//			'|';
//	}
}
