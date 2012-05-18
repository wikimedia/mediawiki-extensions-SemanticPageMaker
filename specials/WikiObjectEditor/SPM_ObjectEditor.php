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
class SPMObjectEditor extends SpecialPage {
	public function __construct() {
		parent::__construct( 'ObjectEditor' );
	}
	/*
	 * Overloaded function that is responsible for the creation of the Special Page
	 */
	public function execute( $query ) {
		global $wgOut, $wgRequest;

		$id = $wgRequest->getVal( 'spm_id' );
		$rid = $wgRequest->getVal( 'spm_rid' );
		$page_name = $wgRequest->getVal( 'spm_title' );

		if ( $id == null ) {
			$q_arr = explode( '/', $query, 3 );

			if ( count( $q_arr ) != 3 ) {
				$wgOut->setPageTitle( wfMsg( 'spm_editor' ) );

				$help = 'Usage: <page revision id>/<WOM id>/<page title>';

				$wgOut->addWikiText( $help );
				return true;
			}
			$rid = $q_arr[0];
			$id = $q_arr[1];
			$page_name = $q_arr[2];
		}

		$wom = WOMProcessor::getPageObject( Title::newFromText( $page_name ), $rid );

		$submit = $wgRequest->getVal( 'submit' );
		$remove = $wgRequest->getVal( 'remove' );

		if ( $submit != null ) {
			$this->savePage( $wom, $id, $rid, 'spm_obj' );
			return true;
		} else if ( $remove != null ) {
			$wom->removePageObject( $id );
			$this->save( $wom, $rid );
			return true;
		}

		SPMWidgetUtils::applyWYSIWYG();

		global $wgTitle;
		$new_url = $wgTitle->getLocalURL( 'action=submit' );
		$html = '
<form method="post" action="' . $new_url . '">
<input type="hidden" name="spm_title" value="' . str_replace( '"', '\"', $page_name ) . '"/>
<input type="hidden" name="spm_id" value="' . $id . '"/>
<input type="hidden" name="spm_rid" value="' . $rid . '"/>
<div align="center">';

		$html .= $this->renderEditor( $wom->getObject( $id ), 'spm_obj', $onSubmit );

		$html .= '
<br/><hr/>
<input name="submit" type="submit" value="Save" onclick="' . $onSubmit . '" class="spm_submit"/>
<input name="remove" type="submit" value="Remove" onclick="" class="spm_submit"/>
</div>
</form>';
		$wgOut->addHTML( $html );

		# We take over from $wgOut, excepting its cache header info
		SPMUtils::showCleanWikiOutput();

		return true;
	}

	public function renderEditor( $obj, $obj_name = 'spm_obj', &$onSubmit ) {
		// check privilege first
		// TBD!!!

		$html = '';

		$html .= SPMProcessor::getEditorHtml( $obj, $obj_name, $onSubmit );

		return $html;
	}

	public function savePage( $wom, $id, $rid, $obj_name = 'spm_obj' ) {
		global $wgRequest, $wgUser;

		SPMProcessor::updateValues( $wom->getObject( $id ), $wgRequest->getArray( $obj_name ), $wom );

		$this->save( $wom, $rid );
	}

	private function save( $wom, $rid = 0 ) {
		print '
<div align="center"> Saving ... </div>
';
		// save to wiki
		$article = new Article( $wom->getTitle() );
		$article->doEdit( $wom->getWikiText(), '', 0, $rid );

		print '
<script type="text/javascript">
/*
var loc = "' . $wom->getTitle()->getFullURL( 'action=wiedit' ) . '";
if(parent.location==loc)
	parent.location.refresh();
else
*/
	parent.location = parent.location;
</script>';

		return true;
	}
}
