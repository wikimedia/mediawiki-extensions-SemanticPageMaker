<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 */

class SPMWidgetExtraPlain extends SPMWidgetExtra {
	public function __construct() {
		parent::__construct( SPM_WG_PLAIN );
	}

	public function getName() {
		return 'plain wiki';
	}

	public function registerResourceModules() {
		global $wgResourceModules, $wgSPMIP, $wgSPMScriptPath;

		$moduleTemplate = array(
			'localBasePath' => $wgSPMIP,
			'remoteBasePath' => $wgSPMScriptPath,
			'group' => 'ext.wes.spm_extra'
		);

		$wgResourceModules['ext.wes.spm_extra.plain'] = $moduleTemplate + array(
			'scripts' => array( 'scripts/wf_designer/widgets/extra/plain.js' ),
		);
	}

	public function addHTMLHeader() {
		global $wgOut, $wgSPMScriptPath;

		// FIXME: MW 1.17 resource loader cannot handle dynamic script inside lazy load scripts

//		// MediaWiki 1.17 introduces the Resource Loader.
//		$realFunction = array( 'SMWOutputs', 'requireResource' );
//		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
//			$wgOut->addModules('ext.wes.spm_extra.plain');
//		} else {
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/widgets/extra/plain.js"></script>' );
//		}
	}

	public function getWidgetWikiHtml( $html ) {
		// FIXME: hard code here
		$idx = strpos( $html, '<div class="spm_wf_plain">' );
		if ( $idx === FALSE ) return FALSE;

		$html = substr( $html, $idx + strlen( '<div class="spm_wf_plain">' ) );
		$html = substr( $html, 0, strrpos( $html, '</div>' ) );

		return $html;
	}
}
