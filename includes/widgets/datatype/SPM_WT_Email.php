<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 *
 */

class SPMWidgetEmailType extends SPMWidgetDataType {
	public function __construct() {
		parent::__construct( SPM_WT_TYPE_EMAIL );
	}

	public function getSMWTypeID() {
		return '_ema';
	}

	protected function getSampleWikiOnEmpty() {
		return 'who@server.com';
	}
}
