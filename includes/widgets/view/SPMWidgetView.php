<?php
/**
 * File holding abstract class SPMWidgetView, the base for all widget view in SPM.
 *
 * @author Ning
 *
 * @file
 * @ingroup SemanticPageMaker
 */

abstract class SPMWidgetView {
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

	public function registerResourceModules() {}

	public function addHTMLHeader() {}

	public function getDesignerHtml( $title_name ) {
		return '
          <tr>
            <td>
              <label>
                <span style="margin-left:10px;">' . wfMessage( 'spm_wd_v_editidx' )->escaped() . '</span>
                <span class="small" style="width:180px;">' . wfMessage( 'spm_wd_v_editidx_help' )->escaped() . '</span>
              </label>
              <input type="text" value="" id="spm_wf_view_editidx" style="margin: 2px 0px 0px 15px;width:20px;">
              <div style="clear:both;"></div>
              <label>' . wfMessage( 'spm_wd_v_optional' )->escaped() . '
                <span class="small">' . wfMessage( 'spm_wd_v_optional_help' )->escaped() . '</span>
              </label>
              <input type="checkbox" id="spm_wf_view_optional" style="width:auto;">
              <div style="clear:both;"></div>
              <label>' . wfMessage( 'spm_wd_v_multiple' )->escaped() . '
                <span class="small">' . wfMessage( 'spm_wd_v_multiple_help' )->escaped() . '</span>
              </label>
              <input type="checkbox" id="spm_wf_view_multiple" style="width:auto;">
              <div style="clear:both;"></div>
              <hr size="1" color="#b7ddf2" />
            </td>
          </tr> ';
	}

	public function getFieldSettings( $params ) {
		$settings = '';
		if ( isset( $params['optional'] ) && ( strtolower( $params['optional'] ) == 'true' ) ) {
			$settings .= 'optional|';
		}
		if ( isset( $params['multiple'] ) && ( strtolower( $params['multiple'] ) == 'true' ) ) {
			$settings .= 'multiple|';
		}
		if ( isset( $params['editidx'] ) ) {
			$idx = intval( trim( $params['editidx'] ) );
			if ( $idx >= 0 ) $settings .= 'editidx=' . $idx . '|';
		}
		return $settings;
	}

	public function getViewWiki( $params ) {
		return '';
	}

	abstract function getFieldHtml( $label, $title, $field, &$params, $extra_params, $freetext );

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
