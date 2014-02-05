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
class SPMWidgetPage extends Article {
	function view() {
		global $wgRequest, $wgUser;

		$diff = $wgRequest->getVal( 'diff' );
		$diffOnly = $wgRequest->getBool( 'diffonly', $wgUser->getOption( 'diffonly' ) );

		if ( isset( $diff ) && $diffOnly )
			return Article::view();

		if ( !wfRunHooks( 'SPMWidgetPageView', array( &$this ) ) )
			return;

		if ( NS_CATEGORY_WIDGET == $this->mTitle->getNamespace() ) {
			$this->openShowCategory();
		}

		Article::view();

		if ( NS_CATEGORY_WIDGET == $this->mTitle->getNamespace() ) {
			$this->closeShowCategoryWidget();
		}
	}

	function openShowCategory() {
		# For overloading
	}

	function closeShowCategoryWidget() {
		global $wgOut;
		$viewer = new CategoryWidgetViewer( $this->mTitle );
		$wgOut->addHTML( $viewer->getHTML() );
	}
}

class CategoryWidgetViewer {
	var $title;

	function __construct( $title ) {
		$this->title = $title;
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
			$wgOut->addModules( 'ext.jquery.fancybox' );
			$wgOut->addModules( 'ext.wes.spm_view' );
		} else {
			wfSPMGetJSLanguageScripts( $pathlng, $userpathlng );
//			$wgOut->addLink( array(
//						'rel'   => 'stylesheet',
//						'type'  => 'text/css',
//						'media' => 'screen, projection',
//						'href'  => $wgSPMScriptPath . '/skins/jquery-ui/base/jquery.ui.all.css'
//					) );
			$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/scripts/fancybox/jquery.fancybox-1.3.4.css'
					) );
			$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/style.css'
					) );
			$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/spm_wf_widgets.css'
					) );

			// might be script collision, ScriptManager extension will handle this
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/jquery-1.4.3.min.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/jquery-ui-1.8.9.custom.min.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/prototype.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/fancybox/jquery.fancybox-1.3.4.pack.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/Language/SPMLanguage.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $pathlng . '"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $userpathlng . '"></script>' );
		}

		SPMWidgetViewUtils::initialize();

		return true; // do not load other scripts or CSS
	}

	function getNewWidgetUI() {
		$category = $this->title->getText();
		return wfMsg( 'wf_spm_create_widget', Title::newFromText( $category, NS_CATEGORY_WIDGET )->getFullURL( 'action=wfedit' ) );
	}

	function getTemplatePrototype( $text ) {
		$new_text = '';
		foreach ( SPMArticleUtils::parsePageTemplates( $text ) as $tmpl ) {
			if ( !is_array( $tmpl ) ) {
				$new_text .= $tmpl;
				continue;
			}
			$tmpl_text = SPMArticleUtils::templateToWiki( $tmpl );
			if ( !array_key_exists( $tmpl['name'], SPMWidgetUtils::$widgetTemplates ) ||
				SPMWidgetUtils::$widgetTemplates[$tmpl['name']] == '' ) {
					$new_text .= $tmpl_text;
					continue;
			}

			$prop_settings = SPMWidgetUtils::getPropertySettings( $tmpl['fields'][1] );
			// parse widget field template only
			foreach ( SPMArticleUtils::parseTemplatePage( $tmpl_text ) as $tf ) {
				if ( !is_array( $tf ) ) {
					$new_text .= $tf;
					continue;
				}
				$new_text .= '{{{' . $tf['field'] . '|' . $prop_settings['prop_instance']->getSampleWiki( $prop_settings['title'], $tf['default'] ) . '}}}';
			}
		}
//		foreach(SPMArticleUtils::parseTemplatePage($text) as $tf) {
//			if(!is_array($tf)) {
//				$new_text .= $tf;
//				continue;
//			}
//			$new_text .= '{{{' . $tf['field'] . '|value}}}';
//		}

		return $new_text;
	}

	protected function getViewWiki( $wf_template ) {
		$wiki = SPMArticleUtils::templateToWiki( $wf_template );

		$view = SPMWidgetUtils::$widgetTemplates[$wf_template['name']];
		if ( $view == '' ) return $wiki;

		$view_instance = SPMWidgetViewUtils::getViewInstanceByTypeID( $view );
		$prop_instance = SPMWidgetUtils::getPropertySettings( $wf_template['fields'][1] );

		$wiki .= $view_instance->getViewWiki( $wf_template['fields'] ) .
			$prop_instance['prop_instance']->getViewWiki( $prop_instance['title'], $wf_template['fields'] );

		return $wiki;
	}
	function updateTemplateView( $text ) {
		return updateTemplateViewBase( $text );
	}
	function updateTemplateViewBase( $text ) {
		$new_text = '';
		$tmpls = SPMArticleUtils::parsePageTemplates( $text );
		$plain = '';
		foreach ( $tmpls as $t ) {
			if ( !is_array( $t ) ) {
				$plain .= $t;
				continue;
			}
			if ( array_key_exists( $t['name'], SPMWidgetUtils::$widgetTemplates ) ) {
				$new_text .= $plain;
				$plain = '';

				$new_text .= $this->getViewWiki( $t );
			} else {
				$plain .= SPMArticleUtils::templateToWiki( $t );
			}
		}
		if ( !$reserved ) {
			$new_text .= $plain;
		}

		return $new_text;
	}
	function getWidgetWiki( $text, $title, $update = false ) {
		if ( !$update ) {
			$text = $this->updateTemplateViewBase( $text );
		} else {
			$text = $this->updateTemplateView( $text );
		}
		$text = $this->getTemplatePrototype( $text );

		return $text;
	}
	function getWidgetHtml( $text, $title, $update = false ) {
		$text = $this->getWidgetWiki( $text, $title, $update ) . "__NOEDITSECTION____NOTOC__";

		global $wgParser, $wgUser;
		$options = ParserOptions::newFromUser( $wgUser );
		$html = $wgParser->parse( $text, $title, $options );

		return $html->getText();
	}
	function loadWidget( $widget, $update = false ) {
		$title = Title::newFromText( $widget );

		if ( floatval( SMW_VERSION ) < 1.5 ) {
			$title = Title::newFromText( $widget, NS_TEMPLATE );
		}
		$revision = Revision::newFromTitle( $title );
		if ( $revision == null ) return '';

		return $this->getWidgetWiki( $revision->getText(), $title, $update );
	}
	function loadParentWidgets( $widgets ) {
		$wiki = '';
		foreach ( $widgets as $w ) {
			$wiki .= $this->loadWidget( $w['value'] );
		}
		return $wiki;
	}
	function loadFieldConnectors() {
		$conn_html = '
<ol id="spm_wf_exps">';
		$conn_html .= SPMWidgetUtils::getFieldConnectorHtml();
		$conn_html .= '
</ol>
';

		return $conn_html;
	}
	function loadWidgetViewWiki() {
		$cate = $this->title->getText();
		SPMWidgetUtils::getSuperWidgetProperties( $cate, $widgets );

		$wiki = $this->loadParentWidgets( $widgets );

		$loaded = false;
		foreach ( SPMWidgetUtils::getWidgetProperties( $cate ) as $w ) {
			$wiki .= $this->loadWidget( $w['value'] );
			$loaded = true;
		}
		if ( !$loaded ) $wiki .= $this->getNewWidgetUI();

		$wiki .= "\n__NOEDITSECTION__\n__NOTOC__";

		return $wiki;
	}
	function loadWidgetView() {
		$wiki = $this->loadWidgetViewWiki();

		global $wgParser, $wgUser;
		$options = ParserOptions::newFromUser( $wgUser );
		$html = $wgParser->parse( $wiki, $this->title, $options );

		return $html->getText();
	}

	function getHTML() {
		wfProfileIn( __METHOD__ );
		$this->addHTMLHeader();

		global $wgSPMScriptPath, $wgOut;
		$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/spm_wf_sf_map.js"></script>' );

		$html = ( defined( 'SF_VERSION' ) ) ? '
<i><a id="spm_wf_create_form" style="cursor:pointer">create a form</a> based on current widget.</i><br/>' : '';

		$link = Title::newFromText( "Special:WidgetClone/{$this->getTitle()->getText()}" );
		$url = $link->getFullUrl();
		$html .= '
<i><a id="spm_wf_clone" href="' . $url . '?' . rand() . '">duplicate current widget</a>.</i>';

		$url = $this->getTitle()->getFullURL( 'action=wcedit' );

		$html .= '
<h1>' . wfMsg( 'wf_spm_preview' ) . '</h1>
<div id="spm_wf_main_container">
' . $this->loadWidgetView() . '
</div>
<h1>' . wfMsg( 'wf_spm_connector' ) . wfMsg( 'wf_spm_connector_edit', $url ) . '</h1>
' . $this->loadFieldConnectors() . '
';

		wfProfileOut( __METHOD__ );
		return $html;
	}
}
