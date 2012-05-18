<?php
/**
 * File holding abstract class WikiObjectModelCollection, the base for all object model in WOM.
 *
 * @author Ning
 *
 * @file
 * @ingroup WikiObjectEditors
 */

abstract class SPMObjectModelCollection extends SPMObjectModel {
	public function updateValues( WikiObjectModel $obj, $values ) {
		$root = $obj;
		while ( $root->getParent() != null ) {
			$root = $root->getParent();
		}

		// just parse the content,
		$new_obj = WOMProcessor::parseToWOM( $values['val'] );
		// insert before this object,
		$root->updatePageObject( $new_obj, $obj->getObjectID() );
	}

	protected function getSubInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		$text = '';
		foreach ( $obj->getObjects() as $o ) {
			$text .= SPMProcessor::getInlineEditText( $o, $prefix );
		}
		return $text;
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		return "<div class='spm_inline_div' id='spm_inline_{$prefix}{$obj->getObjectID()}'>" .
			$this->getSubInlineEditText( $obj, $prefix ) .
			"</div>";
	}
}
