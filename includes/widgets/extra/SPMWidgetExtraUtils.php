<?php
/**
 * This file contains a static class for accessing functions for
 * widget utilities.
 *
 * @author dch
 */

class SPMWidgetExtraUtils {

	private static $isWidgetInitialized = false;
	static $extras = array();

	static function initialize() {
		if ( self::$isWidgetInitialized ) return;

		global $wgSPMWidgetExtras;
		foreach ( $wgSPMWidgetExtras as $e ) {
			$extra = new $e();
			self::$extras[$extra->getTypeID()] = $extra;
		}

		self::$isWidgetInitialized = true;
	}

	public static function getExtra( $typeid ) {
		$fname = 'SPMWidgetExtraUtils::getExtra (SPM)';
		wfProfileIn( $fname );
		self::initialize();
		wfProfileOut( $fname );

		return self::$extras[$typeid];
	}

	public static function getExtraInstanceByTypeID( $typeid ) {
		$fname = 'SPMWidgetExtraUtils::getExtraInstanceByTypeID (SPM)';
		wfProfileIn( $fname );
		self::initialize();
		$extra_instance = null;
		foreach ( self::$extras as $e ) {
			if ( $e->getTypeID() == $typeid ) {
				$extra_instance = $e;
				break;
			}
		}
		wfProfileOut( $fname );

		return $extra_instance;
	}
}
