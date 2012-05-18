<?php
/**
 * This file contains a static class for accessing functions for
 * widget utilities.
 *
 * @author dch
 */

class SPMWidgetViewUtils {

	private static $isWidgetInitialized = false;
	static $views = array();

	private static function getReservedCategoryTemplates() {
		$dbr = wfGetDB( DB_SLAVE, 'category' );

		$title = Title::newFromText( SPMWidgetUtils::$widgetViewingTemplatesCategory, NS_CATEGORY );

		$res = $dbr->select(
			array( 'page', 'categorylinks', 'category' ),
			array( 'page_title' ),
			array( 'page_namespace' => NS_TEMPLATE, 'cl_to' => $title->getDBkey() ),
			__METHOD__,
			array(),
			array( 'categorylinks'  => array( 'INNER JOIN', 'cl_from = page_id' ),
				'category' => array( 'LEFT JOIN', 'cat_title = page_title AND page_namespace = ' . NS_CATEGORY ) )
		);
		$tmpls = array();
		while ( $x = $dbr->fetchObject ( $res ) ) {
			$tmpls[] = Title::makeTitle( $x->page_namespace, $x->page_title )->getText();
		}
		return $tmpls;
	}
	static function initialize() {
		if ( self::$isWidgetInitialized ) return;

		global $wgSPMWidgetViews;
		foreach ( $wgSPMWidgetViews as $v ) {
			$view = new $v();
			self::$views[$view->getTypeID()] = $view;
		}

		$tmpls = self::getReservedCategoryTemplates();
		foreach ( $tmpls as $tmpl ) {
			SPMWidgetUtils::$boundTemplates[] = $tmpl;
			SPMWidgetUtils::$widgetTemplates[$tmpl] = $tmpl;
			self::$views[$tmpl] = new SPMWidgetTemplateView( $tmpl );
		}

		self::$isWidgetInitialized = true;
	}

	public static function getView( $typeid ) {
		$fname = 'SPMWidgetViewUtils::getView (SPM)';
		wfProfileIn( $fname );
		self::initialize();
		wfProfileOut( $fname );

		return self::$views[$typeid];
	}

	public static function getViewInstanceByTypeID( $typeid ) {
		$fname = 'SPMWidgetViewUtils::getViewInstanceByTypeID (SPM)';
		wfProfileIn( $fname );
		self::initialize();
		$view_instance = null;
		foreach ( self::$views as $v ) {
			if ( $v->getTypeID() == $typeid ) {
				$view_instance = $v;
				break;
			}
		}
		wfProfileOut( $fname );

		return $view_instance;
	}
}
