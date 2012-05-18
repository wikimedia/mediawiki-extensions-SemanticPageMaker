<?php
/**
 * @author ning
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	exit( 1 );
}

/**
 * Static class for accessing public static functions to generate and execute semantic queries
 * and to serialise their results.
 */
class SPMProcessor {
	private static $isObjectEditorRegistered = false;
	private static $editors = array();
	private static $base_editor;

	private static function setupObjectEditors() {
		if ( self::$isObjectEditorRegistered ) return;

		global $wgSPMObjectModels;
		foreach ( $wgSPMObjectModels as $e ) {
			$editor = new $e();
			self::$editors[$editor->getTypeID()] = $editor;
		}
		self::$base_editor = self::$editors[WOM_TYPE_TEXT];

		self::$isObjectEditorRegistered = true;
	}

	public static function getObjectEditor( WikiObjectModel $obj ) {
		$fname = 'SPMObjectModel::getObjectEditor (WOM)';
		wfProfileIn( $fname );

		if ( !self::$isObjectEditorRegistered ) {
			self::setupObjectEditors();
		}
		$id = $obj->getTypeID();
		if ( isset( self::$editors[$id] ) ) {
			$result = self::$editors[$id];
		} else {
			$result = self::$base_editor;
		}
		wfProfileOut( $fname );

		return $result;
	}

	public static function getEditorHtml( $obj, $name_prefix = 'spm_obj', &$onSubmit = '' ) {
		return self::getObjectEditor( $obj )->getEditorHtml( $obj, $name_prefix, $onSubmit );
	}

	public static function updateValues( $obj, $values ) {
		return self::getObjectEditor( $obj )->updateValues( $obj, $values );
	}

	public static function getInlineEditText( $obj, $prefix = '' ) {
		return self::getObjectEditor( $obj )->getInlineEditText( $obj, $prefix );
	}
}
