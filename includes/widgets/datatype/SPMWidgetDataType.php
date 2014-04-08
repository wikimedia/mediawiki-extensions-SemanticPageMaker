<?php
/**
 * File holding abstract class SPMWidgetDataType, the base for all widget data type in SPM.
 *
 * @author Ning
 *
 * @file
 * @ingroup SemanticPageMaker
 */

abstract class SPMWidgetDataType {
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

	public function initProperties() {}
	public function smwInitDatatypes() {}

// /// Set methods /////

// /// Get methods /////
	public function getTypeID() {
		return $this->m_typeid;
	}

	public function getName() {
		return $this->getTypeID();
	}

	public function getListString() {
		return "Common Wiki type(s)|{$this->getName()}";
	}

	public function getSMWTypeID() {
		return '_wpg';
	}

	public function getFieldParameters( &$params ) {
		$default = array_shift( $params );
		return array( '___default' => $default );
	}

	public function registerResourceModules() {}

	public function addHTMLHeader() {}

	public function getDesignerHtml( $title_name ) {
		return '
          <tr>
            <td>
              <label style="width:150px;">' . wfMsg( 'spm_wd_dt_default' ) . '
                <span class="small" style="width:150px;">' . wfMsg( 'spm_wd_dt_default_help' ) . '</span>
              </label>
              <input type="text" value="" id="spm_wf_field_default" style="margin: 2px 0px 0px 10px;">
              <div style="clear:both;"></div>
              <hr size="1" color="#b7ddf2" />
              <label style="text-align:left;">
              	<span style="margin-left:10px;">' . wfMsg( 'spm_wd_dt_possible' ) . '</span>
                <span class="small">' . wfMsg( 'spm_wd_dt_possible_help' ) . '</span>
              </label>
              <textarea id="spm_wf_prop_allows" style="margin: 2px 0px 0px 10px;"></textarea>
              <div style="clear:both;"></div>
              <hr size="1" color="#b7ddf2" />
            </td>
          </tr> ';
	}

	public function getFieldSettings( $proptitle, $params ) {
		$description = '';
		$store = smwfGetStore();
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$descriptions = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_HD' )->getDataItem() );
		} else {
			$descriptions = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '___SPM_WF_HD' ) );
		}
		if ( count( $descriptions ) > 0 ) {
			// always use the first description value
			$description = str_replace( "\n", ' ', SPMUtils::getWikiValue( $descriptions[0] ) );
		}
		$settings = $description . "\n";

		$default = '';
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$default_values = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_DF' )->getDataItem() );
		} else {
			$default_values = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '___SPM_WF_DF' ) );
		}
		foreach ( $default_values as $dv ) {
			if ( $dv == null ) continue;
			$default = SPMUtils::getWikiValue( $dv );
			break;
		}
		$default = str_replace( "\n", '\n', $default );
//		if( preg_match('/\{\{\{[^|]+(\|.*)?\}\}\}/', $params[2], $m) ) {
//			if(isset($m[1])) {
//				$default = str_replace( "\n", '\n', substr( $m[1], 1 ) );
//			}
//		}
		$settings .= $default . "\n";

		$allows = '';
		if ( $proptitle->exists() ) {
			$wom = WOMProcessor::getPageObject( $proptitle );
			global $smwgRecursivePropertyValues;
			if ( $smwgRecursivePropertyValues ) {
				$props = $wom->getObjectsByTypeID( WOM_TYPE_NESTPROPERTY );
			} else {
				$props = $wom->getObjectsByTypeID( WOM_TYPE_PROPERTY );
			}
			$smwallows = SMWPropertyValue::makeProperty( '_PVAL' );
			$allow_str = $smwallows->getWikiValue();
			foreach ( $props as $p ) {
				if ( $p->getPropertyName() == $allow_str ) {
					$allows .= $p->getPropertyValue() . '\n';
				}
			}
		}
		$settings .= $allows . "\n";

		$aclqueries = array();
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$acls = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_AC' )->getDataItem() );
		} else {
			$acls = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '___SPM_WF_AC' ) );
		}
		foreach ( $acls as $a ) {
			if ( $a == null ) continue;
			$v = explode( ';', SPMUtils::getWikiValue( $a ), 3 );
			if ( count( $v ) < 3 ) continue;
			// FIXME: n-ary uses semi-colon as separators
			$aclqueries[] = SPMWidgetUtils::decodeValue( str_replace( "\n", '', trim( $v[2] ) ) );
			break;
		}
		$settings .= implode( '\n', $aclqueries ) . "\n";

		return $settings;
	}

	public function getViewWiki( $proptitle, $params ) {
		return '';
	}

	protected function getPossibleValues( $semdata ) {
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$allowed_values = $semdata->getPropertyValues( SMWPropertyValue::makeProperty( '_PVAL' )->getDataItem() )
				+ $semdata->getPropertyValues( SMWPropertyValue::makeProperty( '___SPM_PVAL' )->getDataItem() );
		} else {
			$allowed_values = $semdata->getPropertyValues( SMWPropertyValue::makeProperty( '_PVAL' ) )
				+ $semdata->getPropertyValues( SMWPropertyValue::makeProperty( '___SPM_PVAL' ) );
		}
		$possible_values = array();
		foreach ( $allowed_values as $value ) {
			// HTML-unencode each value
			$wiki_value = html_entity_decode( SPMUtils::getWikiValue( $value ) );
			$possible_values[] = $wiki_value;
		}

		return count( $possible_values ) > 0 ? $possible_values : false;
	}
	public function getAllPossibleValues( Title $proptitle, $extra_semdata = null ) {
		$possible_values = false;

		$rev = Revision::newFromTitle( $proptitle );
		if ( $rev != null ) {
			global $wgParser, $wgUser;
			$options = ParserOptions::newFromUser( $wgUser );
//			global $wgParserConf;
//			$parser = new Parser( $wgParserConf );
			$output = $wgParser->parse( $rev->getText(), $proptitle, $options );
			SMWParseData::storeData( $output, $proptitle, true );

			$semdata = SMWParseData::getSMWdata( $wgParser );
//			$semdata = smwfGetStore()->getSemanticData($proptitle);
			$possible_values = $this->getPossibleValues( $semdata );
		}

		$possible_values2 = false;
		if ( $extra_semdata !== null ) {
			$possible_values2 = $this->getPossibleValues( $extra_semdata );
		}
		if ( $possible_values2 !== false ) {
			if ( $possible_values === false ) {
				$possible_values = $possible_values2;
			} else {
				// merge possible values
				$possible_values = array_intersect( $possible_values, $possible_values2 );
			}
		}

		return $possible_values;
	}
	protected function renderPossibleValuesHtml(
		&$js,
		$name, $current_value, $possible_values,
		Title $proptitle,
		$extra_semdata = null, $params = array() ) {

		if ( $possible_values === false ) return '';

		$multiple = $params['multiple'];
		$optional = $params['optional'];

		if ( !$optional && count( $possible_values ) == 1 ) {
			// one possible value only
			return '<input readonly="readonly" class="spm_wf_val" name="' . $name .
'" type="text" value="' . str_replace( "\n", ' ', $possible_values[0] ) . '"/>';
		}
		// dropdown or autocomplete?
		if ( !$multiple && count( $possible_values ) < 10 ) {
			$html = '<select style="width:150px;" class="spm_wf_val' . ( $optional ? ' spm_wf_optional_val' : '' ) . '" name="' . $name . '">' . "\n";
			$html .= '<option value="">' . ( $optional ? '' : '--- select ---' ) . '</optional>' . "\n";

			foreach ( $possible_values as $value ) {
				$html .= '<option value="' . str_replace( '"', '\"', $value ) . '" ' .
					( ( $current_value == $value || trim( $current_value ) == trim( $value ) ) ? 'selected="selected"' : '' ) . '>' .
					htmlspecialchars( $value ) . '</option>' . "\n";
			}
			$html .= '</select>' . "\n";
			return $html;
		}

		// FIXME: may cause performance issue if too many data, use ajax instead
		$autocomplete = array();
		foreach ( $possible_values as $val ) {
			$autocomplete[] = '"' . str_replace( '"', '\"', $val ) . '"';
		}
		$js .= '
spm_wf_field.data.push( {
	name : "' . $name . '",
	type : "ac",
	params : [ [' . implode( ',', $autocomplete ) . '], ' . ( $multiple ? '","' : 'null' ) . ' ]
} );';

		$clazz = '';
		if ( $multiple ) $clazz = 'spm_wf_multi_val';
		if ( $optional ) $clazz .= ' spm_wf_optional_val';
		return '<input class="spm_wf_val ' . $clazz . '" name="' . $name .
'" type="text" value="' . str_replace( "\n", ' ', $current_value ) . '"/>';
	}

	protected function renderNonPossibleValuesField(
		&$js,
		$name, $current_value, $possible_values,
		Title $proptitle,
		$extra_semdata = null, $params = array() ) {

		$multiple = $params['multiple'];
		$optional = $params['optional'];

		$clazz = '';
		if ( $multiple ) $clazz = 'spm_wf_multi_val';
		if ( $optional ) $clazz .= ' spm_wf_optional_val';
		return '<input class="spm_wf_val ' . $clazz . '" name="' . $name .
'" type="text" value="' . str_replace( '"', '\"', str_replace( "\n", ' ', $current_value ) ) . '"/>';
	}

	protected function getFieldValidationJs(
		$name, Title $proptitle,
		$extra_semdata = null, $params = array() ) {

		return '';
	}

	protected function isEditable( $proptitle ) {
		$store = smwfGetStore();

		$editable = true;
		global $wgUser;
		$name = "[[User:{$wgUser->getName()}]]";
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$acls = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_AC' )->getDataItem() );
		} else {
			$acls = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '___SPM_WF_AC' ) );
		}
		foreach ( $acls as $a ) {
			if ( $a == null ) continue;
			$v = explode( ';', SPMUtils::getWikiValue( $a ), 3 );
			if ( count( $v ) < 3 ) continue;
			// FIXME: n-ary uses semi-colon as separators
			$editable = false;
			$aclquery = $name . SPMWidgetUtils::decodeValue( str_replace( "\n", '', trim( $v[2] ) ) );
			$__queryobj = SMWQueryProcessor::createQuery( $aclquery, array() );
			$__queryobj->querymode = SMWQuery::MODE_INSTANCES;
			$__res = smwfGetStore()->getQueryResult( $__queryobj );
			$__resCount = $__res->getCount();
			if ( $__resCount > 0 ) {
				$editable = true;
				break;
			}
		}

		return $editable;
	}

	public function getEditorUI( $title,
			$id, $label, $tmpl_name, $field_name, $current_value,
			Title $proptitle,
			$extra_semdata = null,
			$params = array() ) {

		if ( !$this->isEditable( $proptitle ) ) {
			$name = str_replace( '"', '\"', "{$tmpl_name}[0][{$field_name}]" );
			return '<input class="spm_wf_val" name="' . $name .
'" type="hidden" value="' . str_replace( '"', '\"', str_replace( "\n", ' ', $current_value ) ) . '"/>';
		}

		$multiple = $params['multiple'];
		$optional = $params['optional'];

		$store = smwfGetStore();
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$descriptions = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_HD' )->getDataItem() );
		} else {
			$descriptions = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '___SPM_WF_HD' ) );
		}
		if ( count( $descriptions ) > 0 ) {
			// always use the first description value
			$description = SPMUtils::getWikiValue( $descriptions[0] );
		}
		$hint1 = wfMsgWikiHtml( 'wf_spm_field_description', $description );
		$hint2 = ( $multiple ? wfMsg( 'wf_spm_hint_field_multiple' ) : '' ) .
//				( $optional ? wfMsg( 'wf_spm_hint_field_optional' ) : '' ) .
				wfMsgWikiHtml( 'wf_spm_hint_field_description',
					SMWDataValueFactory::findTypeLabel( $this->getSMWTypeID() ),
					$this->getSampleWiki() );

		$sample = '';
		foreach ( SPMArticleUtils::parseTemplatePage( $params['raw'] ) as $tf ) {
			if ( !is_array( $tf ) ) {
				$sample .= $tf;
				continue;
			}
			$sample .= '{{{' . $tf['field'] . '|' . $this->getSampleWiki( $proptitle, $tf['default'] ) . '}}}';
		}
		// FIXME: hard code here
		if ( strpos( $params['tmpl_name'], 'table row' ) !== false ) {
			$sample = "{|class='spm_infobox'\n{$sample}\n|}";
		}

		global $wgParser, $wgUser;
		$options = ParserOptions::newFromUser( $wgUser );
		$html = $wgParser->parse( $sample, $title, $options );
		$sample = $html->getText();

		global $wgSPMScriptPath, $wgOut;
		return '
<tr><td>' . $wgOut->parse( "[[{$proptitle}|{$label}]]" . ( $optional ? '' : '*' ), true ) . '
' . ( $hint1 ? "<p>{$hint1}</p>":'' ) . '
</td><td>
<div id="' . $id . '" class="spm_wf_fld">
' . $this->getEditorHtml( $title, $tmpl_name, $field_name, $current_value, $proptitle, $extra_semdata, $params ) . '
</div>
</td></tr>';
//		return '
// <tr><td>
// <label>' . $wgOut->parse("[[{$proptitle}|{$label}]]" . ($optional ? '' : '*'), true) . '</label>
// <img class="popupIcon" src="' . $wgSPMScriptPath . '/skins/wf_editor/hint.png"/>
// <div class="tooltip">
//	<div class="label" style="width:310px;">
//		' . $hint1 . '
//		<b>settings</b>' . $hint2 . '
//		<b>Output sample</b>
//		<div class="spm_wf_formfldsmpl">
//			' . $sample . '
//		</div>
//	</div>
// </div>
// </td></tr>
// <tr><td>
// <div id="' . $id . '" class="spm_wf_fld">
// ' . $this->getEditorHtml($title, $tmpl_name, $field_name, $current_value, $proptitle, $extra_semdata, $params) . '
// </div>
// </td></tr>';
	}
	public function getDefaultValue( Title $proptitle, $extra_semdata = null ) {
		$default_value = '';
		if ( $extra_semdata != null ) {
			if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
				$default_values = $extra_semdata->getPropertyValues( SMWPropertyValue::makeProperty( '___SPM_WF_DF' )->getDataItem() );
			} else {
				$default_values = $extra_semdata->getPropertyValues( SMWPropertyValue::makeProperty( '___SPM_WF_DF' ) );
			}
			if ( count( $default_values ) > 0 ) {
				// always use the first description value
				$default_value = SPMUtils::getWikiValue( $default_values[0] );
			}
		}
		if ( $default_value == '' ) {
			$store = smwfGetStore();
			if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
				$default_values = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_DF' )->getDataItem() );
			} else {
				$default_values = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '___SPM_WF_DF' ) );
			}
			foreach ( $default_values as $dv ) {
				if ( $dv == null ) continue;
				$default_value = SPMUtils::getWikiValue( $dv );
				break;
			}
		}
		return $default_value;
	}
	public function getEditorHtml( $title,
		$tmpl_name, $field_name, $current_value,
		Title $proptitle,
		$extra_semdata = null, $params = array(), $ajax = false ) {

		$name = str_replace( '"', '\"', "{$tmpl_name}[0][{$field_name}]" );
		$possible_values = $this->getAllPossibleValues( $proptitle, $extra_semdata );

		if ( $current_value == '' ) {
			$current_value = $this->getDefaultValue( $proptitle, $extra_semdata );
		}

		$js = '';
		if ( $possible_values !== false ) {
			$html = $this->renderPossibleValuesHtml( $js, $name, $current_value, $possible_values, $proptitle, $extra_semdata, $params );
		} else {
			$html = $this->renderNonPossibleValuesField( $js, $name, $current_value, $possible_values, $proptitle, $extra_semdata, $params );
		}
		$js .= $this->getFieldValidationJs( $name, $proptitle, $extra_semdata, $params );

		if ( $js != '' ) {
			if ( $ajax ) {
				$html .= '
<script type="text/javascript">
spm_wf_field.data = [];
' . $js . '
jQuery(document).ready(function(){spm_wf_field.js.bindFields();});
</script>';
			} else {
				global $wgOut;
				$wgOut->addScript( '
<script type="text/javascript">
' . $js . '
</script>' );
			}
		}

		return $html;
	}

	public function getFieldValue( $val, $proptitle ) {
		return trim( $val );
	}

	protected function getSampleWikiOnEmpty() {
		return 'value';
	}

	public function getSampleWiki( $proptitle = null, $default_val = '' ) {
		if ( $default_val != '' ) return $default_val;
		if ( $proptitle != null ) {
			if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
				$semdata = smwfGetStore()->getSemanticData( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ) );
			} else {
				$semdata = smwfGetStore()->getSemanticData( $proptitle );
			}
			$possible_values = $this->getPossibleValues( $semdata );
			if ( $possible_values !== false ) return $possible_values[0];
		}

		return $this->getSampleWikiOnEmpty();
	}

	public function getPropertyWiki( &$params ) {
		$default = array_shift( $params );
		$description = array_shift( $params );
		$avs = explode( "\n", array_shift( $params ) );
		$allows_values = array();
		foreach ( $avs as $av ) {
			$allows_values[] = SPMWidgetUtils::encodeValue( $av );
		}
		$aqs = explode( "\n", array_shift( $params ) );
		$acl_queries = array();
		foreach ( $aqs as $aq ) {
			// FIXME: n-ary uses semi-colon as separators
			$acl_queries[] = SPMWidgetUtils::encodeValue( $aq );
		}

		$typevalue = SMWDataValueFactory::newTypeIDValue( '__typ', SMWDataValueFactory::findTypeLabel( $this->getSMWTypeID() ) );
		$smwdatatype = SMWPropertyValue::makeProperty( '_TYPE' );
		$wiki = "[[{$smwdatatype->getWikiValue()}::" . SPMWidgetUtils::getPrefixedText( $typevalue ) . "]]\n";

		if ( strlen( $description ) > 0 ) {
			$smwdesc = SMWPropertyValue::makeProperty( '___SPM_WF_HD' );
			$wiki .= "\n\n[[{$smwdesc->getWikiValue()}::{$description}]]\n";
		}

		if ( strlen( $default ) > 0 ) {
			$smwdesc = SMWPropertyValue::makeProperty( '___SPM_WF_DF' );
			$wiki .= "\n\n[[{$smwdesc->getWikiValue()}::{$default}]]\n";
		}

		$smwallows = SMWPropertyValue::makeProperty( '_PVAL' );
		foreach ( $allows_values as $allows ) {
			$allows = trim( $allows );
			if ( strlen( $allows ) > 0 ) $wiki .= "* [[{$smwallows->getWikiValue()}::{$allows}]]\n";
		}

		$smwacls = SMWPropertyValue::makeProperty( '___SPM_WF_AC' );
		foreach ( $acl_queries as $acl ) {
			$acl = trim( $acl );
			if ( strlen( $acl ) > 0 ) $wiki .= "* [[{$smwacls->getWikiValue()}::write;true;{$acl}]]\n";
		}
		return $wiki;
	}

	public function getFieldPropertyDefinition() { return ''; }

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
