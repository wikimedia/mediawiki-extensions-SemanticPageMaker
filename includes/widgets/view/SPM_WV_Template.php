<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 */

class SPMWidgetTemplateView extends SPMWidgetView {
	public function __construct( $id ) {
		parent::__construct( $id );
	}

	public function getListString() {
		return "Template view(s)|{$this->getTypeID()}";
	}

	public function getFieldHtml( $label, $title, $field, &$params, $extra_params, $freetext ) {
		$optional = ( array_shift( $params ) == 'true' );
		$multiple = ( array_shift( $params ) == 'true' );
		$editidx = intval( trim( array_shift( $params ) ) );

		$level = array_shift( $params );

		$settings = ( $optional ? '|optional=true' : '' ) . ( $multiple ? '|multiple=true' : '' ) . ( $editidx > 0 ? ( '|editidx=' . $editidx ) : '' );

		$default = '';
		foreach ( $extra_params as $k => $p ) {
			if ( $k == '___default' ) {
				$default = $p;
			} else {
				$settings .= "|{$k}={$p}";
			}
		}

		$t = Title::newFromText( $title );
		$viewer = new CategoryWidgetDesignViewer( $t );
		$text = '
{{' . $this->getName() . '|' . $label . '|' . $title . '/' . $field . '|{{{' . $field . '|' . $default . '}}}' . $settings . '}}
' . $freetext;

		$html = $viewer->getWidgetHtml2( $text, $t );

		// FIXME: hard code here
		$html = substr( $html, strpos( $html, '</div>' ) + 6 );

		return $html;
	}
}
