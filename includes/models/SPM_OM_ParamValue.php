<?php
/**
 * This model implements Parameter / Template_field value models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMParamValueModel extends SPMObjectModelCollection {
	public function __construct() {
		parent::__construct( WOM_TYPE_PARAM_VALUE );
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		if ( !( $obj instanceof WOMParamValueModel ) ) return '';

		// don't know what happens in parameter, just return common wiki text
		return $obj->getWikiText();
	}
}
