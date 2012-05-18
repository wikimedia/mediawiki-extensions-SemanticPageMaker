<?php
/**
 * This file contains a static class for accessing functions for
 * widget utilities.
 *
 * @author dch
 */

class SPMWidgetDataTypeUtils {
	private static $isWidgetInitialized = false;
	static $datatypes = array();

	static function initialize() {
		if ( self::$isWidgetInitialized ) return;

		global $wgSPMWidgetDataTypes;
		foreach ( $wgSPMWidgetDataTypes as $t ) {
			$datatype = new $t();
			self::$datatypes[$datatype->getTypeID()] = $datatype;
		}

		self::$isWidgetInitialized = true;
	}
	public static function getDateType( $typeid ) {
		$fname = 'SPMWidgetDataTypeUtils::getDateType (SPM)';
		wfProfileIn( $fname );
		self::initialize();
		wfProfileOut( $fname );

		return self::$datatypes[$typeid];
	}
	public static function getDataTypeBySMWTypeID( $typeid ) {
		$fname = 'SPMWidgetDataTypeUtils::getDataTypeBySMWTypeID (SPM)';
		wfProfileIn( $fname );
		self::initialize();
		$dt_instance = null;
		foreach ( self::$datatypes as $dt ) {
			if ( $dt->getSMWTypeID() == $typeid ) {
				$dt_instance = $dt;
				break;
			}
		}
		wfProfileOut( $fname );

		return $dt_instance;
	}

	static function initProperties() {
		$fname = 'SPMWidgetDataTypeUtils::initProperties (SPM)';
		wfProfileIn( $fname );
		self::initialize();
		foreach ( self::$datatypes as $dt ) {
			$dt->initProperties();
		}
		wfProfileOut( $fname );
	}

	static function smwInitDatatypes() {
		$fname = 'SPMWidgetDataTypeUtils::initProperties (SPM)';
		wfProfileIn( $fname );
		self::initialize();
		foreach ( self::$datatypes as $dt ) {
			$dt->smwInitDatatypes();
		}
		wfProfileOut( $fname );

		return true;
	}
}
