<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 *
 */

class SMWWidgetDatatypeWikiPageValue extends SMWWikiPageValue {
	public function __construct( $typeid ) {
		parent::__construct( $typeid );
		switch ( $typeid ) {
			case '___wdg': // has type
				$this->m_fixNamespace = NS_CATEGORY_WIDGET;
				break;
			case '___wsw':
				$this->m_fixNamespace = NS_CATEGORY_WIDGET;
				break;
		}
	}
}

class SPMWidgetWidgetType extends SPMWidgetDataType {
	public function __construct() {
		parent::__construct( SPM_WT_TYPE_WIDGET );
	}

	public function getListString() {
		return $this->getTypeID();
	}

	public function smwInitDatatypes() {
		global $wgSPMContLang;
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			SMWDataValueFactory::registerDatatype( '___wdg', 'SMWWidgetDatatypeWikiPageValue', SMWDataItem::TYPE_WIKIPAGE, $wgSPMContLang->getDatatype( 'widget' ) ); // widget
			SMWDataValueFactory::registerDatatype( '___wsw', 'SMWWidgetDatatypeWikiPageValue', SMWDataItem::TYPE_WIKIPAGE );
			// in SMW 1.6, have to register default _wpg back, to SMWDataValueFactory::$mNewDataItemIds
			// could be a bug in SMW 1.6
			SMWDataValueFactory::registerDatatype( '_wpg', 'SMWWikiPageValue', SMWDataItem::TYPE_WIKIPAGE );
		} else {
			SMWDataValueFactory::registerDatatype( '___wdg', 'SMWWidgetDatatypeWikiPageValue', $wgSPMContLang->getDatatype( 'widget' ) ); // widget
			SMWDataValueFactory::registerDatatype( '___wsw', 'SMWWidgetDatatypeWikiPageValue' );
		}
	}

	public function getSMWTypeID() {
		return '___wdg';
	}

	public function initProperties() {
		global $wgSPMContLang;
		$wf_props = $wgSPMContLang->getPropertyLabels();

		if ( array_key_exists( SPM_WF_SP_HAS_WIDGET_ATTRIBUTE, $wf_props ) )
			SMWPropertyValue::registerProperty( '___SPM_WF_WA', '___wsw', $wf_props[SPM_WF_SP_HAS_WIDGET_ATTRIBUTE], true );

		// also initialize hardcoded English values, if it's a non-English-language wiki
		SMWPropertyValue::registerProperty( '___SPM_WF_WA_BACKUP', '___wsw', 'SPM has widget attribute', true );
	}

	public function registerResourceModules() {
		global $wgResourceModules, $wgSPMIP, $wgSPMScriptPath;

		$moduleTemplate = array(
			'localBasePath' => $wgSPMIP,
			'remoteBasePath' => $wgSPMScriptPath,
			'group' => 'ext.wes.spm_dt'
		);

		$wgResourceModules['ext.wes.spm_dt.widget'] = $moduleTemplate + array(
			'scripts' => array( 'scripts/wf_designer/widgets/datatype/widget.js' ),
		);
	}

	public function addHTMLHeader() {
		global $wgOut, $wgSPMScriptPath;

		// FIXME: MW 1.17 resource loader cannot handle dynamic script inside lazy load scripts

//		// MediaWiki 1.17 introduces the Resource Loader.
//		$realFunction = array( 'SMWOutputs', 'requireResource' );
//		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
//			$wgOut->addModules('ext.wes.spm_dt.widget');
//		} else {
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/widgets/datatype/widget.js"></script>' );
//		}
	}

	public function getFieldParameters( &$params ) {
		$extra_params = parent::getFieldParameters( $params );
		$extra_params['widget'] = 'true';
		return $extra_params;
	}

	public function getPropertyWiki( &$params ) {
		$wiki = parent::getPropertyWiki( $params );

		$widget = trim( array_shift( $params ) );

		$typevalue = SMWDataValueFactory::newTypeIDValue( '___wsw', $widget );
		$smwdatatype = SMWPropertyValue::makeProperty( '___SPM_WF_WA' );
		$wiki .= "\n* Has widget: [[{$smwdatatype->getWikiValue()}::" . SPMWidgetUtils::getPrefixedText( $typevalue ) . "]]\n";

		return $wiki;
	}

	public function getFieldSettings( $proptitle, $params ) {
		$settings = parent::getFieldSettings( $proptitle, $params );

		$widget = '';
		$store = smwfGetStore();
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$widgets = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_WA' )->getDataItem() );
		} else {
			$widgets = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '___SPM_WF_WA' ) );
		}
		if ( count( $widgets ) > 0 ) {
			$widget = SPMUtils::getWikiValue( $widgets[0] );
		}
		$settings .= $widget . "\n";

		return $settings;
	}

	protected function getSampleWikiOnEmpty() {
		return 'Wom nothing'; // build in blank page for widget
	}

	private function getParentCategories( $category_tree, &$categories ) {
		foreach ( $category_tree as $cate => $subtree ) {
			$cate = Title::newFromText( $cate )->getText();
			if ( isset( $categories[$cate] ) ) continue;

			$categories[$cate] = true;
			$this->getParentCategories( $subtree, $categories );
		}
	}
	private function getSubCategories( $category, &$categories ) {
		$db =& wfGetDB( DB_SLAVE );
		$res = $db->select(
			array( 'page', 'categorylinks' ),
			array( 'page_title' ),
			array( 'page_namespace' => NS_CATEGORY, 'cl_to' => $category->getDBkey() ),
			__METHOD__,
			array(),
			array( 'categorylinks'  => array( 'INNER JOIN', 'cl_from = page_id' ) )
		);
		$cates = array();
		if ( $db->numRows( $res ) > 0 ) {
			while ( $row = $db->fetchObject( $res ) ) {
				$cates[] = Title::makeTitle( NS_CATEGORY, $row->page_title );
			}
		}
		$db->freeResult( $res );

		foreach ( $cates as $c ) {
			$cate = $c->getText();
			if ( isset( $categories[$cate] ) ) continue;

			$categories[$cate] = true;
			$this->getSubCategories( $c, $categories );
			$this->getParentCategories( $c->getParentCategoryTree(), $categories );
		}
	}
	public function getDesignerHtml( $title_name ) {
		$category = Title::newFromText( $title_name, NS_CATEGORY );
		$categories = array( $category->getText() => true );
		$this->getParentCategories( $category->getParentCategoryTree(), $categories );
		$this->getSubCategories( $category, $categories );

		$db =& wfGetDB( DB_SLAVE );
		$res = $db->select( $db->tableName( 'page' ),
				'page_title',
				'page_namespace=' . NS_CATEGORY_WIDGET . ' AND page_is_redirect = 0',
				__METHOD__ );
		$widgets = array();
		if ( $db->numRows( $res ) > 0 ) {
			while ( $row = $db->fetchObject( $res ) ) {
				$widgets[] = Title::makeTitle( NS_CATEGORY_WIDGET, $row->page_title )->getText();
			}
		}
		$db->freeResult( $res );

		$options = '';
		foreach ( $widgets as $w ) {
			if ( isset( $categories[$w] ) ) continue;
			$options .= '
<option value="' . str_replace( '"', '\"', $w ) . '">' . htmlspecialchars( $w ) . '</option>';
		}

		return '
          <tr>
            <td>
              <label style="width:150px;">Widget name
                <span class="small" style="width:150px;">Include widget?</span>
              </label>
	<select id="spm_wf_field_widget" style="margin: 2px 0px 0px 10px;">' . $options . '
	</select>
              <div style="clear:both;"></div>
              <input type="hidden" id="spm_wf_field_default"/>
              <input type="hidden" id="spm_wf_prop_allows"/>
            </td>
          </tr> ';
	}

	public function getViewWiki( $proptitle, $params ) {
		$widget = '';
		$store = smwfGetStore();
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$widgets = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_WA' )->getDataItem() );
		} else {
			$widgets = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '___SPM_WF_WA' ) );
		}
		if ( count( $widgets ) > 0 ) {
			$widget = SPMUtils::getWikiValue( $widgets[0] );
		}

		$title = Title::newFromText( $widget, NS_CATEGORY_WIDGET );
		if ( $title == null || !$title->exists() ) return '';

		$viewer = new CategoryWidgetViewer( $title );
		return $viewer->loadWidgetViewWiki();
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

		$hint1 = wfMessage( 'wf_spm_field_description', '' )->parse();

		global $wgSPMScriptPath, $wgOut;
		return '
<tr><td>' . $wgOut->parse( "[[{$proptitle}|{$label}]]", true ) . '
' . ( $hint1 ? "<p>{$hint1}</p>":'' ) . '
</td><td>
<div id="' . $id . '" class="spm_wf_fld">
' . $this->getEditorHtml( $title, $tmpl_name, $field_name, $current_value, $proptitle, $extra_semdata, $params ) . '
</div>
</td></tr>';
//		return '
// <tr><td>
// <label>' . $wgOut->parse("[[{$proptitle}|{$label}]]", true) . '</label>
// <img class="popupIcon" src="' . $wgSPMScriptPath . '/skins/wf_editor/hint.png"/>
// <div class="tooltip">
// <span class="label">' . $hint1 . '<h4>settings</h4><p>type: Widget.</p></span>
// </div>
// </td></tr>
// <tr><td>
// <div id="' . $id . '" class="spm_wf_fld">
// ' . $this->getEditorHtml($title, $tmpl_name, $field_name, $current_value, $proptitle, $extra_semdata, $params) . '
// </div>
// </td></tr>';
	}

	public function getEditorHtml( $title,
			$tmpl_name, $field_name, $current_value,
			Title $proptitle,
			$extra_semdata = null, $params = array(), $ajax = false ) {

		if ( $title == null ) return wfMessage( 'wf_spm_err_not_support' )->text();

		$widget = '';
		$store = smwfGetStore();
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$widgets = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_WA' )->getDataItem() );
		} else {
			$widgets = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '___SPM_WF_WA' ) );
		}
		if ( count( $widgets ) > 0 ) {
			$widget = SPMUtils::getWikiValue( $widgets[0] );
		}
		if ( $widget == '' ) return '
<span class="small" style="width:450px;">' . wfMessage( 'wf_spm_err_widget_not_defined', $proptitle )->parse() . '</span>';

		$name = str_replace( '"', '\"', "{$tmpl_name}[0][{$field_name}]" );

		$multiple = $params['multiple'];
		$optional = $params['optional'];

		$clazz = '';
		if ( $multiple ) $clazz = 'spm_wf_multi_val';
		if ( $optional ) $clazz .= ' spm_wf_optional_val';

//		$current_value = trim( $current_value );
		$subtitle = Title::newFromText( $title->getText() . '/' . $proptitle->getText(), $title->getNamespace() );
		$current_value = SPMWidgetUtils::getTitlePrefixedText( $subtitle );

		$html = '<input type="hidden" class="spm_wf_val ' . $clazz . '" name="' . $name .
'" type="text" value="' . str_replace( '"', '\"', $current_value ) . '"/>';

//		if($multiple) {
//			foreach( explode(',', $current_value) as $pg ){
//				if($pg == '') continue;
//				$wom = WOMProcessor::getPageObject( Title::newFromText( trim( $pg ) ) );
//				$html .= SPMWidgetUtils::getWidgetInputHtml($widget, $wom);
//			}
//		} else {
		{
			try {
				$wom = WOMProcessor::getPageObject( $subtitle );
			} catch ( Exception $e ) {
				$wom = new WOMPageModel();
				$wom->setTitle( $subtitle );
			}

			$html .= SPMWidgetUtils::getWidgetInputHtml( $widget, $wom, $params['t_vals'] );
		}

		return $html;
	}
}
