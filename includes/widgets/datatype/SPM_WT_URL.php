<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 *
 */

class SPMWidgetURLType extends SPMWidgetDataType {
	public function __construct() {
		parent::__construct( SPM_WT_TYPE_URL );
	}

	public function getSMWTypeID() {
		return '_uri';
	}

	protected function getSampleWikiOnEmpty() {
		return 'http://host.com';
	}
}
