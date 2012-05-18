<?php
/*
 * Created on 22.11.2010
 *
 * Author: ning
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $wgSPMIP, $wgSPMScriptPath;

require_once( $wgSPMIP . '/includes/widgets/SPM_WidgetConfig.php' );

// have to initialize magic hook before extension functions
$wgAutoloadClasses['SPMWidgetParserFunctions'] = $wgSPMIP . '/includes/widgets/SPM_WidgetParserFunctions.php';
$wgHooks['LanguageGetMagic'][] = 'SPMWidgetParserFunctions::languageGetMagic';

function smwfSPMWidgetFormInitNamespaces() {
	global $smwgNamespaceIndex, $wgExtraNamespaces, $wgNamespaceAliases, $wgNamespacesWithSubpages, $wgLanguageCode, $wgSPMContLang;

	define( 'NS_CATEGORY_WIDGET',       $smwgNamespaceIndex + 50 );
	define( 'NS_CATEGORY_WIDGET_TALK',  $smwgNamespaceIndex + 51 );
	smwfSPMInitContentLanguage( $wgLanguageCode );

	// Register namespace identifiers
	if ( !is_array( $wgExtraNamespaces ) ) { $wgExtraNamespaces = array(); }
	$wgExtraNamespaces = $wgExtraNamespaces + $wgSPMContLang->getNamespaces();
	$wgNamespaceAliases = $wgNamespaceAliases + $wgSPMContLang->getNamespaceAliases();

	// Support subpages only for talk pages by default
	$wgNamespacesWithSubpages = $wgNamespacesWithSubpages + array(
				NS_CATEGORY_WIDGET_TALK => true
	);
}

smwfSPMWidgetFormInitNamespaces();

/**
 * Intializes WidgetAssemblers
 * Called from SPM during initialization.
 */
function wgSPMWidgetSetupExtension() {
	global $wgSPMIP;
	require_once( $wgSPMIP . '/includes/widgets/SPM_WidgetSetup.php' );

	global $wgHooks, $wgAutoloadClasses, $wgSpecialPages, $wgSpecialPageGroups;

    // we widget
	$wgAutoloadClasses['SPMArticleUtils'] = $wgSPMIP . '/includes/widgets/SPM_ArticleUtils.php';
	$wgAutoloadClasses['SPMWidgetUtils'] = $wgSPMIP . '/includes/widgets/SPM_WidgetUtils.php';
	$wgAutoloadClasses['SPMWidgetDataTypeUtils'] = $wgSPMIP . '/includes/widgets/datatype/SPMWidgetDataTypeUtils.php';
	$wgAutoloadClasses['SPMWidgetViewUtils'] = $wgSPMIP . '/includes/widgets/view/SPMWidgetViewUtils.php';
	$wgAutoloadClasses['SPMWidgetExtraUtils'] = $wgSPMIP . '/includes/widgets/extra/SPMWidgetExtraUtils.php';

	$wgAutoloadClasses['SPMWidgetPage'] = $wgSPMIP . '/includes/widgets/SPM_WidgetPage.php';
	$wgAutoloadClasses['CategoryWidgetViewer'] = $wgSPMIP . '/includes/widgets/SPM_WidgetPage.php';
	$wgAutoloadClasses['SPMWidgetDesignPage'] = $wgSPMIP . '/includes/widgets/SPM_WidgetDesignPage.php';
	$wgAutoloadClasses['CategoryWidgetDesignViewer'] = $wgSPMIP . '/includes/widgets/SPM_WidgetDesignPage.php';
	$wgAutoloadClasses['SPMWidgetDesignPage2'] = $wgSPMIP . '/includes/widgets/SPM_WidgetDesignPage2.php';
	$wgAutoloadClasses['CategoryWidgetDesignViewer2'] = $wgSPMIP . '/includes/widgets/SPM_WidgetDesignPage2.php';
	// register tab hooks
	$wgHooks['SkinTemplateTabs'][] = 'SPMWidgetUtils::displayTab';
	// vector hook
	$wgHooks['SkinTemplateNavigation'][] = 'SPMWidgetUtils::displayTabVector';
	$wgHooks['SkinTemplateTabAction'][] = 'SPMWidgetUtils::tabAction';
	$wgHooks['UnknownAction'][] = 'SPMWidgetUtils::applyWidgetDesignAction';
	$wgHooks['UnknownAction'][] = 'SPMWidgetUtils::applyWidgetDesignAction2';
	$wgHooks['ArticleFromTitle'][] = 'SPMWidgetUtils::widgetViewPage';
	$wgHooks['BeforePageDisplay'][] = 'SPMWidgetUtils::addHTMLHeader';
    $wgHooks['AbortMove'][] = 'SPMWidgetUtils::onWidgetMove';

	$wgHooks['CategoryPageView'][] = 'SPMWidgetUtils::addWFInput';
	// register semantic data
    global $smwgNamespacesWithSemanticLinks;
    $smwgNamespacesWithSemanticLinks[NS_CATEGORY_WIDGET] = true;
    $smwgNamespacesWithSemanticLinks[NS_CATEGORY_WIDGET_TALK] = false;
	$wgHooks['smwInitProperties'][] = 'SPMWidgetUtils::initProperties';
    $wgHooks['smwInitDatatypes'][] = 'SPMWidgetUtils::smwInitDatatypes';
    $wgHooks['smwInitDatatypes'][] = 'SPMWidgetDataTypeUtils::smwInitDatatypes';
    global $wgParser;
    if ( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
    	$wgHooks['ParserFirstCallInit'][] = 'SPMWidgetParserFunctions::registerFunctions';
    } else {
    	if ( class_exists( 'StubObject' ) && !StubObject::isRealObject( $wgParser ) ) {
    		$wgParser->_unstub();
    	}
    	SPMWidgetParserFunctions::registerFunctions( $wgParser );
    }

    // WOM api output
    $wgAutoloadClasses['SPMWidgetApiUtils'] = $wgSPMIP . '/includes/widgets/SPM_WidgetApiUtils.php';
	$wgHooks['womGetExtraOutputObjects'][] = 'SPMWidgetApiUtils::getOutputWOMObjects';

    $wgHooks['ParserOnTemplateLoopCheck'][] = 'SPMWidgetUtils::parserOnTemplateLoopCheck';

    global $wgRequest;
	$action = $wgRequest->getVal( 'action' );
	// add we AJAX calls
	if ( $action == 'ajax' ) {
		$method_prefix = smwfSPMGetAjaxMethodPrefix();

		// decide according to ajax method prefix which script(s) to import
		switch( $method_prefix ) {
			case '_wf_' :
				require_once( $wgSPMIP . '/includes/widgets/SPM_WFAjaxAccess.php' );
				break;
		}
	} else { // otherwise register special pages
		$wgAutoloadClasses['SPMWidgetAssembler'] = $wgSPMIP . '/specials/WidgetAssembler/SPM_WidgetAssembler.php';
		$wgSpecialPages['WidgetAssembler'] = array( 'SPMWidgetAssembler' );
		$wgSpecialPageGroups['WidgetAssembler'] = 'smw_group';

		$wgAutoloadClasses['SPMWidgetClone'] = $wgSPMIP . '/specials/WidgetClone/SPM_WidgetClone.php';
		$wgSpecialPages['WidgetClone'] = array( 'SPMWidgetClone' );
		$wgSpecialPageGroups['WidgetClone'] = 'smw_group';

		if ( defined( 'SMW_HALO_VERSION' ) && version_compare( SMW_HALO_VERSION, '1.5', '>=' ) ) {
			$wgAutoloadClasses['SPMQueryInterface'] = $wgSPMIP . '/specials/SPMQueryInterface/SPM_QueryInterface.php';
			$wgSpecialPages['SPMQueryInterface'] = array( 'SPMQueryInterface' );
			$wgAutoloadClasses['SPMQueryInterface2'] = $wgSPMIP . '/specials/SPMQueryInterface/SPM_QueryInterface2.php';
			$wgSpecialPages['SPMQueryInterface2'] = array( 'SPMQueryInterface2' );
		}
		if ( !defined( 'SF_VERSION' ) ) {
			if ( class_exists( 'HTMLTextField' ) ) { // added in MW 1.16
				$wgAutoloadClasses['SPMUploadWindow2'] = $wgSPMIP . '/specials/WidgetAssembler/SPM_UploadWindow2.php';
				$wgSpecialPages['UploadWindow'] = array( 'SPMUploadWindow2' );
			} else {
				$wgAutoloadClasses['SPMUploadWindow'] = $wgSPMIP . '/specials/WidgetAssembler/SPM_UploadWindow.php';
				$wgSpecialPages['UploadWindow'] = array( 'SPMUploadWindow' );
			}

			// autocomplete
			global $wgAPIModules, $sfgMaxAutocompleteValues;
			$sfgMaxAutocompleteValues = 1000;

			$wgAutoloadClasses['SFUtils'] = $wgSPMIP . '/includes/widgets/datatype/SF_Utils.php';
			$wgAutoloadClasses['SFAutocompleteAPI'] = $wgSPMIP . '/includes/widgets/datatype/SF_AutocompleteAPI.php';
			$wgAPIModules['sfautocomplete'] = 'SFAutocompleteAPI';
		}
	}

	// resource loader
	SPMWidgetUtils::registerResourceModules();

	SPMWidgetExtraUtils::initialize();
	foreach ( SPMWidgetExtraUtils::$extras as $e ) {
		$e->initializeOnSetupExtension();
	}

	return true;
}
