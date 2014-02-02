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
class SPMWidgetClone extends UnlistedSpecialPage {
	public function __construct() {
		parent::__construct( 'WidgetClone' );
	}
	/*
	 * Overloaded function that is responsible for the creation of the Special Page
	 */
	public function execute( $query ) {
		global $wgOut, $wgRequest;

		$srcWidget = $wgRequest->getVal( 'src' );
		$targetWidget = $wgRequest->getVal( 'target' );
		$parent = $wgRequest->getVal( 'parent' );
		$include_act = $wgRequest->getBool( 'include_act' );

		if ( $srcWidget == null ) {
			$q_arr = explode( '/', $query, 4 );

			if ( count( $q_arr ) < 1 ) {
				$wgOut->setPageTitle( wfMsg( 'spm_clone' ) );

				$help = 'Usage: <source category name>/<target category name>/<parent category name>/<include actions ? 1:0>';

				$wgOut->addWikiText( $help );
				return true;
			}
			$srcWidget = $q_arr[0];
			$targetWidget = $q_arr[1];
			$parent = $q_arr[2];
			$include_act = $q_arr[3];
		}

		$submit = $wgRequest->getVal( 'submit' );

		$srcWidget = Title::newFromText( $srcWidget )->getText();
		$targetWidget = Title::newFromText( $targetWidget );
		if ( $submit != null ) {
			if ( $targetWidget == null ) {
				$wgOut->addScript( '<script type="text/javascript">alert("Please fill in target widget name");</script>' );
			} else {
				$targetWidget = $targetWidget->getText();
				$this->widgetClone( $srcWidget, $targetWidget, $parent, $include_act );
				return true;
			}
		}

		$cate = Title::newFromText( $srcWidget, NS_CATEGORY )->getText();
		SPMWidgetUtils::getSuperWidgetProperties( $cate, $widgets );
		$parents = array( '<option value="" selected="selected"></option>' );
		foreach ( $widgets as $w ) {
			$parents[] = '<option value="' . str_replace( '"', '\"', $w['category'] ) . '"' .
			( $parent == $w['category'] ? ' selected="selected"' : '' ) . '>' . $w['category'] . '</option>';
		}

		$new_url = $this->getTitle()->getLocalURL( 'action=submit' );
		$html = '
<form method="post" action="' . $new_url . '">
<div align="center">
<h2>(Re)Create a new category widget after <i>' . htmlentities( $srcWidget ) . '</i></h2>
<input type="hidden" name="src" value="' . str_replace( '"', '\"', $srcWidget ) . '"/>
<table class="spm_wf_table">
<tr><td>Inherit from Category widget (parent)</td><td>
<SELECT name="parent">
' . implode( "\n", $parents ) . '
</SELECT>
</td></tr>
<tr><td>&nbsp;</td><td><input type="checkbox" name="include_act"/> Include actions</td></tr>
<tr><td>Target Category widget name *</td><td>
<input type="text" name="target" value="' . str_replace( '"', '\"', $targetWidget ) . '"/>
</td></tr>
<table>';

		$html .= '
<br/><hr/>
<input name="submit" type="submit" value="Save" onclick="cloneCheckSubmit" class="spm_submit"/>
</div>
</form>';
		$wgOut->addHTML( $html );

		# We take over from $wgOut, excepting its cache header info
		SPMUtils::showCleanWikiOutput();

		return true;
	}

	public function widgetClone( $srcWidget, $targetWidget, $parent, $include_act ) {
		print '
<div align="center"> Saving ... </div>
';
		$summary = "Widget clone from {$srcWidget}";

		if ( $parent ) $parent = Title::newFromText( $parent, NS_CATEGORY )->getText();

		// save to wiki
		$content = "'''Created by Semantic Page Maker.'''\n" . ( $parent ? "[[Category:{$parent}]]" : '' );
		$c_title = Title::newFromText( $targetWidget, NS_CATEGORY );
		$article = new Article( $c_title );
		$article->doEdit( $content, $summary );

		$content = '';
		$t_title = Title::newFromText( $targetWidget, NS_TEMPLATE );
		SPMWidgetUtils::getSuperWidgetProperties(
			Title::newFromText( $srcWidget, NS_CATEGORY )->getText(),
			$widgets );
		$templates = array();
		$act_widgets = array();
		foreach ( $widgets as $w ) {
			$templates[] = $w['value'];
			if ( !in_array( $w['category'], $act_widgets ) ) $act_widgets[] = $w['category'];
			if ( $w['category'] == $parent ) {
				$templates = array();
				$act_widgets = array();
			}
		}
		foreach ( SPMWidgetUtils::getWidgetProperties( $srcWidget ) as $w ) {
			$templates[] = $w['value'];
		}
		$act_widgets[] = $srcWidget;

		$fields = array();
		foreach ( $templates as $t ) {
			$revision = Revision::newFromTitle( Title::newFromText( $t, NS_TEMPLATE ) );
			if ( $revision == null )continue;
			$wikitext = $revision->getText();

			foreach ( SPMArticleUtils::parsePageTemplates( $wikitext ) as $tmpl ) {
				if ( !is_array( $tmpl ) ) {
					$content .= $tmpl;
				} else {
					if ( array_key_exists( $tmpl['name'], SPMWidgetUtils::$widgetTemplates ) &&
					SPMWidgetUtils::$widgetTemplates[$tmpl['name']] != '' ) {

						if ( preg_match( '/\{\{\{([^|}]+)/', $tmpl['fields'][2], $m ) ) {
							$key = trim( $m[1] );
							// skip the same key
							if ( in_array( $key, $fields ) ) continue;
							$fields[] = $key;

							$prop_name = $tmpl['fields'][1];
							$revision = Revision::newFromTitle( Title::newFromText( $prop_name, SMW_NS_PROPERTY ) );
							$p_content = ( $revision == null ) ? '' : $revision->getText();

							$ps = explode( '/', $prop_name, 2 );
							$new_prop_name = $targetWidget . '/' . $ps[1];
							$p_title = Title::newFromText( $new_prop_name, SMW_NS_PROPERTY );
							$article = new Article( $p_title );
							$article->doEdit( $p_content, $summary );

							$tmpl['fields'][1] = $new_prop_name;
						}
					}
					$content .= SPMArticleUtils::templateToWiki( $tmpl );
				}
			}
		}
		$article = new Article( $t_title );
		$article->doEdit( $content, $summary );

		$smwdatatype = SMWPropertyValue::makeProperty( '___SPM_WF_ST' );
		$content = "[[{$smwdatatype->getWikiValue()}::" . SPMWidgetUtils::getTitlePrefixedText( $t_title ) . "| ]]\n";
		if ( $include_act ) {
			// action connectors
			// FIXME: replace widget name for now, shall use property mapping instead
			foreach ( $act_widgets as $aw ) {
				$revision = Revision::newFromTitle( Title::newFromText( $aw, NS_CATEGORY_WIDGET ) );
				$w_content = ( $revision == null ) ? '' : $revision->getText();
				$w_content = preg_replace( '/\[\[\s*' . $smwdatatype->getWikiValue() . '\s*:[:=][^]]+\]\]/i', '', $w_content );
				$w_content = str_replace( $act_widgets, $targetWidget, $w_content );

				$content .= $w_content;
			}
		}
		$w_title = Title::newFromText( $targetWidget, NS_CATEGORY_WIDGET );
		$article = new Article( $w_title );
		$article->doEdit( $content, $summary );

		print '
<script type="text/javascript">
/*
var loc = "' . $w_title->getFullURL() . '";
if(parent.location==loc)
	parent.location.refresh();
else
*/
	parent.location = "' . $w_title->getFullURL() . '";
</script>';

		return true;
	}
}
