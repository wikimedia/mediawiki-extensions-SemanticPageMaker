<?php
/**
 * Special handling for category description pages
 * Modelled after ImagePage.php
 *
 */

if ( !defined( 'MEDIAWIKI' ) )
	die( 1 );

/**
 */
class SPMWidgetDesignPage extends SPMWidgetPage {
	function closeShowCategoryWidget() {
		global $wgOut;
		$viewer = new CategoryWidgetDesignViewer( $this->mTitle );
		$wgOut->addHTML( $viewer->getHTML() );
	}
}

class CategoryWidgetDesignViewer extends CategoryWidgetViewer {
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
			$wgOut->addModules( 'ext.wes.spm_designer' );
		} else {
			parent::addHTMLHeader();

			$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/wf_designer/fg.menu.css'
					) );
			$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/scripts/colorpicker/css/colorpicker.css'
					) );
			$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/wf_designer/spm_wf_designer.css'
					) );
			$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/wf_designer/inettuts.css'
					) );
			$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/wf_designer/inettuts.js.css'
					) );

			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/fg.menu.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/colorpicker/js/colorpicker.js"></script>' );

			// might be script collision, ScriptManager extension will handle this
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wysiwyg/ckeditor/ckeditor.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wysiwyg/script.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/spm_wf_designer.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/inettuts.js"></script>' );
		}

		SPMWidgetDataTypeUtils::initialize();
		SPMWidgetViewUtils::initialize();
		SPMWidgetExtraUtils::initialize();

		$script = '';
		foreach ( SPMWidgetDataTypeUtils::$datatypes as $dt ) {
			$script .= "
spm_wf_editor.datatype['{$dt->getTypeID()}'] = {
	name: \"" . str_replace( '"', '\"', $dt->getName() ) . "\",
	list_str: \"" . str_replace( '"', '\"', $dt->getListString() ) . "\"
};";
		}

		foreach ( SPMWidgetViewUtils::$views as $v ) {
			$script .= "
spm_wf_editor.view['{$v->getTypeID()}'] = {
	name: \"" . str_replace( '"', '\"', $v->getName() ) . "\",
	list_str: \"" . str_replace( '"', '\"', $v->getListString() ) . "\"
};";
		}

		foreach ( SPMWidgetExtraUtils::$extras as $e ) {
			$script .= "
spm_wf_editor.extra['{$e->getTypeID()}'] = {
	name: \"" . str_replace( '"', '\"', $e->getName() ) . "\",
	list_str: \"" . str_replace( '"', '\"', $e->getListString() ) . "\"
};";
		}
		$wgOut->addScript( '
<script type="text/javascript">
' . $script . '
</script>' );

		foreach ( SPMWidgetDataTypeUtils::$datatypes as $dt ) {
			$dt->addHTMLHeader();
		}
		foreach ( SPMWidgetViewUtils::$views as $v ) {
			$v->addHTMLHeader();
		}
		foreach ( SPMWidgetExtraUtils::$extras as $e ) {
			$e->addHTMLHeader();
		}

		return true; // do not load other scripts or CSS
	}

	static function getToolbarHtml() {
		return '<div id="spm_wf_toolbar_container" class="stylized">
		<div style="display: block;" id="spm_wf_toolbar" class="myform">
		  <div style="margin:20px;" class="buttons">
			<a href="javascript:void(0);" style="padding: 10px;" id="spm_wf_editor_add_layout">append new layout</a>
			<a href="javascript:void(0);" style="display:none;padding:10px;" class="spm_wf_editor_save positive">save widget(s)</a>
			<a href="javascript:void(0);" class="positive" style="display:none;padding:10px;" id="spm_wf_editor_reset">reset</a>
		  </div>
		</div>
		</div>
		<a href="javascript:void(0);" class="spm_wf_editor_save" id="spm_wf_editor_save2"></a>';
	}

	static function getLayoutHtml() {
		global $wgSPMScriptPath;
		return '
	<div>
		<div class="tablayout">
			<input type="radio" value="1" name="tab_selected_layout" id="tab_selected_layout" checked="true">
			<img src="' . $wgSPMScriptPath . '/skins/wf_designer/img/tab_layout_1column_1_highlight.gif" style="padding-right: 8px;">
		</div>
		<div class="tablayout">
			<input type="radio" value="2" name="tab_selected_layout" id="tab_selected_layout">
			<img src="' . $wgSPMScriptPath . '/skins/wf_designer/img/tab_layout_2column_1_highlight.gif" style="padding-right: 8px;">
		</div>
		<div class="tablayout">
			<input type="radio" value="3" name="tab_selected_layout" id="tab_selected_layout">
			<img src="' . $wgSPMScriptPath . '/skins/wf_designer/img/tab_layout_3column_1_highlight.gif" style="padding-right: 8px;">
		</div>
	</div>
	<div style="clear:both;"></div>
	';
		}

	static function getFieldDesignerHtml() {
		return '
<div id="fbpl_overlay"></div>
<div id="fbpl" class="shadow">
	<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%" style="z-index:200000111">
		<tr>
			<td class="shadowtl">&nbsp;</td>
			<td class="shadowtbg">
				<h3>Field settings<a class="close2" id="small_pl" style="right:10px;"></a></h3>
			</td>
			<td class="shadowtr">&nbsp;</td>
		</tr><tr>
			<td class="shadowlbg">&nbsp;</td>
			<td>
				<div id="fbpl_accordion">
				<h3><a href="#">Common</a></h3>
				<div>
				<div class="remarkps">
				<table class="stylized" style="width:375px;">
					<tr>
						<td>
							<label style="width:120px;">Label
								<span class="small" style="width:120px;">Label of the element</span>
							</label>
							<input id="spm_wf_label" value="" type="text">

							<label style="width:120px;">Field
								<span class="small" style="width:120px;">ID, "=label" on blank</span>
							</label>
							<input id="spm_wf_field" value="" type="text">

							<div style="clear:both;"></div>
						</td>
					</tr><tr>
						<td>
							<hr size="1" color="#b7ddf2" />
							<label style="width:200px; text-align:left; margin-left:10px;">Description
								<span style="width:200px" class="small">Hint on widget field editing</span>
							</label>
							<input id="spm_wf_prop_description" value="" type="text" style="width:330px">
						</td>
					</tr><tr>
						<td>
							<hr size="1" color="#b7ddf2" />
							<label style="width:200px; text-align:left; margin-left:10px;">Access Control
								<span style="width:260px" class="small">Editable userset(query syntax), blank for all</span>
							</label>
							<input id="spm_wf_prop_aclquery" value="" type="text" style="width:330px">
						</td>
					</tr>
				</table>
				</div>
				</div>
				<h3><a href="#">Datatype</a></h3>
				<div>
				<div class="remarkps">
					<div class="myform">
						<table class="stylized" style="width:375px;"><tr><td>
							<label style="width:110px;">Data
								<span class="small" style="width:110px;">Type of element</span>
							</label>
							<div id="spm_wf_editor_datatype_list" style="display:inline;top:5px"
								class="fg-button ui-widget ui-state-default ui-corner-all">&nbsp;
								<span class="ui-icon ui-icon-triangle-1-s"></span>
								<input id="spm_wf_editor_datatype" style="width:150px" type="text" value="" readonly="readonly"/>
							</div>
							<div style="clear:both;"></div>
						</td></tr></table>

						<div class="fl">
							<table class="stylized" style="width:375px;"><tr><td>
								<label style="width:230px;text-align: left; margin-left: 10px;">Other data type definition here.
									<span class="small" style="width:200px;">E.g., text, date, link</span>
								</label>
							</td></tr></table>
						</div>
					</div>
				</div>
				</div>
				<h3><a href="#">View</a></h3>
				<div>
				<div class="remarkps">
					<div class="myform">
						<table class="stylized" style="width:375px;"><tr><td>
							<label style="width:110px;">View
								<span class="small" style="width:110px;">Element to create</span>
							</label>
							<div id="spm_wf_editor_view_list" style="display:inline;top:5px;"
								class="fg-button ui-widget ui-state-default ui-corner-all">&nbsp;
								<span class="ui-icon ui-icon-triangle-1-s"></span>
								<input id="spm_wf_editor_view" style="width:150px" type="text" value="" readonly="readonly"/>
							</div>
							<div style="clear:both;"></div>
						</td></tr></table>

						<div class="fr">
							<table class="stylized" style="width:375px;"><tr><td>
								<label style="width:230px;text-align: left; margin-left: 10px;">Other type related actions here.
									<span class="small" style="width:200px;">E.g., for infobox field, can "create a new infobox" or "append to the latest"</span>
								</label>
							</td></tr></table>
						</div>
					</div>
				</div>
				</div>
				</div>
			</td>
			<td class="shadowrbg">&nbsp;</td>
		</tr><tr>
			<td class="shadowlbg">&nbsp;</td>
			<td bgcolor="#ffffff">
				<span style="float:right; margin-top:5px;">
					<input type="button" value="Submit" class="submit fb" id="spm_wf_updatefield"/>
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



<div style="display: none" id="spm_wf_editor_datatype_content"><ul></ul></div>
<div style="display: none" id="spm_wf_editor_view_content"><ul></ul></div>
<div style="display: none" id="spm_wf_editor_widget_content"><ul></ul></div>
';
	}
	function getDesignerUI() {
		return CategoryWidgetDesignViewer::getToolbarHtml() .
//			CategoryWidgetDesignViewer::getLayoutHtml() .
			CategoryWidgetDesignViewer::getFieldDesignerHtml() .
			$this->loadWidgetView();
	}

	private function getFieldSettings( $wf_template, &$view ) {
		$view = SPMWidgetUtils::$widgetTemplates[$wf_template['name']];
		$view_instance = SPMWidgetViewUtils::getViewInstanceByTypeID( $view );
		$prop_instance = SPMWidgetUtils::getPropertySettings( $wf_template['fields'][1] );
		$r = preg_match( '/\{\{\{([^|}]+)(\||\})/', $wf_template['fields'][2], $m );
		if ( !$r ) return "";
		$field = trim( $m[1] );
		$sample_wiki = $prop_instance['prop_instance']->getSampleWiki( $prop_instance['title'], '' );

		return "{$wf_template['fields'][0]}
{$wf_template['fields'][1]}
{$field}
{$sample_wiki}
{$view}
{$prop_instance['prop_instance']->getName()}
{$view_instance->getFieldSettings( $wf_template['fields'] )}
{$prop_instance['prop_instance']->getFieldSettings( $prop_instance['title'], $wf_template['fields'] )}";
	}

	private function matchHtmlTag( $wom, $tagname, $attributes ) {
		if ( $wom->getTypeID() != WOM_TYPE_HTMLTAG )
			return false;
		if ( strtolower( trim( $wom->getName() ) ) != strtolower( trim( $tagname ) ) )
			return false;

		$attrs = $wom->getAttributes();
		foreach ( $attrs as $a => $v ) {
			$a = strtolower( $a );
			$v = strtolower( $v );

			$v = preg_replace( '/^[\'"](.*)[\'"]$/', '$1', $v );
			if ( $a == 'class' ) {
				$classes = array();
				foreach ( explode( ' ', $v ) as $c ) {
					if ( $c == '' ) continue;
					$classes[strtolower( $c )] = true;
				}
				$v = $classes;
			}
			$attrs[$a] = $v;
		}

		foreach ( $attributes as $a => $v ) {
			$a = strtolower( $a );
			$v = strtolower( $v );

			if ( $a == 'id' ) $a = 'tag_id';
			$v = preg_replace( '/^[\'"](.*)[\'"]$/', '$1', $v );

			if ( !isset( $attrs[$a] ) ) return false;
			if ( $a == 'class' ) {
				foreach ( explode( ' ', $v ) as $c ) {
					if ( $c == '' ) continue;
					if ( !isset( $attrs[$a][$c] ) ) return false;
				}
			} else {
				if ( $v != $attrs[$a] ) return false;
			}
		}

		return true;
	}
	function parseToLayoutWidgets( $parent, &$container ) {
		if ( !( $parent instanceof WikiObjectModelCollection ) ) return;
		// FIXME: hard code here
		foreach ( $parent->getObjects() as $obj ) {
			if ( $this->matchHtmlTag( $obj, 'div', array( 'class' => 'layout-content' ) ) ) {
				$columns = array();
				foreach ( $obj->getObjects() as $column ) {
					if ( $this->matchHtmlTag( $column, 'ul', array( 'class' => 'column' ) ) ) {
						$widgets = array();
						foreach ( $column->getObjects() as $widget ) {
							if ( $this->matchHtmlTag( $widget, 'li', array( 'class' => 'widget' ) ) ) {
								$content = '';
								foreach ( $widget->getObjects() as $cobj ) {
									if ( $this->matchHtmlTag( $cobj, 'div', array( 'class' => 'widget-content' ) ) ) {
										$content .= $cobj->getInnerWikiText();
									}
								}
								$widgets[] = array( 'class' => $widget->getAttribute( 'class' ), 'style' => $widget->getAttribute( 'style' ), 'content' => $content );
							}
						}
						$columns[] = array( 'class' => $column->getAttribute( 'class' ), 'widgets' => $widgets );
					}
				}
				$container[] = $columns;
			} elseif ( $this->matchHtmlTag( $obj, 'div', array( 'style' => 'clear:both;' ) ) ) {
				;
			} else {
				$this->parseToLayoutWidgets( $obj, $container );
			}
		}
	}

	function renderWikiView( $text ) {
		foreach ( SPMWidgetExtraUtils::$extras as $e ) {
			$h = $e->getWikiWidgetView( $text );
			if ( $h !== FALSE ) return $h;
		}

		$new_text = '';
		$tmpls = SPMArticleUtils::parsePageTemplates( $text );
		$plain = '';
		$reserved = false;

		foreach ( $tmpls as $t ) {
			if ( !is_array( $t ) ) {
				$plain .= $t;
				continue;
			}
			if ( array_key_exists( $t['name'], SPMWidgetUtils::$widgetTemplates ) ) {
				if ( !$reserved ) {
//					$plain = preg_replace('/^\n+/', '', $plain);
					$plain = preg_replace( '/\n+$/', "\n", $plain );
					if ( trim( $plain ) == '' ) $plain = '';
					$new_text .= '
<div class="spm_wf_plain"><div class="spm_wf_wiki_body">' . $plain . '</div>
<div class="spm_wf_wiki"><nowiki>' . htmlspecialchars( $plain ) . '</nowiki></div></div>';
				}
				$new_text .= "\n";
				$plain = '';

				$tmpl_text = $this->getViewWiki( $t );
				$tmpl_text .= "\n<div class=\"spm_wf_wiki\"><nowiki>" . htmlspecialchars( SPMArticleUtils::templateToWiki( $t ) ) . "</nowiki></div>";

				$reserved = ( SPMWidgetUtils::$widgetTemplates[ $t['name'] ] == '' );
				if ( !$reserved ) {
					$tmpl_text .= "<div class=\"spm_wf_field_settings\"><nowiki>{$this->getFieldSettings( $t, $view )}</nowiki></div>";
				}

				if ( in_array( $t['name'], SPMWidgetUtils::$boundTemplates ) ) {
					$new_text .= '<div class="spm_wf_bound spm_wf_b_' . preg_replace( '/\s/', ' ', $view ) . '">
' . $tmpl_text . '
</div>';
				} else {
					$new_text .= $tmpl_text;
				}
			} else {
				$plain .= SPMArticleUtils::templateToWiki( $t );
			}
		}
		if ( !$reserved ) {
//			$plain = preg_replace('/^\n+/', '', $plain);
			$plain = preg_replace( '/\n+$/', "\n", $plain );
			$new_text .= '
<div class="spm_wf_plain"><div class="spm_wf_wiki_body">' . $plain . '</div>
<div class="spm_wf_wiki"><nowiki>' . htmlspecialchars( $plain ) . '</nowiki></div></div>';
		}

		return $new_text;
	}

	function renderLayoutWidgetOnFreeWidgets( $text ) {
		$new_text = '
<div class="layout">
	<div class="layout-head"></div>
	<div class="layout-content">
        <ul class="column column1">
        	<li class="widget">
				<div class="widget-head">&nbsp;</div>
				<div class="widget-content">';

		$tmpls = SPMArticleUtils::parsePageTemplates( $text );
		$plain = '';
		$reserved = false;

		$table = false;

		foreach ( $tmpls as $t ) {
			if ( !is_array( $t ) ) {
				$plain .= $t;
				continue;
			}
			if ( array_key_exists( $t['name'], SPMWidgetUtils::$widgetTemplates ) ) {
				if ( !$reserved ) {
//					$plain = preg_replace('/^\n+/', '', $plain);
					$plain = preg_replace( '/\n+$/', "\n", $plain );
					if ( trim( $plain ) == '' ) $plain = '';
					$new_text .= '
<div class="spm_wf_plain"><div class="spm_wf_wiki_body">' . $plain . '</div>
<div class="spm_wf_wiki"><nowiki>' . htmlspecialchars( $plain ) . '</nowiki></div></div>';
				}
				$new_text .= "\n";
				$plain = '';

				if ( $table === false || $table == 'end' ) {
					$new_text .= '</div></li>';
				}

				$table = SPMWidgetTableRowView::tableTemplates( $t['name'] );

				if ( $table === false || $table == 'begin' ) {
					$new_text .= '
			<li class="widget">
				<div class="widget-head">&nbsp;</div>
				<div class="widget-content">';
				}

				$tmpl_text = $this->getViewWiki( $t );
				$tmpl_text .= "\n<div class=\"spm_wf_wiki\"><nowiki>" . htmlspecialchars( SPMArticleUtils::templateToWiki( $t ) ) . "</nowiki></div>";

				$reserved = ( SPMWidgetUtils::$widgetTemplates[ $t['name'] ] == '' );
				if ( !$reserved ) {
					$tmpl_text .= "<div class=\"spm_wf_field_settings\"><nowiki>{$this->getFieldSettings( $t, $view )}</nowiki></div>";
				}

				if ( in_array( $t['name'], SPMWidgetUtils::$boundTemplates ) ) {
					$new_text .= '<div class="spm_wf_bound spm_wf_b_' . preg_replace( '/\s/', ' ', $view ) . '">
' . $tmpl_text . '
</div>';
				} else {
					$new_text .= $tmpl_text;
				}
			} else {
				$plain .= SPMArticleUtils::templateToWiki( $t );
			}
		}
		if ( !$reserved ) {
//			$plain = preg_replace('/^\n+/', '', $plain);
			$plain = preg_replace( '/\n+$/', "\n", $plain );
			if ( trim( $plain ) == '' ) $plain = '';

			$new_text .= '
<div class="spm_wf_plain"><div class="spm_wf_wiki_body">' . $plain . '</div>
<div class="spm_wf_wiki"><nowiki>' . htmlspecialchars( $plain ) . '</nowiki></div></div>';
		}

		$new_text .= '
				</div>
			</li>
		</ul>
        <div style="clear:both;"></div>
        </div></div>';

		return $new_text;
	}
	function updateTemplateView( $text, $layout_exclude = false ) {
		if ( $layout_exclude ) return $this->renderWikiView( $text );

// <ul class="column column([123])"><li class="widget"><div class="widget-content"></div></li></ul>
// preg_match_all('(</ul>)?(<div style="clear:both;">)?<ul class="column column([123])">', $plain, $m);
// preg_match_all('(</div></li>)?<li class="widget"><div class="widget-content">', $plain, $m);

		// divide text into layout / widget
		$page_obj = WOMProcessor::parseToWOM( $text );
		$container = array();
		$this->parseToLayoutWidgets( $page_obj, $container );
		if ( count( $container ) == 0 ) {
			return $this->renderLayoutWidgetOnFreeWidgets( $text );
		}

		$new_text = '';
		foreach ( $container as $layout ) {
			$new_text .= '
<div class="layout">
	<div class="layout-head"></div>
	<div class="layout-content">';
			foreach ( $layout as $column ) {
				$new_text .= '
		<ul class="column ' . preg_replace( '/\bcolumn\b/', '', $column['class'] ) . '">';
				foreach ( $column['widgets'] as $widget ) {
					$new_text .= '
			<li class="widget ' . preg_replace( '/\bwidget\b/', '', $widget['class'] ) . '" style="' . $widget['style'] . '">
				<div class="widget-head">&nbsp;</div>
				<div class="widget-content">';

					$new_text .= $this->renderWikiView( $widget['content'] );
					$new_text .= '</div></li>';
				}
				$new_text .= '</ul>';
			}
			$new_text .= '<div style="clear:both;"></div></div></div>';
		}

		return $new_text;
	}

	function getWidgetHtml2( $wiki, $title ) {
		$text = $this->updateTemplateView( "\n" . trim( $wiki ) . "\n", true ) . "__NOEDITSECTION____NOTOC__";
		$text = $this->getTemplatePrototype( $text );

		global $wgParser, $wgUser;
		$options = ParserOptions::newFromUser( $wgUser );
		$html = $wgParser->parse( $text, $title, $options );

		return $html->getText();
	}

	function loadParentWidgets( $widgets ) {
		$wiki = '';
		foreach ( $widgets as $w ) {
			$tit = Title::newFromText( $w['category'], NS_CATEGORY_WIDGET );
			$wiki .= wfMsg( 'wf_spm_parent', $tit->getFullURL( 'action=wfedit' ), $tit->getText() );
			$wiki .= $this->loadWidget( $w['value'] );
		}
		return $wiki;
	}
	function loadWidgetView() {
		$cate = $this->title->getText();
		SPMWidgetUtils::getSuperWidgetProperties( $cate, $widgets );

		$wiki = '
			<div id="spm_wf_info">
			<div id="spm_wf_info_msg"> </div>
			</div>
			' . /*wfMsg('wf_spm_current') .*/  '
			<div id="spm_wf_main_container">
			' . $this->loadParentWidgets( $widgets ) . '
			<div id="spm_wf_main">
			';

		// only one template bound to each widget
		$edit_direct = '';
		$new = true;
		foreach ( SPMWidgetUtils::getWidgetProperties( $cate ) as $w ) {
			$new = false;
			$link = Title::newFromText( $w['value'] );
			$url = $link->getFullURL( 'action=edit' );

//			$wiki .= wfMsg('wf_spm_src_template', $url);
			$edit_direct = wfMsg( 'wf_spm_src_template', $url );

			$wiki .= $this->loadWidget( $w['value'], true );

//			$wiki .='<div style="clear: both;"></div>';
		}

		if ( $new ) {
			$wiki .= $this->updateTemplateView( $text );
		}

		$wiki .= '
			</div></div>';

		$wiki .= wfMsg( 'wf_spm_freetext' );


		$wiki .= "__NOEDITSECTION____NOTOC__";

		global $wgParser, $wgUser, $wgOut, $smwgIQRunningNumber;
		$options = ParserOptions::newFromUser( $wgUser );
		$html = $wgParser->parse( $edit_direct . $wiki, $this->title, $options );
		$wgOut->addScript( '
<script type="text/javascript">
spm_wf_editor.js.$smwgIQRunningNumber=' . $smwgIQRunningNumber . ';
</script>' );

		return $html->getText();
	}

	function getHTML() {
		wfProfileIn( __METHOD__ );
		$this->addHTMLHeader();

		global $wgOut, $wgTitle;
		$wgOut->addWikiText( wfMsg( 'wf_wd_hint_wfedit', Title::newFromText( $wgTitle->getText(), NS_CATEGORY ) ) );

		$html = $this->getDesignerUI();

		wfProfileOut( __METHOD__ );
		return $html;
	}
}
