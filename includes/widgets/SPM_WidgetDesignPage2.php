<?php
/**
 * Special handling for category widget design pages,
 * edit template field connector only
 */

if ( !defined( 'MEDIAWIKI' ) )
	die( 1 );

/**
 */
class SPMWidgetDesignPage2 extends SPMWidgetPage {
	function closeShowCategoryWidget() {
		global $wgOut;
		$viewer = new CategoryWidgetDesignViewer2( $this->mTitle );
		$wgOut->addHTML( $viewer->getHTML() );
	}
}

class CategoryWidgetDesignViewer2 extends CategoryWidgetViewer {
	function __construct( $title ) {
		parent::__construct( $title );
	}


	// ObjectModel scripts callback
	// includes necessary script and css files.
	function addHTMLHeader() {
		if ( $this->title->getNamespace() != NS_CATEGORY_WIDGET ) return true;

		global $wgSPMScriptPath, $wgOut;
		// FIXME: MW resource loader issue, @import flag will not work in css resource loader
		$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/jquery-ui/base/jquery.ui.all.css'
					) );

		// MediaWiki 1.17 introduces the Resource Loader.
		$realFunction = array( 'SMWOutputs', 'requireResource' );
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
			$wgOut->addModules( 'ext.wes.spm_conn' );
		} else {
			parent::addHTMLHeader();

			global $wgSPMWfFancyBoxIncluded;
			if ( !$wgSPMWfFancyBoxIncluded ) {
				$wgSPMWfFancyBoxIncluded = true;
				$wgOut->addLink( array(
							'rel'   => 'stylesheet',
							'type'  => 'text/css',
							'media' => 'screen, projection',
							'href'  => $wgSPMScriptPath . '/scripts/fancybox/jquery.fancybox-1.3.4.css'
						) );

				$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/fancybox/jquery.fancybox-1.3.4.pack.js"></script>' );
		    }

			$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/wf_conn_designer/spm_wf_fld_conn_designer.css'
					) );

			$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/wf_designer/spm_wf_designer.css'
					) );

			// might be script collision, ScriptManager extension will deal this
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_conn_designer/spm_wf_fld_conn_designer.js"></script>' );
		}

		SPMWidgetViewUtils::initialize();

		$script = '';
		foreach ( SPMWidgetViewUtils::$views as $v ) {
			$script .= "
spm_wf_editor.view['{$v->getTypeID()}'] = {
	name: \"" . str_replace( '"', '\"', $v->getName() ) . "\"
};";
		}
		$wgOut->addScript( '
<script type="text/javascript">
' . $script . '
</script>' );

		foreach ( SPMWidgetViewUtils::$views as $v ) {
			$v->addHTMLHeader();
		}

		return true; // do not load other scripts or CSS
	}

	static function getExpressionHelpHtml() {
		global $smwgQMaxInlineLimit;
		return '
<div id="fbpl_overlay"></div>
<div id="fbpl" class="shadow">
	<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%" style="z-index:200000111">
		<tr>
			<td class="shadowtl">&nbsp;</td>
			<td class="shadowtbg">
				<h3>Expression help<a class="close2" id="small_pl" style="right:10px;"></a></h3>
			</td>
			<td class="shadowtr">&nbsp;</td>
		</tr><tr>
			<td class="shadowlbg">&nbsp;</td>
			<td>
				<div id="fbpl_accordion">
					<h3><a href="#">Expression</a></h3>
					<div class="stylized" style="width:375px;">
						<div class="remarkps">
			              <span class="small" style="text-align:left;width:300px;">
			              !!! Replace field with "{{{source_id}}}" if you want.
			              </span>
						  <textarea id="spm_wf_expression_txt"></textarea>
			              <div style="clear:both;"></div>
						</div>
					</div>
					<h3><a href="#">Tools</a></h3>
					<div class="stylized">
					<div class="remarkps">
						<table style="width:375px;"><tr><td>
							<tr>
					            <td>
									<div id="wf_wd_exp_tabs">
										<ul>
											<li><a href="#tabs-0">Parser functions</a></li>
											<li><a href="#tabs-1">Enumeration</a></li>
											<li><a href="#tabs-2">Semantic query</a></li>
										</ul>
										<div id="tabs-0">
									              <select id="spm_wf_exp_pfs"></select>
									              <div style="clear:both;"></div>
									              <span class="small" style="text-align:left;width:300px;">
									              [ <a href="http://www.mediawiki.org/wiki/Help:Extension:ParserFunctions" target="_blank">help</a> ]
									              </span>
									              <a id="spm_wf_exp_apf">Append</a>
										</div>
										<div id="tabs-1">
									              <textarea id="spm_wf_exp_allows"></textarea>
									              <span class="small" style="text-align:left;width:300px;">
									              Each line stands for one value.</span>
									              <a id="spm_wf_exp_aenum">Append</a>
									              <div style="clear:both;"></div>
										</div>
										<div id="tabs-2">
									              <span class="small" style="text-align:left;width:300px;">
									              The result set of query => possible values
									' . ( ( defined( 'SMW_HALO_VERSION' ) && version_compare( SMW_HALO_VERSION, '1.5', '>=' ) ) ? '
									<br/><a id="spm_wf_exp_qi">use query interface</a>' : '' ) . '
									              </span>
									              <textarea id="spm_wf_exp_query"></textarea>
									              <span class="small" style="text-align:left;width:300px;">
									              Each line stands for one query. <br/>
									              Maximum ' . $smwgQMaxInlineLimit . ' results for each query. <br/>
									              </span>
									              <a id="spm_wf_exp_aquery">Append</a>
										</div>
									</div>
								</td>
							</tr>
						</table>
					</div>
					</div>
				</div>
			</td>
			<td class="shadowrbg">&nbsp;</td>
		</tr><tr>
			<td class="shadowlbg">&nbsp;</td>
			<td bgcolor="#ffffff">
				<span style="float:right; margin-top:5px;">
					<input type="button" value="Update" class="submit fb" id="spm_wf_update"/>
					<input type="button" value="Cancel" class="submit fb" id="spm_wf_cancel"/>
				</span>
		  </td>
			<td class="shadowrbg">&nbsp;</td>
		</tr>
		<tr>
			<td class="shadowbl">&nbsp;</td>
			<td class="shadowbbg">&nbsp;</td>
			<td class="shadowbr">&nbsp;</td>
		</tr>
	</table>
</div>
		';
	}

	static function getToolbarHtml() {
		return '
        <div style="display:block;padding:10px;" class="stylized">
          <div style="margin:10px;" class="buttons">
            <button id="spm_wf_conn_reset">reset</button> |
            <button id="spm_wf_conn_reloadp">reload from parent(s)</button>
          </div>
          <h1>Select a connector component to edit</h1>
          <div style="margin:10px;" class="buttons">
          	<button id="spm_wf_conn_add">add connector</button> |
            <button id="spm_wf_conn_add_src">add source field</button>
          </div>
        </div>
        <div id="spm_wf_info">
          <div id="spm_wf_info_msg"> </div>
          <div style="margin:10px;" class="buttons">
            <button class="positive" style="display:none" id="spm_wf_editor_save">save change</button>
          </div>
        </div>
        ';
	}
	function updateTemplateView( $text ) {
		$new_text = '';
		$tmpls = SPMArticleUtils::parsePageTemplates( $text );
		$lastLF = false;
		foreach ( $tmpls as $t ) {
			if ( !is_array( $t ) ) {
				$new_text .= $t;
				continue;
			}
			$tmpl_text = $this->getViewWiki( $t );
			$view = '';
			if ( array_key_exists( $t['name'], SPMWidgetUtils::$widgetTemplates ) ) {
				$view = SPMWidgetUtils::$widgetTemplates[$t['name']];

				$reserved = ( SPMWidgetUtils::$widgetTemplates[ $t['name'] ] == '' );
				if ( !$reserved && preg_match( '/\{\{\{([^|}]+)(\||\})/', $t['fields'][2], $m ) ) {
					$field = trim( $m[1] );
					$tmpl_text .= "<div class=\"spm_wf_field_settings\"><nowiki>{$t['fields'][0]}
{$t['fields'][1]}
{$field}
{$view}</nowiki></div>";
				}
			}
			if ( in_array( $t['name'], SPMWidgetUtils::$boundTemplates ) ) {
				$new_text .= '<div class="spm_wf_bound spm_wf_b_' . $view . '">
' . $tmpl_text . '
</div>';
			} else {
				$new_text .= $tmpl_text;
			}
		}
		return $new_text;
	}
	function loadParentWidgets( $widgets ) {
		$wiki = '';
		foreach ( $widgets as $w ) {
			$tit = Title::newFromText( $w['category'], NS_CATEGORY_WIDGET );
			$wiki .= wfMessage( 'wf_spm_parent', $tit->getFullURL( 'action=wcedit' ), $tit->getText() )->text();
			$wiki .= $this->loadWidget( $w['value'], true );
		}
		return $wiki;
	}
	function loadWidgetView() {
		$cate = $this->title->getText();
		SPMWidgetUtils::getSuperWidgetProperties( $cate, $widgets );

		$wiki = '
<div id="spm_wf_main_container">
<div id="spm_wf_main">
' . $this->loadParentWidgets( $widgets ) . '
<div id="spm_wf_current">
';

		foreach ( SPMWidgetUtils::getWidgetProperties( $cate ) as $w ) {
			$wiki .= $this->loadWidget( $w['value'], true );
		}

		$wiki .= '
</div>
</div>
</div>';


		$wiki .= "__NOEDITSECTION____NOTOC__";

		global $wgParser, $wgUser;
		$options = ParserOptions::newFromUser( $wgUser );
		$html = $wgParser->parse( $wiki, $this->title, $options );

		return $html->getText();
	}

	function getHTML() {
		wfProfileIn( __METHOD__ );
		$this->addHTMLHeader();

		$html = CategoryWidgetDesignViewer2::getToolbarHtml() .
CategoryWidgetDesignViewer2::getExpressionHelpHtml() . '
<h1>' . wfMessgae( 'wf_spm_connector' )->escaped() . '</h1>
' . $this->loadFieldConnectors() . '
<h1>' . wfMessgae( 'wf_spm_preview' )->escaped() . '</h1>
' . $this->loadWidgetView();

		wfProfileOut( __METHOD__ );
		return $html;
	}
}
