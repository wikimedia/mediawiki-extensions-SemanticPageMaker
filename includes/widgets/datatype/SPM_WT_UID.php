<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 *
 */

class SPMWidgetUidType extends SPMWidgetDataType {
	public function __construct() {
		parent::__construct( SPM_WT_TYPE_UID );
	}

	public function smwInitDatatypes() {
		global $wgSPMContLang;
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			SMWDataValueFactory::registerDatatype( '___uid', 'SMWStringValue', SMWDataItem::TYPE_STRING, $wgSPMContLang->getDatatype( 'uid' ) );
			// in SMW 1.6, have to register default _str back, to SMWDataValueFactory::$mNewDataItemIds
			// could be a bug in SMW 1.6
			SMWDataValueFactory::registerDatatype( '_str', 'SMWStringValue', SMWDataItem::TYPE_STRING );
		} else {
			SMWDataValueFactory::registerDatatype( '___uid', 'SMWStringValue', $wgSPMContLang->getDatatype( 'uid' ) );
		}
	}

	public function initProperties() {
		global $wgSPMContLang;
		$wf_props = $wgSPMContLang->getPropertyLabels();

		if ( array_key_exists( SPM_WF_SP_HAS_UID_PREFIX, $wf_props ) )
			SMWPropertyValue::registerProperty( '___SPM_WF_UP', '_str', $wf_props[SPM_WF_SP_HAS_NUM_RANGE], true );

		// also initialize hardcoded English values, if it's a non-English-language wiki
		SMWPropertyValue::registerProperty( '___SPM_WF_UP_BACKUP', '_str', 'SPM has UID prefix', true );
	}

	public function getSMWTypeID() {
		return '___uid';
	}

	public function registerResourceModules() {
		global $wgResourceModules, $wgSPMIP, $wgSPMScriptPath;

		$moduleTemplate = array(
			'localBasePath' => $wgSPMIP,
			'remoteBasePath' => $wgSPMScriptPath,
			'group' => 'ext.wes.spm_dt'
		);

		$wgResourceModules['ext.wes.spm_dt.uid'] = $moduleTemplate + array(
			'scripts' => array( 'scripts/wf_designer/widgets/datatype/uid.js' ),
		);
	}

	public function addHTMLHeader() {
		global $wgOut, $wgSPMScriptPath;

		// FIXME: MW 1.17 resource loader cannot handle dynamic script inside lazy load scripts

//		// MediaWiki 1.17 introduces the Resource Loader.
//		$realFunction = array( 'SMWOutputs', 'requireResource' );
//		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
//			$wgOut->addModules('ext.wes.spm_dt.uid');
//		} else {
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/widgets/datatype/uid.js"></script>' );
//		}
	}

	public function getDesignerHtml( $title_name ) {
		$html = '
          <tr>
            <td>
              <input type="hidden" value="" id="spm_wf_field_default">
              <input type="hidden" value="" id="spm_wf_prop_allows">
              <label style="text-align:left;margin-left:10px;">
                UID prefix
                <span class="small">E.g., "ORDER-"</span>
              </label>
              <input type="text" value="" id="spm_wf_field_prefix" style="margin: 2px 0px 0px 10px;">
              <div style="clear:both;"></div>
            </td>
          </tr> ';

		return $html;
	}

	public function getFieldSettings( $proptitle, $params ) {
		$settings = parent::getFieldSettings( $proptitle, $params );

		$store = smwfGetStore();
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$uid_prefixes = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_UP' )->getDataItem() );
		} else {
			$uid_prefixes = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '___SPM_WF_UP' ) );
		}
		// get the first only
		$uid_prefix = '';
		if ( count( $uid_prefixes ) > 0 ) $uid_prefix = SPMUtils::getWikiValue( $uid_prefixes[0] );
		$settings .= $uid_prefix . "\n";

		return $settings;
	}

	public function getPropertyWiki( &$params ) {
		$wiki = parent::getPropertyWiki( $params );

		$uid_prefix = SMWPropertyValue::makeProperty( '___SPM_WF_UP' )->getWikiValue();
		$up = trim( array_shift( $params ) );
		if ( $up == '' ) return $wiki;
		$wiki .= "
* Has UID prefix: [[{$uid_prefix}::{$up}]]";

		return $wiki;
	}

	private function getPrefix( $proptitle = null ) {
		if ( $proptitle == null ) return '';

		$store = smwfGetStore();
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$uid_prefixes = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_UP' )->getDataItem() );
		} else {
			$uid_prefixes = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '___SPM_WF_UP' ) );
		}
		// get the first prefix only
		$uid_prefix = '';
		if ( count( $uid_prefixes ) > 0 ) $uid_prefix = SPMUtils::getWikiValue( $uid_prefixes[0] );
		return $uid_prefix;
	}
	public function getSampleWiki( $proptitle = null, $default_val = '' ) {
		return $this->getPrefix( $proptitle ) . '0001';
	}

	protected function getSampleWikiOnEmpty() {
		return 'UID-0001';
	}

	public function getEditorUI( $title,
			$id, $label,
			$tmpl_name, $field_name, $current_value,
			Title $proptitle,
			$extra_semdata = null,
			$params = array() ) {

		if ( $current_value == '' ) $current_value = SPMWidgetUidType::getId( $this->getPrefix( $proptitle ) );

		return parent::getEditorUI( $title,
			$id, $label, $tmpl_name, $field_name, $current_value,
			$proptitle, $extra_semdata, $params );
	}

	public function getEditorHtml( $title,
		$tmpl_name, $field_name, $current_value,
		Title $proptitle,
		$extra_semdata = null, $params = array(), $ajax = false ) {

		$name = str_replace( '"', '\"', "{$tmpl_name}[0][{$field_name}]" );
		return '<input class="spm_wf_val" readonly="readonly" name="' . $name .
'" type="text" value="' . str_replace( "\n", ' ', $current_value ) . '"/>';
	}

	static function getId( $prefix ) {
		global $wgUploadDirectory;
		$file = $wgUploadDirectory . '/uids';
		$text = file_get_contents( $file );
		if ( $text === FALSE ) $text = '';

		$ids = array();
		foreach ( explode( "\n", $text ) as $idline ) {
			$i = strrpos( $idline, '=' );
			if ( $i !== FALSE ) {
				$ids[substr( $idline, 0, $i )] = intval( substr( $idline, $i + 1 ) );
			}
		}
		if ( !isset( $ids[$prefix] ) ) $ids[$prefix] = 1;

		$id  = $ids[$prefix];
		$ids[$prefix] ++;

		$text = '';
		foreach ( $ids as $pre => $i ) {
			$text .= "$pre=$i\n";
		}
		file_put_contents( $file, $text );
		return $prefix . sprintf( "%04u", $id );
	}
}
