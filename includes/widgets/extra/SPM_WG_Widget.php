<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 */

class SPMWidgetExtraWidget extends SPMWidgetExtra {
	public function __construct() {
		parent::__construct( SPM_WG_WIDGET );
	}

	public function onAjaxAccess( $method, $params ) {
		if ( $method == "getWGWidgetWidgets" ) {
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				'page',
				'page_title',
				array( 'page_namespace' => NS_WIDGET ),
				__METHOD__
			);
			$ret = array();
			while ( $s = $res->fetchObject() ) {
				$ret[] = '"' . str_replace( '"', '\"', $s->page_title ) . '"';
			}
			$dbr->freeResult( $res );

			return '[' . implode( ', ', $ret ) . ']';
		} elseif ( $method == "getWGDefaultWidget" ) {
			$widget_wiki = array_shift( $params );

			$ws = SPMArticleUtils::parsePageTemplates( trim( $widget_wiki ), true );
			foreach ( $ws as $w ) {
				if ( is_array( $w ) ) {
					$name = preg_replace( '/^\s*#widget:\s*/i', '', $w['name'] );
					return Title::newFromText( $name )->getText();
				}
			}
			return "";
		} elseif ( $method == "getWGWidgetFields" ) {
			$widget = trim( array_shift( $params ) );
			$widget_wiki = array_shift( $params );

			if ( $widget == '' ) return "[]";

			$title = Title::newFromText( $widget, NS_WIDGET );
			$rev = Revision::newFromTitle( $title );
			$text = $rev->getText();
			// FIXME: get easy fields
			if ( !preg_match_all( '/\{\$([^|}]+)[|}]/', $text, $m ) ) return "[]";

			$ws = SPMArticleUtils::parsePageTemplates( trim( $widget_wiki ), true );
			$f = array();
			foreach ( $ws as $w ) {
				if ( is_array( $w ) ) {
					$name = preg_replace( '/^\s*#widget:\s*/i', '', $w['name'] );
					if ( Title::newFromText( $name )->getText() == $title->getText() ) {
						$f = $w['fields'];
					}
					break;
				}
			}
			$fields = array();
			$field_names = array();
			foreach ( $m[1] as $key ) {
				if ( isset( $field_names[$key] ) ) continue;
				$field_names[$key] = true;
				$v = '';
				if ( isset( $f[$key] ) ) {
					$v = str_replace( '"', '\"', $f[$key] );
				}
				$fields[] = '["' . str_replace( '"', '\"', $key ) . '", "' . $v . '"]';
			}
			return '[' . implode( ',', $fields ) . ']';
		}

		return FALSE;
	}

	public function registerResourceModules() {
		global $wgResourceModules, $wgSPMIP, $wgSPMScriptPath;

		$moduleTemplate = array(
			'localBasePath' => $wgSPMIP,
			'remoteBasePath' => $wgSPMScriptPath,
			'group' => 'ext.wes.spm_extra'
		);

		$wgResourceModules['ext.wes.spm_extra.widget'] = $moduleTemplate + array(
			'scripts' => array( 'scripts/wf_designer/widgets/extra/widgetext.js' ),
		);
	}

	public function addHTMLHeader() {
		global $wgOut, $wgSPMScriptPath;

		// FIXME: MW 1.17 resource loader cannot handle dynamic script inside lazy load scripts

//		// MediaWiki 1.17 introduces the Resource Loader.
//		$realFunction = array( 'SMWOutputs', 'requireResource' );
//		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
//			$wgOut->addModules('ext.wes.spm_extra.widget');
//		} else {
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/widgets/extra/widgetext.js"></script>' );
//		}
	}

	public function getListString() {
		$str = parent::getListString();
		return $str;
	}

	public function getWidgetWikiHtml( $html ) {
		// FIXME: hard code here
		$idx = strpos( $html, '<div class="spm_wf_widget">' );
		if ( $idx === FALSE ) return FALSE;

		$html = substr( $html, $idx + strlen( '<div class="spm_wf_widget">' ) );
		$html = substr( $html, 0, strrpos( $html, '</div>' ) );

		return $html;
	}

	public function getWikiWidgetView( $text ) {
		if ( preg_match( '/^\s*\{\{\s*#widget\s*:/i', $text ) ) {
			$tmpls = SPMArticleUtils::parsePageTemplates( trim( $text ) );
			if ( $tmpls[2] == false ) {
				$text = trim( $text );
				return '
<div class="spm_wf_widget"><div class="spm_wf_wiki_body">' . $text . '</div>
<div class="spm_wf_wiki"><nowiki>' . htmlspecialchars( $text ) . '</nowiki></div></div>';
			}
		}

		return FALSE;
	}
}
