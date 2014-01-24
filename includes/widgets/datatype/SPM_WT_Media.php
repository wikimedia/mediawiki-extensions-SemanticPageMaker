<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 *
 */

class SMWMediaDatatypeWikiPageValue extends SMWWikiPageValue {
	public function __construct( $typeid ) {
		parent::__construct( $typeid );
		switch ( $typeid ) {
			case '___med':
				$this->m_fixNamespace = NS_MEDIA;
				break;
		}
	}
}

class SPMWidgetMediaType extends SPMWidgetDataType {
	public function __construct() {
		parent::__construct( SPM_WT_TYPE_MEDIA );
	}

	public function smwInitDatatypes() {
		global $wgSPMContLang;
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			SMWDataValueFactory::registerDatatype( '___med', 'SMWMediaDatatypeWikiPageValue', SMWDataItem::TYPE_WIKIPAGE, $wgSPMContLang->getDatatype( 'media' ) ); // media
			// in SMW 1.6, have to register default _wpg back, to SMWDataValueFactory::$mNewDataItemIds
			// could be a bug in SMW 1.6
			SMWDataValueFactory::registerDatatype( '_wpg', 'SMWWikiPageValue', SMWDataItem::TYPE_WIKIPAGE );
		} else {
			SMWDataValueFactory::registerDatatype( '___med', 'SMWMediaDatatypeWikiPageValue', $wgSPMContLang->getDatatype( 'media' ) ); // media
		}
	}

	public function getSMWTypeID() {
		return '___med';
	}

	static $uploadId = 0;
	protected function renderNonPossibleValuesField(
		&$js,
		$name, $current_value, $possible_values,
		Title $proptitle,
		$extra_semdata = null, $params = array() ) {

		$multiple = $params['multiple'];
		$optional = $params['optional'];

		$id1 = 'spm_wf_file_' . SPMWidgetFileType::$uploadId;
		$id2 = 'spm_wf_fileupload_' . SPMWidgetFileType::$uploadId;
		SPMWidgetFileType::$uploadId ++;

		$js .= '
spm_wf_field.data.push( {
	name : "' . $name . '",
	type : "upload",
	params : [ "' . $id2 . '" ]
} );';

		global $wgOut, $wgSPMScriptPath, $wgSPMWfFancyBoxIncluded;
		// MediaWiki 1.17 introduces the Resource Loader.
		$realFunction = array( 'SMWOutputs', 'requireResource' );
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
			$wgOut->addModules( 'ext.wes.spm_cate' );
		} else {
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

		$upload_window_page = SpecialPageFactory::getPage( 'UploadWindow' );
		$query_string = "sfInputID={$id1}";
		if ( $multiple ) $query_string .= "&sfDelimiter=,";
		$upload_window_url = $upload_window_page->getTitle()->getFullURL( $query_string );

		$clazz = '';
		if ( $multiple ) $clazz = 'spm_wf_multi_val';
		if ( $optional ) $clazz .= ' spm_wf_optional_val';
		return '<input id="' . $id1 . '" class="spm_wf_val ' . $clazz . '" name="' . $name .
'" type="text" value="' . str_replace( '"', '\"', $current_value ) . '"/>
<a id="' . $id2 . '" href="' . $upload_window_url . '">' . wfMsg( 'upload' ) . '</a>';
	}

	protected function getSampleWikiOnEmpty() {
		return 'sample.png';
	}
}
