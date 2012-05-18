<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 *
 */

class SMWFileDatatypeWikiPageValue extends SMWWikiPageValue {
	public function __construct( $typeid ) {
		parent::__construct( $typeid );
		switch ( $typeid ) {
			case '___img':
				$this->m_fixNamespace = NS_IMAGE; // NS_FILE
				break;
		}
	}
}

class SPMWidgetFileType extends SPMWidgetMediaType {
	public function __construct() {
		parent::__construct();
		$this->m_typeid = SPM_WT_TYPE_FILE;
	}

	public function smwInitDatatypes() {
		global $wgSPMContLang;
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			SMWDataValueFactory::registerDatatype( '___img', 'SMWFileDatatypeWikiPageValue', SMWDataItem::TYPE_WIKIPAGE, $wgSPMContLang->getDatatype( 'image' ) ); // image
			// in SMW 1.6, have to register default _wpg back, to SMWDataValueFactory::$mNewDataItemIds
			// could be a bug in SMW 1.6
			SMWDataValueFactory::registerDatatype( '_wpg', 'SMWWikiPageValue', SMWDataItem::TYPE_WIKIPAGE );
		} else {
			SMWDataValueFactory::registerDatatype( '___img', 'SMWFileDatatypeWikiPageValue', $wgSPMContLang->getDatatype( 'image' ) ); // image
		}
	}

	public function getSMWTypeID() {
		return '___img';
	}

	public function registerResourceModules() {
		global $wgResourceModules, $wgSPMIP, $wgSPMScriptPath;

		$moduleTemplate = array(
			'localBasePath' => $wgSPMIP,
			'remoteBasePath' => $wgSPMScriptPath,
			'group' => 'ext.wes.spm_dt'
		);

		$wgResourceModules['ext.wes.spm_dt.file'] = $moduleTemplate + array(
			'scripts' => array( 'scripts/wf_designer/widgets/datatype/file.js' ),
		);
	}

	public function addHTMLHeader() {
		global $wgOut, $wgSPMScriptPath;

		// FIXME: MW 1.17 resource loader cannot handle dynamic script inside lazy load scripts

		// MediaWiki 1.17 introduces the Resource Loader.
//		$realFunction = array( 'SMWOutputs', 'requireResource' );
//		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
//			$wgOut->addModules('ext.wes.spm_dt.file');
//		} else {
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/widgets/datatype/file.js"></script>' );
//		}
	}

	public function getFieldParameters( &$params ) {
		$extra_params = parent::getFieldParameters( $params );
		$extra_params['type'] = array_shift( $params );
		$extra_params['size'] = array_shift( $params );
		return $extra_params;
	}

	public function getFieldSettings( $proptitle, $params ) {
		$settings = parent::getFieldSettings( $proptitle, $params );

		$type = $params['type'];
		$settings .= $type . "\n";

		$size = $params['size'];
		$settings .= $size . "\n";

		return $settings;
	}

	public function getDesignerHtml( $title_name ) {
		$html = '
          <tr>
            <td>
              <label style="width:150px;">Showing type
                <span class="small" style="width:150px;">Image or link?</span>
              </label>
	<select id="spm_wf_field_mediatype" style="margin: 2px 0px 0px 10px;">
		<option value="image">Image</option>
		<option value="media">File Link</option>
	</select>
              <div style="clear:both;"></div>
              <label style="width:150px;">Size
                <span class="small" style="width:150px;">Image size: {width}px <br/>/ x{height}px <br/>/ {width}x{height}px</span>
              </label>
<input type="text" value="150px" id="spm_wf_image_size" style="margin: 2px 0px 0px 10px;">
              <div style="clear:both;"></div>
              <hr size="1" color="#b7ddf2" />
            </td>
          </tr> ';
		$html .= parent::getDesignerHtml( $title_name );

		return $html;
	}
}
