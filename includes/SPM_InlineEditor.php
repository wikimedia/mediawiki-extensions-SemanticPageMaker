<?php
/*
 * Created on 22.11.2010
 *
 * Author: ning
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

class SPMInlineEditor {
	static function applyWIEditAction( $action, $article ) {
		if ( $action != 'wiedit' ) {
			return true;
		}
		$title = $article->getTitle();
		$title->invalidateCache();
		$article->view();

		// The resolution of timestamps for the cache is only in seconds. Invalidate
		// the cache by setting a timestamp 2 seconds from now.
		$now = wfTimestamp( TS_MW, time() + 2 );
		$dbw = wfGetDB( DB_MASTER );
		$success = $dbw->update( 'page',
			array( /* SET */
						'page_touched' => $now
			), array( /* WHERE */
						'page_namespace' => $title->getNamespace() ,
						'page_title' => $title->getDBkey()
			), 'SPMInlineEditor::applyWIEditAction'
		);

		return false;
	}
	static function displayTabVector( &$obj, &$links ) {
		global $wgUser;
		$title = $obj->getTitle();
		if ( $title->getNamespace() == NS_SPECIAL ) return true; // Special page
		if ( $title->getNamespace() == NS_TEMPLATE ) return true; // Template page
		if ( $title->getNamespace() == NS_CATEGORY_WIDGET ) return true; // Category widget page

		$content_actions = $links['views'];
		// Check if edit tab is present, if not don't at WIEdit tab
		if ( !array_key_exists( 'edit', $content_actions ) ) return true;

		global $wgUser, $wgRequest;
		$action = $wgRequest->getText( 'action' );
		// Build WIEdit tab
		$article = new Article( $title );
		$oldid = $article->getOldID() == 0 ? '' : 'oldid=' . $article->getOldID() . '&';
		$main_action['wiedit'] = array(
	        	'class' => ( $action == 'wiedit' ) ? 'selected' : false,
	        	'text' => wfMessage( 'wiedit_tab' )->text(), // Title of the tab
	        	'href' => $title->getLocalUrl( $oldid . 'action=wiedit' )   // where it links to
		);

		// Find position of edit button
		$editpos = isset( $content_actions['edit'] ) ?
			count( range( 0, $content_actions['edit'] ) ) + 1 :
			count( $content_actions );

		// Split array
		$beforeedit = array_slice( $content_actions, 0, $editpos );
		$afteredit = array_slice( $content_actions, $editpos, count( $content_actions ) );
		// Merge array with new action
		$content_actions = array_merge( $beforeedit, $main_action );   // add a new action
		$content_actions = array_merge( $content_actions, $afteredit );

		$links['views'] = $content_actions;

		return true; // always return true, in order not to stop MW's hook processing!
	}

	static function addWIEditTab ( $content_actions ) {
		// Check if ontoskin is available
		global $wgUser, $wgTitle;
		if ( $wgTitle->getNamespace() == NS_SPECIAL ) return true; // Special page
		if ( $wgTitle->getNamespace() == NS_TEMPLATE ) return true; // Template page
		if ( $wgTitle->getNamespace() == NS_CATEGORY_WIDGET ) return true; // Category widget page
		// Check if edit tab is present, if not don't at WIEdit tab
		if ( !array_key_exists( 'edit', $content_actions ) ) return true;

		global $wgUser, $wgRequest;
		$action = $wgRequest->getText( 'action' );

		// Find position of edit button
		$editpos = isset( $content_actions['edit'] ) ?
			count( range( 0, $content_actions['edit'] ) ) + 1 :
			count( $content_actions );

		// Build WIEdit tab
		global $wgSPMRenameTab;
		$article = new Article( $wgTitle );
		$oldid = $article->getOldID() == 0 ? '' : 'oldid=' . $article->getOldID() . '&';
		$wiaction = ( $action == 'wiedit' ) ?
				array(
		        'class' => 'selected',
		        'text' => 'Done',
		        'href' => $wgTitle->getLocalUrl()
				) :
				array(
		        'class' => false,
		        'text' => ( $wgSPMRenameTab ? 'Edit' : wfMessage( 'wiedit_tab' )->text() ),
		        'href' => $wgTitle->getLocalUrl( $oldid . 'action=wiedit' )   // where it links to
				);
		if ( $wgSPMRenameTab ) {
			$main_action = array(
				'edit' => $wiaction,
				'editsrc' => $content_actions['edit']
			);
			$main_action['editsrc']['text'] = 'edit source';

			// Split array
			$beforeedit = array_slice( $content_actions, 0, $editpos - 1 );
		} else {
			$main_action['wiedit'] = $wiaction;
			// Split array
			$beforeedit = array_slice( $content_actions, 0, $editpos );
		}

		$afteredit = array_slice( $content_actions, $editpos, count( $content_actions ) );
		// Merge array with new action
		$content_actions = array_merge( $beforeedit, $main_action );   // add a new action
		$content_actions = array_merge( $content_actions, $afteredit );

		return true;
	}

	// ObjectModel scripts callback
	// includes necessary script and css files.
	public static function registerResourceModules() {
		global $wgResourceModules, $wgSPMIP, $wgSPMScriptPath;
		wfSPMGetLocalJSLanguageScripts( $pathlng, $userpathlng );

		$moduleTemplate = array(
			'localBasePath' => $wgSPMIP,
			'remoteBasePath' => $wgSPMScriptPath,
			'group' => 'ext.wes'
		);

		$wgResourceModules['ext.wes.inline'] = $moduleTemplate + array(
			'scripts' => array(
				'scripts/Language/SPMLanguage.js',
				$pathlng,
				$userpathlng,
				'scripts/inline_editor/spm_inline_editor.js' ),
			'styles' => array( 'skins/inline_editor/spm_inline_editor.css' ),
			'dependencies' => array(
		      'ext.jquery.fancybox',
			)
		);
	}

	static function addHTMLHeader( &$out ) {
		$out->addScript( '<script type="text/javascript">var spm_objs = [];</script>' . "\n" );

		// MediaWiki 1.17 introduces the Resource Loader.
		$realFunction = array( 'SMWOutputs', 'requireResource' );
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
			$out->addModules( 'ext.wes.inline' );
		} else {
			global $wgSPMScriptPath;
			$out->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/scripts/fancybox/jquery.fancybox-1.3.4.css'
					) );
			$out->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/inline_editor/spm_inline_editor.css'
					) );

			wfSPMGetJSLanguageScripts( $pathlng, $userpathlng );

			// might be script collision, ScriptManager extension will handle this
			$out->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/prototype.js"></script>' );
			$out->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/Language/SPMLanguage.js"></script>' );
			$out->addScript( '<script type="text/javascript" src="' . $pathlng . '"></script>' );
			$out->addScript( '<script type="text/javascript" src="' . $userpathlng . '"></script>' );
			$out->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/jquery-1.4.3.min.js"></script>' );
			$out->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/fancybox/jquery.fancybox-1.3.4.pack.js"></script>' );
			$out->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/inline_editor/spm_inline_editor.js"></script>' );
		}

		return true; // do not load other scripts or CSS
	}

	static $wom = array(); // 'page_name'=>title full text, 'wom' => WOM
	static $editorBound = false;
	public static function parserBeforeInternalParse( &$parser, &$text, &$state ) {
		if ( SPMInlineEditor::$editorBound ) return true;

		if ( !$parser->getTitle()->exists() ) return true;

		$wom = WOMProcessor::parseToWOM( $text );
		$text = SPMProcessor::getInlineEditText( $wom, count( SPMInlineEditor::$wom ) . '_' );
		SPMInlineEditor::$wom[] = array( 'page_name' => $parser->getTitle()->getFullText(), 'wom' => $wom );

		// FIXME: the first text piece should always be page content. not good
		SPMInlineEditor::$editorBound = true;

		return true;
	}

	public static function parserBeforeTemplateParse( &$parser, $title, &$text ) {
		if ( $title->getNamespace() == NS_TEMPLATE ) return true;

		if ( !$title->exists() ) return true;
		// deal with transclusion only
		$wom = WOMProcessor::parseToWOM( $text );
		$text = SPMProcessor::getInlineEditText( $wom, count( SPMInlineEditor::$wom ) . '_' );
		SPMInlineEditor::$wom[] = array( 'page_name' => $title->getFullText(), 'wom' => $wom );

		return true;
	}

	// revision check on save
	// ajax call to get editor
	// render to iframe, inline div could be better, tbd
	// save via ajax
	// refresh current page, inline html update if possible (not for now, have to deal with js)

	// categories are shown in includes/Skin.php, function getCategories()
	// there is no hook available for the inline editor
	// just put it away for the prototype
	public static function bindEditor( &$out ) {
		$script = '';

		foreach ( SPMInlineEditor::$wom as $prefix => $obj ) {
			$page_name = $obj['page_name'];
			$wom = $obj['wom'];

			$title = Title::newFromText( $page_name );
			$article = new Article( $title );
			$rid = 0;
			if ( $out->getTitle()->getFullText() == $page_name ) {
				$rid = $article->getOldID();
				if ( $rid == 0 ) {
					$revision = Revision::newFromTitle( $title );
					if ( $revision === null ) continue;
					$rid = $revision->getId();
				}
			}

			foreach ( $wom->getObjectSet() as $id => $obj ) {
				$link = Title::newFromText( "Special:ObjectEditor/{$rid}/{$id}/{$page_name}" );
				$url = $link->getFullUrl();

				$title = ( ( $out->getTitle()->getFullText() != $page_name ) ? "Be careful! Editing another page!! " : "" ) .
					"Editing {$obj->getTypeID()} in {$page_name} ";

				$url = str_replace( "'", "\\'", $url );
				$title = str_replace( "'", "\\'", $title );

				$script .= "spm_objs.push({
					id:'{$prefix}_{$id}',
					url:'{$url}',
					title:'{$title}'});\n";
			}
		}
		$out->addScript( '<script type="text/javascript">
' . $script . '
</script>' );

		return true;
	}
}