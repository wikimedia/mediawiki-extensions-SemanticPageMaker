<?php
/*
 * Created on 22.11.2010
 *
 * Author: ning
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

define( 'SPM_VERSION', '0.1' );

/**
 * For older versions of mediawiki, which don't support NS_FILE (< 1.14).
 */
if ( !defined( 'NS_FILE' ) ) {
	define( 'NS_FILE', NS_IMAGE );
}

$wgSPMIP = $IP . '/extensions/SemanticPageMaker';
$wgSPMScriptPath = $wgScriptPath . '/extensions/SemanticPageMaker';

global $wgExtensionFunctions;
$wgExtensionFunctions[] = 'wgSPMSetupExtension';

require_once( $wgSPMIP . '/includes/SPM_Setup.php' );


function smwfSPMInitContentLanguage( $langcode ) {
	global $wgSPMIP, $wgSPMContLang;
	if ( !empty( $wgSPMContLang ) ) { return; }

	$mwContLangClass = 'SPMLanguage' . str_replace( '-', '_', ucfirst( $langcode ) );

	if ( file_exists( $wgSPMIP . '/languages/' . $mwContLangClass . '.php' ) ) {
		include_once( $wgSPMIP . '/languages/' . $mwContLangClass . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists( $mwContLangClass ) ) {
		include_once( $wgSPMIP . '/languages/SPMLanguageEn.php' );
		$mwContLangClass = 'SPMLanguageEn';
	}
	$wgSPMContLang = new $mwContLangClass();
}


require_once( $wgSPMIP . '/includes/widgets/SPM_WidgetInitialize.php' );


function smwfSPMInitMessages() {
	global $wgSPMMessagesInitialized;
	if ( isset( $wgSPMMessagesInitialized ) ) return; // prevent double init

	wfSPMInitUserMessages(); // lazy init for ajax calls

	$wgSPMMessagesInitialized = true;
}
function wfSPMInitUserMessages() {
	global $wgMessageCache, $wgSPMContLang, $wgLanguageCode;
	smwfSPMInitContentLanguage( $wgLanguageCode );

	global $wgSPMIP, $wgSPMLang;
	if ( !empty( $wgSPMLang ) ) { return; }
	global $wgMessageCache, $wgLang;
	$mwLangClass = 'SPMLanguage' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if ( file_exists( $wgSPMIP . '/languages/' . $mwLangClass . '.php' ) ) {
		include_once( $wgSPMIP . '/languages/' . $mwLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists( $mwLangClass ) ) {
		global $wgSPMContLang;
		$wgSPMLang = $wgSPMContLang;
	} else {
		$wgSPMLang = new $mwLangClass();
	}

	$wgMessageCache->addMessages( $wgSPMLang->getUserMsgArray(), $wgLang->getCode() );
}


function smwfSPMGetAjaxMethodPrefix() {
	$func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : ( isset( $_GET["rs"] ) ? $_GET["rs"] : NULL );
	if ( $func_name == NULL ) return NULL;
	if ( !( substr( $func_name, 0, 3 ) == 'spm' ) ) return false;
	return substr( $func_name, 3, 4 ); // return _xx_ of spm_xx_methodname, may return FALSE
}

/**
 * Intializes SemanticPageMaker Extension.
 * Called from SPM during initialization.
 */
function wgSPMSetupExtension() {
	global $wgSPMIP, $wgHooks, $wgExtensionCredits, $wgAvailableRights;
	global $wgAutoloadClasses, $wgSpecialPages, $wgMessagesDirs;

	smwfSPMInitMessages();
	if ( !defined( 'WOM_VERSION' ) ) {
		echo wfMessage( 'spm_error_nowom' )->escaped();
		die;
	}

	$wgMessagesDirs['SemanticPageMaker'] = __DIR__ . '/../i18n';

	$wgExtensionMessagesFiles['SemanticPageMakerAlias'] = __DIR__ . '/../SemanticPageMaker.alias.php';

	$wgAutoloadClasses['SPMProcessor'] = $wgSPMIP . '/includes/SPM_Processor.php';

	$wgAutoloadClasses['SPMInlineEditor'] = $wgSPMIP . '/includes/SPM_InlineEditor.php';
	$wgHooks['UnknownAction'][] = 'SPMInlineEditor::applyWIEditAction';
	// Allow WIEdit by default for all
	$wgGroupPermissions['*']['wiedit'] = true;
	// Register WIEdit-Tab
	$wgHooks['SkinTemplateContentActions'][] = 'SPMInlineEditor::addWIEditTab';
	// vector hook
	$wgHooks['SkinTemplateNavigation'][] = 'SPMInlineEditor::displayTabVector';
	// new right for WIEdit mode
    $wgAvailableRights[] = 'wiedit';

	$wgAutoloadClasses['SPMUtils'] = $wgSPMIP . '/includes/SPM_Utils.php';

    global $wgRequest;
	$action = $wgRequest->getVal( 'action' );
	// add spm AJAX calls
	if ( $action == 'ajax' ) {
		$method_prefix = smwfSPMGetAjaxMethodPrefix();

		// decide according to ajax method prefix which script(s) to import
		switch( $method_prefix ) {
			case '_om_' :
				require_once( $wgSPMIP . '/specials/WikiObjectEditor/SPM_AjaxAccess.php' );
				break;
		}
	} else if ( $action == 'wiedit' ) {
		$wgHooks['ParserBeforeInternalParse'][] = 'SPMInlineEditor::parserBeforeInternalParse';
		$wgHooks['ParserBeforeTemplateParse'][] = 'SPMInlineEditor::parserBeforeTemplateParse';

		if ( defined( 'SMW_HALO_VERSION' ) ) {
			// insert SPM header hook before add halo header
			$found = false;
			foreach ( $wgHooks['BeforePageDisplay'] as $k => $hookVal ) {
				if ( $hookVal == 'smwfHaloAddHTMLHeader' ) {
					$wgHooks['BeforePageDisplay'][$k] = 'SPMInlineEditor::addHTMLHeader';
					$found = true;
					break;
				}
			}
			if ( !$found ) $wgHooks['BeforePageDisplay'][] = 'SPMInlineEditor::addHTMLHeader';
			$wgHooks['BeforePageDisplay'][] = 'smwfHaloAddHTMLHeader';
		} else {
			$wgHooks['BeforePageDisplay'][] = 'SPMInlineEditor::addHTMLHeader';
		}
		$wgHooks['BeforePageDisplay'][] = 'SPMInlineEditor::bindEditor';
	} else { // otherwise register special pages
		$wgAutoloadClasses['SPMObjectEditor'] = $wgSPMIP . '/specials/WikiObjectEditor/SPM_ObjectEditor.php';
		$wgSpecialPages['ObjectEditor'] = array( 'SPMObjectEditor' );
	}

	// resource loader
	SPMInlineEditor::registerResourceModules();

	wgSPMWidgetSetupExtension();

	// Register Credits
	$wgExtensionCredits['parserhook'][] = array(
	'name' => 'Semantic Page Maker Extension (formerly pulished as Wiki&#160;Editors&#160;Extension)', 'version' => SPM_VERSION,
			'author' => array( 'Ning Hu', 'Justin Zhang', '[http://smwforum.ontoprise.com/smwforum/index.php/Jesse_Wang Jesse Wang]', 'sponsored by [http://projecthalo.com Project Halo]', '[http://www.vulcan.com Vulcan Inc.]' ),
			'url' => 'http://wiking.vulcan.com/dev',
			'descriptionmsg' => 'semanticpagemaker-desc' );

	return true;
}

function wfSPMGetJSLanguageScripts( &$pathlng, &$userpathlng ) {
	global $wgSPMIP, $wgLanguageCode, $wgSPMScriptPath, $wgUser;

	// content language file
	$lng = '/scripts/Language/SPMLanguage';
	if ( !empty( $wgLanguageCode ) ) {
		$lng .= ucfirst( $wgLanguageCode ) . '.js';
		if ( file_exists( $wgSPMIP . $lng ) ) {
			$pathlng = $wgSPMScriptPath . $lng;
		} else {
			$pathlng = $wgSPMScriptPath . '/scripts/Language/SPMLanguageEn.js';
		}
	} else {
		$pathlng = $wgSPMScriptPath . '/scripts/Language/SPMLanguageEn.js';
	}

	// user language file
	$lng = '/scripts/Language/SPMLanguage';
	if ( isset( $wgUser ) ) {
		$lng .= "User" . ucfirst( $wgUser->getOption( 'language' ) ) . '.js';
		if ( file_exists( $wgSPMIP . $lng ) ) {
			$userpathlng = $wgSPMScriptPath . $lng;
		} else {
			$userpathlng = $wgSPMScriptPath . '/scripts/Language/SPMLanguageUserEn.js';
		}
	} else {
		$userpathlng = $wgSPMScriptPath . '/scripts/Language/SPMLanguageUserEn.js';
	}
}

function wfSPMGetLocalJSLanguageScripts( &$pathlng, &$userpathlng ) {
	global $wgSPMIP, $wgLanguageCode, $wgUser;

	// content language file
	$lng = 'scripts/Language/SPMLanguage';
	if ( !empty( $wgLanguageCode ) ) {
		$lng .= ucfirst( $wgLanguageCode ) . '.js';
		if ( file_exists( $wgSPMIP . $lng ) ) {
			$pathlng = $lng;
		} else {
			$pathlng = 'scripts/Language/SPMLanguageEn.js';
		}
	} else {
		$pathlng = 'scripts/Language/SPMLanguageEn.js';
	}

	// user language file
	$lng = 'scripts/Language/SPMLanguage';
	if ( isset( $wgUser ) ) {
		$lng .= "User" . ucfirst( $wgUser->getOption( 'language' ) ) . '.js';
		if ( file_exists( $wgSPMIP . $lng ) ) {
			$userpathlng = $lng;
		} else {
			$userpathlng = 'scripts/Language/SPMLanguageUserEn.js';
		}
	} else {
		$userpathlng = 'scripts/Language/SPMLanguageUserEn.js';
	}
}
