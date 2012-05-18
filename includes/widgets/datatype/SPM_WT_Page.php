<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 *
 */

class SMWPageDatatypeWikiPageValue extends SMWWikiPageValue {
	public function __construct( $typeid ) {
		parent::__construct( $typeid );
		switch ( $typeid ) {
			case '___wpc':
				$this->m_fixNamespace = NS_CATEGORY;
				break;
			case '___wpp':
				$this->m_fixNamespace = SMW_NS_PROPERTY;
				break;
		}
	}
}

class SPMWidgetPageType extends SPMWidgetDataType {
	public function __construct() {
		parent::__construct( SPM_WT_TYPE_PAGE );
	}

	public function initProperties() {
		global $wgSPMContLang;
		$wf_props = $wgSPMContLang->getPropertyLabels();

		if ( array_key_exists( SPM_WF_SP_HAS_RANGE_NAMESPACE, $wf_props ) )
			SMWPropertyValue::registerProperty( '___SPM_WF_RN', '_str', $wf_props[SPM_WF_SP_HAS_RANGE_NAMESPACE], true );
		if ( array_key_exists( SPM_WF_SP_HAS_RANGE_CATEGORY, $wf_props ) )
			SMWPropertyValue::registerProperty( '___SPM_WF_RC', '___wpc', $wf_props[SPM_WF_SP_HAS_RANGE_CATEGORY], true );
		if ( array_key_exists( SPM_WF_SP_HAS_RANGE_PROPERTY, $wf_props ) )
			SMWPropertyValue::registerProperty( '___SPM_WF_RP', '___wpp', $wf_props[SPM_WF_SP_HAS_RANGE_PROPERTY], true );

		// also initialize hardcoded English values, if it's a non-English-language wiki
		SMWPropertyValue::registerProperty( '___SPM_WF_RN_BACKUP', '_str', 'SPM has range namespace', true );
		SMWPropertyValue::registerProperty( '___SPM_WF_RC_BACKUP', '___wpc', 'SPM has range category', true );
		SMWPropertyValue::registerProperty( '___SPM_WF_RP_BACKUP', '___wpp', 'SPM has range property', true );
	}

	public function smwInitDatatypes() {
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			SMWDataValueFactory::registerDatatype( '___wpc', 'SMWPageDatatypeWikiPageValue', SMWDataItem::TYPE_WIKIPAGE );
			SMWDataValueFactory::registerDatatype( '___wpp', 'SMWPageDatatypeWikiPageValue', SMWDataItem::TYPE_WIKIPAGE );
			// in SMW 1.6, have to register default _wpg back, to SMWDataValueFactory::$mNewDataItemIds
			// could be a bug in SMW 1.6
			SMWDataValueFactory::registerDatatype( '_wpg', 'SMWWikiPageValue', SMWDataItem::TYPE_WIKIPAGE );
		} else {
			SMWDataValueFactory::registerDatatype( '___wpc', 'SMWPageDatatypeWikiPageValue' );
			SMWDataValueFactory::registerDatatype( '___wpp', 'SMWPageDatatypeWikiPageValue' );
		}
	}

	public function registerResourceModules() {
		global $wgResourceModules, $wgSPMIP, $wgSPMScriptPath;

		$moduleTemplate = array(
			'localBasePath' => $wgSPMIP,
			'remoteBasePath' => $wgSPMScriptPath,
			'group' => 'ext.wes.spm_dt'
		);

		$wgResourceModules['ext.wes.spm_dt.page'] = $moduleTemplate + array(
			'scripts' => array( 'scripts/wf_designer/widgets/datatype/page.js' ),
			'dependencies' => array( 'ext.jquery.fancybox' )
		);
	}

	public function addHTMLHeader() {
		global $wgOut, $wgSPMScriptPath;

		// MediaWiki 1.17 introduces the Resource Loader.
		$realFunction = array( 'SMWOutputs', 'requireResource' );
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
//			$wgOut->addModules('ext.wes.spm_dt.page');

			// FIXME: MW 1.17 resource loader cannot handle dynamic script inside lazy load scripts
			$wgOut->addModules( 'ext.jquery.fancybox' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/widgets/datatype/page.js"></script>' );
		} else {
			global $wgOut, $wgSPMScriptPath, $wgSPMWfFancyBoxIncluded;
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/widgets/datatype/page.js"></script>' );

			if ( !$wgSPMWfFancyBoxIncluded ) {
				$wgSPMWfFancyBoxIncluded = true;
				$wgOut->addLink( array(
							'rel'   => 'stylesheet',
							'type'  => 'text/css',
							'media' => 'screen, projection',
							'href'  => $wgSPMScriptPath . '/scripts/fancybox/jquery.fancybox-1.3.4.css'
						) );

				$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/fancybox/jquery.fancybox-1.3.4.pack.js"></script>' );
		    }
		}
	}

	public function getDesignerHtml( $title_name ) {
		global $smwgQMaxInlineLimit;
		$html = '
          <tr>
            <td>
              <label style="width:100px;">Default value
              </label>
              <input type="text" value="" id="spm_wf_field_default" style="margin: 2px 0px 0px 10px;">
              <div style="clear:both;"></div>
              <hr size="1" color="#b7ddf2" />
            </td>
          </tr>
          <tr>
            <td>
              <label style="text-align:left;">
              	<span style="margin-left:10px;">Allow values by</span>
              </label>
              <div style="clear:both;"></div>
<div id="wf_wd_page_tabs">
	<ul>
		<li><a href="#tabs-1">Enumeration</a></li>
		<li><a href="#tabs-2">Semantic query</a></li>
		<li><a href="#tabs-3">Special range</a></li>
	</ul>
	<div id="tabs-1">
              <textarea id="spm_wf_prop_allows"></textarea>
              <span class="small" style="text-align:left;width:300px;">
              Each line stands for one value. <br/>
              !!! will not take effect if Range is set</span>
              <div style="clear:both;"></div>
	</div>
	<div id="tabs-2">
              <span class="small" style="text-align:left;width:300px;">
              The result set of query => possible values
' . ( ( defined( 'SMW_HALO_VERSION' ) && version_compare( SMW_HALO_VERSION, '1.5', '>=' ) ) ? '
<br/><a id="spm_wf_page_qi">use query interface</a>' : '' ) . '
              </span>
              <textarea id="spm_wf_prop_query"></textarea>
              <span class="small" style="text-align:left;width:300px;">
              Each line stands for one query. <br/>
              Maximum ' . $smwgQMaxInlineLimit . ' results for each query. <br/>
              !!! will not take effect if Range is set
              </span>
              <div style="clear:both;"></div>
	</div>
	<div id="tabs-3">
              <span class="small" style="text-align:left;margin-left:10px;width:300px;">
              Values or page names has range => possible values</span>
              <input type="text" value="" id="spm_wf_ac_range" style="margin: 2px 0px 0px 10px; width:300px;"">
              <span class="small" style="text-align:left;margin-left:10px;width:300px;">
              !!! Not work with Enumeration / Query mode.</span>
              <div style="clear:both;"></div>
              <ul style="list-style-type:none;list-style-image:none;"><li>
              <label style="text-align: left;">Widget field
                <span class="small" style="width:270px">all the values of a widget field (property)<br/>
                format : "widget name"/"field name"
                </span>
              </label>
              <input type="radio" name="spm_wf_ac_range_type" value="property" style="width:auto;">
              </li><li>
              <label style="text-align: left;">Category
                <span class="small" style="width:270px">the names of all pages in a category</span>
              </label>
              <input type="radio" name="spm_wf_ac_range_type" value="category" style="width:auto;">
              </li><li>
              <label style="text-align: left;">Namespace
                <span class="small" style="width:270px">the names of all pages in a namespace</span>
              </label>
              <input type="radio" name="spm_wf_ac_range_type" value="namespace" style="width:auto;">
              </li></ul>
              <div style="clear:both;"></div>
	</div>
</div>
            </td>
          </tr> ';

		return $html;
	}

	public function getFieldSettings( $proptitle, $params ) {
		$settings = parent::getFieldSettings( $proptitle, $params );

		$allowquerys = '';
		if ( $proptitle->exists() ) {
			$pfs = WOMProcessor::getParserFunctions( $proptitle, 'wfallowsvalue' );
			if ( count( $pfs ) > 0 ) {
				// always use the first one
				foreach ( array_shift( $pfs )->getObjects() as $field ) {
					$text = $field->getWikiText();
					if ( $text { strlen( $text ) - 1 } == '|' ) $text = substr( $text, 0, strlen( $text ) - 1 );
					$allowquerys .= $text . '\n';
				}
			}
		}
		$settings .= $allowquerys . "\n";

		// FIXME: do not apply multiple range instances
		$range = '';
		$store = smwfGetStore();
		foreach ( array( '___SPM_WF_RN', '___SPM_WF_RC', '___SPM_WF_RP' ) as $ptxt ) {
			$property = SMWPropertyValue::makeProperty( $ptxt );
			if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
				$props = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), $property->getDataItem() );
			} else {
				$props = $store->getPropertyValues( $proptitle, $property );
			}
			foreach ( $props as $propvalue ) {
				if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
					$val = SMWCompatibilityHelpers::getDBkeysFromDataItem( $propvalue );
					if ( is_array( $val ) ) $val = $val[0];
				} else {
					$val = $propvalue->getShortWikiText();
				}
				switch( $ptxt ) {
					case '___SPM_WF_RN':
						$range = 'namespace:' . $val;
						break;
					case '___SPM_WF_RC':
						$range = 'category:' . $val;
						break;
					case '___SPM_WF_RP':
						$range = 'property:' . $val;
						break;
				}
			}
		}

		$settings .= $range . "\n";
		return $settings;
	}

	public function getPropertyWiki( &$params ) {
		$wiki = parent::getPropertyWiki( $params );

		$querys = explode( "\n", array_shift( $params ) );
		$first = true;
		foreach ( $querys as $q ) {
			$q = trim( $q );
			if ( $q == '' || $q == '|' ) continue;
			if ( $first ) {
				$wiki .= '
{{#wfallowsvalue:';
				$first = false;
			}
			$wiki .= $q . '|';
		}
		if ( !$first ) $wiki .= '}}';

		$range = trim( array_shift( $params ) );
		if ( $range != '' ) {
			$range = explode( ':', $range, 2 );

			// recreate wiki text, range will not work with enumeration or query
			$wiki = '';

			$typevalue = SMWDataValueFactory::newTypeIDValue( '__typ', SMWDataValueFactory::findTypeLabel( $this->getSMWTypeID() ) );
			$smwdatatype = SMWPropertyValue::makeProperty( '_TYPE' );
			$wiki = "[[{$smwdatatype->getWikiValue()}::" . SPMWidgetUtils::getPrefixedText( $typevalue ) . "]]\n";

			switch( $range[0] ) {
				case 'namespace':
					$typevalue = SMWDataValueFactory::newTypeIDValue( '_str', trim( $range[1] ) );
					$smwdatatype = SMWPropertyValue::makeProperty( '___SPM_WF_RN' );
					$wiki .= "[[{$smwdatatype->getWikiValue()}::{$typevalue->getWikiValue()}]]\n";
					break;
				case 'category':
					$typevalue = SMWDataValueFactory::newTypeIDValue( '___wpc', trim( $range[1] ) );
					$smwdatatype = SMWPropertyValue::makeProperty( '___SPM_WF_RC' );
					$wiki .= "[[{$smwdatatype->getWikiValue()}::" . SPMWidgetUtils::getPrefixedText( $typevalue ) . "]]\n";
					break;
				case 'property':
					$typevalue = SMWDataValueFactory::newTypeIDValue( '___wpp', trim( $range[1] ) );
					$smwdatatype = SMWPropertyValue::makeProperty( '___SPM_WF_RP' );
					$wiki .= "[[{$smwdatatype->getWikiValue()}::" . SPMWidgetUtils::getPrefixedText( $typevalue ) . "]]\n";
					break;
			}
		}
		return $wiki;
	}

	private function getUser() {
		global $wgUser;
		return ( $wgUser->isAnon() ) ? $wgUser->getName() : ( Title::newFromText( $wgUser->getName(), NS_USER )->getPrefixedText() );
	}

	public function getSampleWiki( $proptitle = null, $default_val = '' ) {
		$wiki = parent::getSampleWiki( $proptitle, $default_val );

		return ( trim( $wiki ) == '__ALWAYS_CURRENT_USER__' ) ? $this->getUser() : $wiki;
	}

	public function getFieldParameters( &$params ) {
		$default = array_shift( $params );
		return array( '___default' => ( trim( $default ) == '__ALWAYS_CURRENT_USER__' ) ? $this->getUser() : $default );
	}

	public function getEditorUI( $title,
			$id, $label, $tmpl_name, $field_name, $current_value,
			Title $proptitle,
			$extra_semdata = null,
			$params = array() ) {

		if ( $this->getDefaultValue( $proptitle, $extra_semdata ) == '__ALWAYS_CURRENT_USER__' ) {
			global $wgParser, $wgTitle, $wgUser;
			$current_value = $this->getUser();
		}

		return parent::getEditorUI( $title,
			$id, $label, $tmpl_name, $field_name, $current_value,
			$proptitle, $extra_semdata, $params );
	}

	protected function renderNonPossibleValuesField(
		&$js,
		$name, $current_value, $possible_values,
		Title $proptitle,
		$extra_semdata = null, $params = array() ) {

		$multiple = $params['multiple'];
		$optional = $params['optional'];

		// FIXME: range settings in $extra_semdata is not supported,
		// cannot get the intersection between property settings and extra connector definition
		$range = '';
		$store = smwfGetStore();
		foreach ( array( '___SPM_WF_RN', '___SPM_WF_RC', '___SPM_WF_RP' ) as $ptxt ) {
			$property = SMWPropertyValue::makeProperty( $ptxt );
			if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
				$props = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), $property->getDataItem() );
			} else {
				$props = $store->getPropertyValues( $proptitle, $property );
			}
			foreach ( $props as $propvalue ) {
				if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
					$val = SMWCompatibilityHelpers::getDBkeysFromDataItem( $propvalue );
					if ( is_array( $val ) ) $val = $val[0];
				} else {
					$val = $propvalue->getShortWikiText();
				}
				switch( $ptxt ) {
					case '___SPM_WF_RN':
						$range = '
spm_wf_field.data.push( {
	name : "' . $name . '",
	type : "ac_range",
	params : [ "namespace", "' . str_replace( '"', '\"', $val )  . '", ' . ( $multiple ? '","' : 'null' ) . ' ]
} );';
						break;
					case '___SPM_WF_RC':
						$range = '
spm_wf_field.data.push( {
	name : "' . $name . '",
	type : "ac_range",
	params : [ "category", "' . str_replace( '"', '\"', $val )  . '", ' . ( $multiple ? '","' : 'null' ) . ' ]
} );';
						break;
					case '___SPM_WF_RP':
						$ptitle = Title::newFromText( $val, SMW_NS_PROPERTY );
						if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
							$types = $store->getPropertyValues( new SMWDIWikiPage( $ptitle->getDBkey(), $ptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '_TYPE' )->getDataItem() );
						} else {
							$types = $store->getPropertyValues( $ptitle, SMWPropertyValue::makeProperty( '_TYPE' ) );
						}
						$type = 'relation';
						if ( count( $types ) > 0 ) {
							// FIXME: - more than one type not handled
							$type_id = SMWDataValueFactory::findTypeID( SPMUtils::getWikiValue( $types[0] ) );
							$type_instance = SMWDataValueFactory::newTypeIDValue( $type_id );
							if ( !( $type_instance instanceof SMWWikiPageValue ) ) {
								$type = 'attribute';
							}
						}
						$range = '
spm_wf_field.data.push( {
	name : "' . $name . '",
	type : "ac_range",
	params : [ "' . $type . '", "' . str_replace( '"', '\"', $val )  . '", ' . ( $multiple ? '","' : 'null' ) . ' ]
} );';
						break;
				}
			}
		}
		$js .= $range;

		$clazz = '';
		if ( $multiple ) $clazz = 'spm_wf_multi_val';
		if ( $optional ) $clazz .= ' spm_wf_optional_val';
		return '<input class="spm_wf_val ' . $clazz . '" name="' . $name .
'" type="text" value="' . str_replace( '"', '\"', str_replace( "\n", ' ', $current_value ) ) . '"/>';
	}

}
