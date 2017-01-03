<?php
/*
 * Author: ning
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	exit( 1 );
}

global $wgAjaxExportList;
global $wgSPMIP;

$wgAjaxExportList[] = 'spm_wf_EditorAccess';


function spm_wf_EditorAccess( $method ) {
	$params = func_get_args();
	array_shift( $params ); // do not need method

	if ( $method == "getConnectorHtmlEmpty" ) {
		return wfMessage( 'wf_wc_html_exp', '', '', wfMessage( 'wf_wc_html_exp_src', '' )->text() )->text();
	} elseif ( $method == "getConnectorSrcHtmlEmpty" ) {
		return wfMessage( 'wf_wc_html_exp_src', '' )->text();
	} elseif ( $method == "getWidgetConnectorHtml" ) {
		$namespace = intval( array_shift( $params ) );
		$name = array_shift( $params );
		if ( $namespace != NS_CATEGORY_WIDGET ) return wfMessage( 'spm_ajax_fail' )->text();

		return SPMWidgetUtils::getFieldConnectorHtml( $name );
	} elseif ( $method == "getParentWidgetConnectorHtml" ) {
		$namespace = intval( array_shift( $params ) );
		$name = array_shift( $params );
		if ( $namespace != NS_CATEGORY_WIDGET ) return wfMessage( 'spm_ajax_fail' )->text();

		$title = Title::newFromText( $name, NS_CATEGORY );
		$html = '';
		foreach ( $title->getParentCategories() as $c => $v ) {
			$html .= SPMWidgetUtils::getFieldConnectorHtml( Title::newFromText( $c )->getText() );
		}

		return $html;
	} elseif ( $method == "updateWidgetConnectors" ) {
		$namespace = intval( array_shift( $params ) );
		$name = array_shift( $params );
		if ( $namespace != NS_CATEGORY_WIDGET ) return wfMessage( 'spm_ajax_fail' )->text();

		$ret = SPMWidgetUtils::updateWidgetConnectors( Title::newFromText( $name, $namespace ), $params );
		if ( $ret === true )
			return wfMessage( 'spm_ajax_success' )->text();

		return $ret;
	} elseif ( $method == "hitTransaction" ) {
		$val = array_shift( $params );
		$multiple = ( strtolower( array_shift( $params ) ) == 'true' );
		$optional = ( strtolower( array_shift( $params ) ) == 'true' );
		$category = array_shift( $params );
		$trans_id = array_shift( $params );

		return SPMWidgetUtils::hitTransaction( $val,
				array(
					'multiple' => $multiple,
					'optional' => $optional,
				),
				$category, $trans_id, $params
			);
	} elseif ( $method == "getFieldDesignerHtml" ) {
		$type = array_shift( $params );
		$key = array_shift( $params );
		$title_name = array_shift( $params );
		switch( $type ) {
			case 'datatype':
				return SPMWidgetDataTypeUtils::getDateType( $key )->getDesignerHtml( $title_name );
			case 'view':
				return SPMWidgetViewUtils::getView( $key )->getDesignerHtml( $title_name );
		}
		return '';
	} elseif ( $method == "getWikiHtml" ) {
		global $smwgIQRunningNumber;
		$smwgIQRunningNumber = array_shift( $params );
		$wiki = array_shift( $params );

		$title = Title::newFromText( "___TEMP" );
		$viewer = new CategoryWidgetDesignViewer( $title );
		$html = $viewer->getWidgetHtml2( $wiki, $title );


		foreach ( SPMWidgetExtraUtils::$extras as $e ) {
			$h = $e->getWidgetWikiHtml( $html );
			if ( $h !== FALSE ) {
				$html = $h;
				break;
			}
		}

		if ( !defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) ) return $html;

		global $wgOut;
		$modules = array_unique( $wgOut->getModules() );
		// add script block which loads the necessary modules
		// also add to the result all the <script> blocks the page contains
		// add script section for resource module loading in result preview
		$moduleScript = '<script type="text/javascript">' .
			'mw.loader.using(["' . implode( '","', $modules ) . '"], spm_wf_editor.js.executeInitMethods);' .
			'spm_wf_editor.js.$smwgIQRunningNumber=' . $smwgIQRunningNumber . ';' .
			'</script>' . $wgOut->getScript();

		return $html . $moduleScript;
	} elseif ( $method == "getFieldHtml" ) {
		$title = array_shift( $params );
		$field = array_shift( $params );
		$label = array_shift( $params );
		$freetext = array_shift( $params );
		$datatype = array_shift( $params );
		$view = array_shift( $params );
//		$context_before = array_shift( $params );
//		$context_current = array_shift( $params );
//		$context_after = array_shift( $params );

		$view = SPMWidgetViewUtils::getView( $view );
		if ( $view == null ) return wfMessage( 'spm_ajax_fail' )->text();

		$datatype = SPMWidgetDataTypeUtils::getDateType( $datatype );
		if ( $datatype == null ) return wfMessage( 'spm_ajax_fail' )->text();

		$extra_params = $datatype->getFieldParameters( $params );

		return $view->getFieldHtml( ( $label == '' ? $field : $label ), $title, $field, $params, $extra_params, $freetext );
	} elseif ( $method == "removeProperty" ) {
		$title = array_shift( $params );
		$name = array_shift( $params );

		$title = Title::newFromText( $title . '/' . $name, SMW_NS_PROPERTY );
		$article = new Article( $title );
		$ret = $article->doDelete( 'Deleted by Widget Designer' );

		return wfMessage( 'spm_ajax_success' )->text();
	} elseif ( $method == "updatePropertyDefinition" ) {
		$title = array_shift( $params );
		$name = array_shift( $params );
		$type = array_shift( $params );
		$dt = SPMWidgetDataTypeUtils::getDateType( $type );
		if ( $dt == null ) return wfMessage( 'spm_ajax_fail' )->text();

		$text = $dt->getPropertyWiki( $params );

		$title = Title::newFromText( $title . '/' . $name, SMW_NS_PROPERTY );
		$page = WikiPage::factory( $title );
		$ret = $page->doEditContent(
			ContentHandler::makeContent( $text, $title ),
			'Edit by Widget Designer'
		);

		return wfMessage( 'spm_ajax_success' )->text();
	} elseif ( $method == "resetPropertyDefinition" ) {
		global $wgUser;
		foreach ( $params as $param ) {
			$s = explode( '|', $param, 2 );
			$rid = intval( $s[0] );
			$name = $s[1];

			// deleted?
			$title = Title::newFromText( $name, SMW_NS_PROPERTY );
			if ( !$title->exists() ) {
				// undelete first
				$pa = new PageArchive( $title );
				$retval = $pa->undelete( array() );
				if ( !is_array( $retval ) ) continue; // cannot undelete
				if ( $retval[1] ) {
					wfRunHooks( 'FileUndeleteComplete',
						array( $title, array(), $wgUser, '' ) );
				}
			}
			$revision = Revision::newFromId( $rid );
			if ( $revision == null ) continue;
			if ( $revision->isCurrent() ) continue;
			// revert
			$summary = wfMessage( 'revertpage', $revision->getUserText(), $wgUser->getName() )->text();

			$page = WikiPage::factory( $revision->getTitle() );
			$status = $page->doEditContent(
				ContentHandler::makeContent( $revision->getText(), $revision->getTitle() ),
				$summary,
				0,
				$rid
			);
		}
		return wfMessage( 'spm_ajax_success' )->text();
	} elseif ( $method == "refreshPropertyRevision" ) {
		$name = array_shift( $params );
		$wiki = '';
		foreach ( SPMWidgetUtils::getWidgetProperties( $name ) as $w ) {
			$title = Title::newFromText( $w['value'] );

			if ( floatval( SMW_VERSION ) < 1.5 ) {
				$title = Title::newFromText( $w['value'], NS_TEMPLATE );
			}

			$revision = Revision::newFromTitle( $title );
			if ( $revision == null ) continue;
			$wiki .= $revision->getText();
		}
		$tmpls = SPMArticleUtils::parsePageTemplates( $wiki );
		SPMWidgetViewUtils::initialize();
		$rid_json = array();

		foreach ( $tmpls as $t ) {
			if ( !is_array( $t ) ) continue;
			if ( array_key_exists( $t['name'], SPMWidgetUtils::$widgetTemplates ) &&
				SPMWidgetUtils::$widgetTemplates[ $t['name'] ] != '' ) {

				$r = Revision::newFromTitle( Title::newFromText( $t['fields'][1], SMW_NS_PROPERTY ) );
				if ( $r !== null ) {
					$rid_json[] = '{
	"name": "' . str_replace( '"', '\"', $t['fields'][1] ) . '",
	"rid": ' . $r->getId() . '
}';
				}
			}
		}

		return '[' . implode( ',', $rid_json ) . ']';
	} elseif ( $method == "updateWidgetWiki" ) {
		$namespace = intval( array_shift( $params ) );
		$name = array_shift( $params );
		$text = array_shift( $params );
		if ( $namespace != NS_CATEGORY_WIDGET ) return wfMessage( 'spm_ajax_fail' )->text();

		// create template content
		$title = Title::newFromText( $name, NS_TEMPLATE );
		// decode error, just deal this in client js
//		$text = html_entity_decode( $text );
		$page = WikiPage::factory( $title );
		$ret = $page->doEditContent(
			ContentHandler::makeContent( $text, $title ),
			'Edit by Widget Designer'
		);
		if ( !$ret->isOK() ) {
			return $ret->getWikiText();
		}

		// new category widget page
		$template = new SMWWikiPageValue( '___wpw' );
		$template->setTitle( Title::newFromText( $name, NS_TEMPLATE ) );

		$title = Title::newFromText( $name, $namespace );

		// merge template properties, but for now, just ignore them
		$store = smwfGetStore();
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$template2 = $store->getPropertyValues( new SMWDIWikiPage( $title->getDBkey(), $title->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_ST' )->getDataItem() );
		} else {
			$template2 = $store->getPropertyValues( $title, SMWPropertyValue::makeProperty( '___SPM_WF_ST' ) );
		}
		if ( count( $template2 ) == 0 ) {
			$smwdatatype = SMWPropertyValue::makeProperty( '___SPM_WF_ST' );
			$text = "[[{$smwdatatype->getWikiValue()}::" . SPMWidgetUtils::getPrefixedText( $template ) . "| ]]\n";

			$revision = Revision::newFromTitle( $title );
			if ( $revision != null ) $text .= $revision->getText();

			$page = WikiPage::factory( $title );
			$ret = $page->doEditContent(
				ContentHandler::makeContent( $text, $title ),
				'Edit by Widget Designer'
			);
			if ( !$ret->isOK() ) {
				return $ret->getWikiText();
			}
		}

		return wfMessage( 'spm_ajax_success' )->text();
	} elseif ( $method == "createFormPage" ) {
		$widget_name = array_shift( $params );
		$ret = 0;
		$msg = '';
		if ( !defined( 'SF_VERSION' ) ) {
			$msg = 'SF not installed.\nPlease contact Wiki admin.';
		} else {
			SPMWidgetUtils::getSuperWidgetProperties( $widget_name, $widgets );
			$templates = array();

			$widgets = array_merge( $widgets, SPMWidgetUtils::getWidgetProperties( $widget_name ) );
			foreach ( $widgets as $w ) {
				foreach ( array( '___SPM_WF_ST', '___SPM_WF_MT' ) as $ptxt ) {
					$property = SMWPropertyValue::makeProperty( $ptxt );
					if ( $w['prop'] == $property->getWikiValue() ) {
						$templates[] = $w['value'];
					}
				}
			}

			$form_text = '';
			foreach ( $templates as $prefixed_tmpl_name ) {
				$tmpl_title = Title::newFromText( $prefixed_tmpl_name, NS_TEMPLATE );
				$fields = SPMWidgetUtils::getWidgetTemplateFieldViewSettings( $prefixed_tmpl_name );

//				$article = new Article( $tmpl_title );
//				if ( !$article->exists() ) continue;
//
//				$text = ContentHandler::getContentText( $article->getPage()->getContent() );
//				$len = strlen( $text );
//				$offset = 0;
//				$content2 = '';
//				$min = 0;
//				while ( $offset < $len ) {
//					$type = 0;
//					$idx_noinclude = stripos( $text, '<noinclude>', $offset );
//					if ( $idx_noinclude !== false ) {
//						$min = $idx_noinclude;
//						$type = 1;
//					}
//					$idx_nowiki = stripos( $text, '<nowiki>', $offset );
//					if ( $idx_nowiki !== false && $idx_nowiki < $min ) {
//						$min = $idx_nowiki;
//						$type = 2;
//					}
//					if ( $type == 0 ) {
//						$content2 .= substr( $text, $offset );
//						break;
//					} else {
//						$content2 .= substr( $text, $offset, $min - $offset );
//						if ( $type == 1 ) {
//							$offset = stripos( $text, '</noinclude>', $offset );
//							$offset += strlen( '</noinclude>' );
//						} else if ( $type == 2 ) {
//							$offset = stripos( $text, '</nowiki>', $offset );
//							$offset += strlen( '</nowiki>' );
//						}
//					}
//				}
//				$r = preg_match_all( '/\{\{\{\s*([^}|]+)\s*(?:\|[^}]*)?\}\}\}/', $content2, $m );
//				if ( !$r ) continue;
//
//				foreach($m[1] as $f) {
//					$fields[] = trim($f);
//				}
//				$fields = ( array_unique( $fields ) );

				$form_text .= '{{{for template|' . $tmpl_title->getText() . '}}}
{| class="formtable"';
				foreach ( $fields as $f ) {
					$form_text .= '
! ' . $f['label'] . ': || {{{field|' . $f['field'] . '|property=' . $f['property'] . '}}}
|-';
// ! ' . ucfirst( $f ) . ': || {{{field|' . $f . '|}}}
				}
				$form_text .= '
|}
{{{end template}}}';
			}

			if ( $form_text == '' ) {
				$msg = 'There is no field in current widget.\nPlease add some fields.';
			} else {
				$sc_mapview = defined( 'SMW_CONNECTOR_VERSION' ) ? ( '{{#mapview:}}' . "\n" ) : '';
				$form_text = "<noinclude>
This is the '{$widget_name}' form.
To add a page with this form, enter the page name below;
if a page with that name already exists, you will be sent to a form to edit that page.

{$sc_mapview}{{#forminput:{$widget_name}}}
</noinclude><includeonly>
{$form_text}
'''Free text:'''

{{{field|free text}}}

{{{standard input|summary}}}

{{{standard input|minor edit}}} {{{standard input|watch}}}

{{{standard input|save}}} {{{standard input|preview}}} {{{standard input|changes}}} {{{standard input|cancel}}}
</includeonly>";

				$title = Title::newFromText( $widget_name, SF_NS_FORM );

				$page = WikiPage::factory( $title );
				$ret = $page->doEditContent(
					ContentHandler::makeContent( $form_text, $title ),
					'Edit by Widget Designer'
				);
				if ( $ret->isOK() ) {
					$ret = 1;
					$msg = $title->getFullURL();
				} else {
					$msg = $ret->getWikiText();
				}
			}
		}
		return "{$ret}|{$msg}";
	}
	else {
		SPMWidgetExtraUtils::initialize();
		foreach ( SPMWidgetExtraUtils::$extras as $e ) {
			$ret = $e->onAjaxAccess( $method, $params );
			if ( $ret !== FALSE ) return $ret;
		}

		return wfMessage( 'spm_ajax_fail' )->text();
	}
}
