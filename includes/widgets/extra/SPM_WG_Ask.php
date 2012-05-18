<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 */

class SPMWidgetExtraAsk extends SPMWidgetExtra {
	public function __construct() {
		parent::__construct( SPM_WG_ASK );
	}

	public function getName() {
		return 'ask query';
	}

	public function registerResourceModules() {
		global $wgResourceModules, $wgSPMIP, $wgSPMScriptPath;

		$moduleTemplate = array(
			'localBasePath' => $wgSPMIP,
			'remoteBasePath' => $wgSPMScriptPath,
			'group' => 'ext.wes.spm_extra'
		);

		$wgResourceModules['ext.wes.spm_extra.ask'] = $moduleTemplate + array(
			'scripts' => array( 'scripts/wf_designer/widgets/extra/halo_qi.js' ),
		);
	}

	public function addHTMLHeader() {
		global $wgOut, $wgSPMScriptPath;

		// FIXME: MW 1.17 resource loader cannot handle dynamic script inside lazy load scripts

//		// MediaWiki 1.17 introduces the Resource Loader.
//		$realFunction = array( 'SMWOutputs', 'requireResource' );
//		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
//			$wgOut->addModules('ext.wes.spm_extra.ask');
//		} else {
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/widgets/extra/halo_qi.js"></script>' );
//		}
	}

	public function getWidgetWikiHtml( $html ) {
		// FIXME: hard code here
		$idx = strpos( $html, '<div class="spm_wf_ask">' );
		if ( $idx === FALSE ) return FALSE;

		$html = substr( $html, $idx + strlen( '<div class="spm_wf_ask">' ) );
		$html = substr( $html, 0, strrpos( $html, '</div>' ) );

		return $html;
	}

	public function getWikiWidgetView( $text ) {
		if ( preg_match( '/^\s*\{\{\s*#ask\s*:/i', $text ) ) {
			$tmpls = SPMArticleUtils::parsePageTemplates( trim( $text ) );
			if ( $tmpls[2] == false ) {
				$text = trim( $text );
				return '
<div class="spm_wf_ask"><div class="spm_wf_wiki_body">' . $text . '</div>
<div class="spm_wf_wiki"><nowiki>' . htmlspecialchars( $text ) . '</nowiki></div></div>';
			}
		}

		return FALSE;
	}
}
