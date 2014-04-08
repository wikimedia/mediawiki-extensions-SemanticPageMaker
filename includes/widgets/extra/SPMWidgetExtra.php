<?php
/**
 * File holding abstract class SPMWidgetView, the base for all widget view in SPM.
 *
 * @author Ning
 *
 * @file
 * @ingroup SemanticPageMaker
 */

abstract class SPMWidgetExtra {
	protected $m_typeid;

	/**
	 * Array of error text messages. Private to allow us to track error insertion
	 * (PHP's count() is too slow when called often) by using $mHasErrors.
	 * @var array
	 */
	protected $mErrors = array();

	/**
	 * Boolean indicating if there where any errors.
	 * Should be modified accordingly when modifying $mErrors.
	 * @var boolean
	 */
	protected $mHasErrors = false;

	/**
	 * Constructor.
	 *
	 * @param string $typeid
	 */
	public function __construct( $typeid ) {
		$this->m_typeid = $typeid;
	}

// /// Set methods /////
	public function initializeOnSetupExtension() {}
	public function onAjaxAccess( $method, $params ) { return FALSE; }

// /// Get methods /////
	public function getTypeID() {
		return $this->m_typeid;
	}

	public function getName() {
		return $this->getTypeID();
	}

	public function getListString() {
		return $this->getName();
	}

	public abstract function getWidgetWikiHtml( $html );

	public function getWikiWidgetView( $wiki ) { return FALSE; }

	public function registerResourceModules() {}

	public function addHTMLHeader() {}

	/**
	 * Return TRUE if a value was defined and understood by the given type,
	 * and false if parsing errors occurred or no value was given.
	 */
	public function isValid() {
		return ( ( !$this->mHasErrors ) );
	}

	/**
	 * Return a string that displays all error messages as a tooltip, or
	 * an empty string if no errors happened.
	 */
	public function getErrorText() {
		if ( defined( 'SMW_VERSION' ) )
			return smwfEncodeMessages( $this->mErrors );

		return $this->mErrors;
	}

	/**
	 * Return an array of error messages, or an empty array
	 * if no errors occurred.
	 */
	public function getErrors() {
		return $this->mErrors;
	}
}
