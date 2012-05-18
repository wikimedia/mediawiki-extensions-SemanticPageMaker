<?php
/*
 * Author: ning
 */

if ( !defined( 'MEDIAWIKI' ) ) die();



global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

/*
 * Standard class that is resopnsible for the creation of the Special Page
 */
class SPMQueryInterface2 extends UnlistedSpecialPage {
	public function __construct() {
		parent::__construct( 'SPMQueryInterface2' );
	}
	/*
	 * Overloaded function that is responsible for the creation of the Special Page
	 */
	public function execute( $query ) {
		global $wgOut, $wgTitle, $wgUseAjax, $wgStylePath, $smwgHaloScriptPath, $wgSPMScriptPath;
		$title = $wgTitle;
		$wgTitle = Title::newFromText( "Special:QueryInterface" );

		if ( version_compare( SMW_HALO_VERSION, '1.6', '<' ) ) {
			$jsm = SMWResourceManager::SINGLETON();
			if ( $wgUseAjax ) {
				$jsm->addScriptIf( "{$wgStylePath}/common/ajax.js" );
			}
			$jsm->addScriptIf( $smwgHaloScriptPath .  '/scripts/prototype.js' );
		}

		// MediaWiki 1.17 introduces the Resource Loader.
		$realFunction = array( 'SMWOutputs', 'requireResource' );
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
			$wgOut->addModules( "mediawiki.util" );
			$wgOut->addModules( "mediawiki.legacy.wikibits" );
			$wgOut->addModules( "mediawiki.legacy.ajax" );
			$wgOut->addModules( "ext.ScriptManager.prototype" );
			$wgOut->addModules( "ext.jquery.qtip" );
			$wgOut->addModules( "ext.smwhalo.json2" );
		}
		smwfQIAddHTMLHeader( $wgOut );
		smwfHaloAddHTMLHeader( $wgOut );

		$wgTitle = $title;
		$qi = new SMWQueryInterface();
		if ( version_compare( SMW_HALO_VERSION, '1.5.6', '>=' ) )
			$qi->execute( null );
		else
			$qi->execute();

		$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/widgets/extra/field_ask_qi.js"></script>' );

		$html = '
<hr/>
<input type="button" value="OK" onclick="javascript:update_wf_field_ask_qi()"/>';

		$wgOut->addHTML( $html );

		# We take over from $wgOut, excepting its cache header info
		SPMUtils::showCleanWikiOutput();

		return true;
	}
}
