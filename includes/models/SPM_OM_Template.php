<?php
/**
 * This model implements Template models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMTemplateModel extends SPMObjectModelCollection {
	public function __construct() {
		parent::__construct( WOM_TYPE_TEMPLATE );
	}

	private function formInitialize( WOMTemplateModel $obj, &$tmpl_name, &$title, &$tempalte_form, &$is_template ) {
		$is_template = false;
		$tempalte_form = null;
		$tmpl_name = $obj->getName();

		$title = Title::newFromText( $tmpl_name );

		if ( $title->getNamespace() == NS_MAIN ) {
			// http://www.mediawiki.org/wiki/Help:Transclusion
			// If the source is in the Main article namespace (e.g., "Cat"),
			// you must put a colon (:) in front of the name, thus: {{:Cat}}

			// If the source is in the Template namespace (e.g., "Template:Villagepumppages"),
			// just use the name itself, alone, thus: {{Villagepumppages}}
			if ( $tmpl_name { 0 } != ':' ) {
				$title = Title::makeTitleSafe( NS_TEMPLATE, $tmpl_name );
				$tempalte_form = $this->getTemplateForm( $tmpl_name, $title );
				$is_template = true;
			}
		}
	}

	private function getTemplateForm( $tmpl_name, $title ) {
		if ( $title === null ) {
			// try parser function here, will be discarded by WOM processor
			// FIXME: or we can just throw this exception, to declare an invalid object
			return null;
		}

		$article = new Article( $title );
		if ( !$article->exists() ) {
			return null;
		}

		$text = ContentHandler::getContentText( $article->getPage()->getContent() );
		$len = strlen( $text );
		$offset = 0;
		$content2 = '';
		$min = 0;
		while ( $offset < $len ) {
			$type = 0;
			$idx_noinclude = stripos( $text, '<noinclude>', $offset );
			if ( $idx_noinclude !== false ) {
				$min = $idx_noinclude;
				$type = 1;
			}
			$idx_nowiki = stripos( $text, '<nowiki>', $offset );
			if ( $idx_nowiki !== false && $idx_nowiki < $min ) {
				$min = $idx_nowiki;
				$type = 2;
			}
			if ( $type == 0 ) {
				$content2 .= substr( $text, $offset );
				break;
			} else {
				$content2 .= substr( $text, $offset, $min - $offset );
				if ( $type == 1 ) {
					$offset = stripos( $text, '</noinclude>', $offset );
					$offset += strlen( '</noinclude>' );
				} else if ( $type == 2 ) {
					$offset = stripos( $text, '</nowiki>', $offset );
					$offset += strlen( '</nowiki>' );
				}
			}
		}
		$r = preg_match_all( '/\{\{\{\s*([^}|]+)\s*(?:\|[^}]*)?\}\}\}/', $content2, $m );
		if ( !$r ) {
			return '';
		}

		$fields = ( array_unique( $m[1] ) );
		$form_definition = '{{{for template|' . $tmpl_name . '}}}
{| class="formtable"';
		foreach ( $fields as $f ) {
			$form_definition .= '
! ' . ucfirst( $f ) . ':
| {{{field|' . $f . '}}}
|-';
		}
		$form_definition .= '
|}
{{{end template}}}';

		return $form_definition;
	}

	function getWidgetSettings( WOMPageModel $page_obj, $name ) {
		global $wgSPMContLang;
		$wf_props = $wgSPMContLang->getPropertyLabels();

		foreach ( $page_obj->getTitle()->getParentCategories() as $cate => $v ) {
			// get category widget templates
			$cate = Title::newFromText( $cate, NS_CATEGORY )->getText();
			SPMWidgetUtils::getSuperWidgetProperties( $cate, $widgets );
			foreach ( SPMWidgetUtils::getWidgetProperties( $cate ) as $w ) {
				$w['category'] = $cate;
				$widgets[] = $w;
			}

			$matched = false;
			foreach ( $widgets as $w ) {
				if ( ( $w['prop'] == $wf_props[SPM_WF_SP_HAS_TEMPLATE] ||
					$w['prop'] == $wf_props[SPM_WF_SP_HAS_MULTIPLE_TEMPLATE] ) &&
					Title::newFromText( $w['value'], NS_TEMPLATE )->getText() == $name
					) {
						return Title::newFromText( $cate, NS_CATEGORY_WIDGET );
					}
			}
		}

		return null;
	}

	public function getEditorHtml( WikiObjectModel $obj, $name_prefix = 'spm_obj', &$onSubmit = '' ) {
		if ( !( $obj instanceof WOMTemplateModel ) ) return '';

		$name = $obj->getName();
		if ( $name { 0 } == ':' ) return '';

		$name = Title::newFromText( $name, NS_TEMPLATE )->getText();

		$p = $obj;
		while ( ( $p = $p->getParent() ) != null ) {
			$page_obj = $p;
		}

		$widget = $this->getWidgetSettings( $page_obj, $name );
		if ( $widget != null ) {
			return '
<h2>' . wfMessage( 'wf_title', $widget->getText() )->escaped() . '</h2>
' . SPMWidgetUtils::getWidgetAssemblerHtml( $widget->getText(), $page_obj );
		}

		// not matched, SF
		if ( !defined( 'SF_VERSION' ) ) {
			return parent::getEditorHtml( $obj, $name_prefix, $onSubmit ) . '
			<p>Or you can install SemanticForms extension for better user experience.</p>';
		}
		$this->formInitialize( $obj, $tmpl_name, $title, $tempalte_form, $is_template );

		if ( $title == null || !$is_template ) {
			$html = '
<h2>Transclusion ' . $tmpl_name . '</h2>
' . parent::getEditorHtml( $obj, $name_prefix, $onSubmit );
			return $html;
		}

		$html = '
<h2> ' . $title->getFullText() . '</h2>
';
		$form_definition = $tempalte_form;
		if ( $form_definition === null ) {
			$html .= '<p>Template is not defined!</p>
' . parent::getEditorHtml( $obj, $name_prefix, $onSubmit );

			return $html;
		} else if ( $form_definition === '' ) {
			$html .= '<p>No field defined in this template!</p>
' . parent::getEditorHtml( $obj, $name_prefix, $onSubmit );

			return $html;
		}

		$page_contents = $obj->getWikiText();

		global $sfgFormPrinter;

		// $sfgFormPrinter is a StubObject
		if ( $sfgFormPrinter instanceof StubObject ) $sfgFormPrinter->_unstub();
		$sfgFormPrinter->standardInputsIncluded = true;

		if ( strpos( SF_VERSION, '2.' ) === 0 ) {
			list ( $form_text, $javascript_text, $data_text ) =
				$sfgFormPrinter->formHTML( $form_definition, false, true, null, $page_contents );
		} else {
			list ( $form_text, $javascript_text, $data_text ) =
				$sfgFormPrinter->formHTML( $form_definition, false, true, $page_contents );
		}

		// FIXME: hard code here, SF printer add a single form close tag in the return string
		$form_text = str_replace( '</form>', '', $form_text );

		return $html . $form_text;
	}

	public function updateValues( WikiObjectModel $obj, $values ) {
		if ( !( $obj instanceof WOMTemplateModel ) ) return;

		$name = $obj->getName();
		if ( $name { 0 } == ':' ) return;

		$name = Title::newFromText( $name, NS_TEMPLATE )->getText();

		$p = $obj;
		while ( ( $p = $p->getParent() ) != null ) {
			$page_obj = $p;
		}

		$widget = $this->getWidgetSettings( $page_obj, $name );
		if ( $widget != null ) {
			SPMWidgetUtils::updateWidgetValues( $widget->getText(), $page_obj );
			return;
		}

		if ( !defined( 'SF_VERSION' ) ) {
			parent::updateValues( $obj, $values );
		}

		$this->formInitialize( $obj, $tmpl_name, $title, $tempalte_form, $is_template );

		if ( !$tempalte_form ) {
			parent::updateValues( $obj, $values );
			return;
		}

		global $sfgFormPrinter;

		list ( $form_text, $javascript_text, $data_text ) =
			$sfgFormPrinter->formHTML( $tempalte_form, true, false );

		parent::updateValues( $obj, array( 'val' => $data_text ) );
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		if ( !( $obj instanceof WOMTemplateModel ) ) return '';

		return "<div class='spm_inline_div' id='spm_inline_{$prefix}{$obj->getObjectID()}'>{{{$obj->getName()}|{$this->getSubInlineEditText($obj, $prefix)}}}</div>";
	}
}
