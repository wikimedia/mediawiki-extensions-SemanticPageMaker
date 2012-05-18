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
class SPMWidgetAssembler extends SpecialPage {
	public function __construct() {
		parent::__construct( 'WidgetAssembler' );
	}
	/*
	 * Overloaded function that is responsible for the creation of the Special Page
	 */
	public function execute( $query ) {
		global $wgOut, $wgRequest;

		$widget_name = $wgRequest->getVal( 'spm_w' );
		$page_name = $wgRequest->getVal( 'spm_t' );
		$params = $wgRequest->getVal( 'params' );

		if ( $page_name == null || $widget_name == null ) {
			$wgOut->setPageTitle( wfMsg( 'wf_editor' ) );

			$help = 'Usage: ?spm_w=<widget name>&spm_t=<page title>';

			$wgOut->addWikiText( $help );
			return true;
		}

		$title = Title::newFromText( $page_name );
		$revision = Revision::newFromTitle( $title );
		if ( $revision != null ) {
			$wom = WOMProcessor::getPageObject( $title );
		} else {
			$wom = new WOMPageModel();
			$wom->setTitle( $title );
		}
		$page_name = $title->getPrefixedText();

		$submit = $wgRequest->getVal( 'submit' );

		if ( $submit != null ) {
			$this->savePage( $widget_name, $wom );
			SPMUtils::showCleanWikiOutput();
			return true;
		}

		$req = explode( '?', $wgRequest->getRequestURL(), 2 );
		$query_components = explode( '&', $req[1] );
		$t_vals = array();
		foreach ( $query_components as $query_component ) {
			$query_component = urldecode( $query_component );
			$var_and_val = explode( '=', $query_component );
			if ( count( $var_and_val ) == 2 ) {
				if ( preg_match( '/^\s*([^\[\]]+)\[([^\[\]]+)\]\s*$/', $var_and_val[0], $m ) ) {
					$t_vals[$m[1]][$m[2]] = $var_and_val[1];
				}
			}
		}

		$extra_js = $extra_style = '';
		foreach ( explode( ',', $params ) as $p ) {
			if ( strtolower( $p ) == 'autosubmit' ) {
				$extra_js .= '<script type="text/javascript">jQuery(document).ready(function(){document.autoform.submit.click();});</script>';
				$extra_style .= 'visibility:hidden;';
			}
		}

		global $wgTitle;
		$new_url = $wgTitle->getLocalURL( 'action=submit' );
		$html = '
<div style="' . $extra_style . '">
<form name="autoform" method="post" action="' . $new_url . '">
<input type="hidden" name="spm_t" value="' . str_replace( '"', '\"', $page_name ) . '"/>
<input type="hidden" name="spm_w" value="' . str_replace( '"', '\"', $widget_name ) . '"/>
<div align="center">';

		$html .= '
<h2>' . wfMsgWikiHtml( 'wf_title', $page_name ) . '</h2>
' . SPMWidgetUtils::getWidgetAssemblerHtml( $widget_name, $wom, $t_vals );

		$html .= '
<br/>* means required fields<hr/>
<input name="submit" type="submit" value="Save" class="spm_submit"/>
</div>
</form>
</div>';

		$wgOut->addHTML( $html );

		if ( $extra_js ) $wgOut->addScript( $extra_js );

		# We take over from $wgOut, excepting its cache header info
		SPMUtils::showCleanWikiOutput();

		return true;
	}

	public function savePage( $widget_name, WOMPageModel $page_obj ) {
		global $wgRequest, $wgUser;

		SPMWidgetUtils::updateWidgetValues( $widget_name, $page_obj );

		$cate_name = Title::newFromText( $widget_name, NS_CATEGORY )->getText();
		// if current category is in some existing category tree, do not insert
		$cate_already_in_tree = false;
		$cateset = array( $cate_name );
		SPMWidgetUtils::getCategoryHierarchy( $cate_name, $cateset );
		// remove categories in current category tree
		foreach ( $page_obj->getObjectsByTypeID( WOM_TYPE_CATEGORY ) as $c ) {
			if ( in_array( $c->getName(), $cateset ) ) {
				$page_obj->removePageObject( $c->getObjectID() );
			} else {
				$cateset1 = array( $c->getName() );
				SPMWidgetUtils::getCategoryHierarchy( $c->getName(), $cateset1 );
				if ( in_array( $cate_name, $cateset1 ) ) $cate_already_in_tree = true;
			}
		}
		// remove empty 'noinclude' html tags
		foreach ( $page_obj->getObjectsByTypeID( WOM_TYPE_HTMLTAG ) as $po ) {
			if ( strtolower( $po->getName() ) == 'noinclude' && count( $po->getObjects() ) == 0 ) {
				$page_obj->removePageObject( $po->getObjectID() );
			}
		}
		if ( !$cate_already_in_tree ) {
			// apply current category
			global $wgContLang;
			$namespace = $wgContLang->getNsText( NS_CATEGORY );
			$page_obj->appendChildObject( new WOMTextModel( "\n<noinclude>[[{$namespace}:{$cate_name}]]</noinclude>" ) );
		}

		print '
<div align="center"> Saving ... </div>
';
		// save to wiki
		$article = new Article( $page_obj->getTitle() );
		$article->doEdit( $page_obj->getWikiText(), '' );

		print '
<script type="text/javascript">
/*
var loc = "' . $page_obj->getTitle()->getFullURL( 'action=wiedit' ) . '";
if(parent.location==loc)
	parent.location.refresh();
else
*/
	parent.location = "' . $page_obj->getTitle()->getFullURL() . '";
</script>';

		return true;
	}
}
