<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 */

// reserved templates
SPMWidgetUtils::$widgetTemplates['Wom table begin'] = '';
SPMWidgetUtils::$widgetTemplates['Wom table end'] = '';
// meaningful templates
SPMWidgetUtils::$widgetTemplates['Wom table row'] = 'table row';

class SPMWidgetTableRowView extends SPMWidgetView {
	static function tableTemplates( $tmpl ) {
		switch( $tmpl ) {
			case 'Wom table begin':
				return 'begin';
			case 'Wom table end':
				return 'end';
			case 'Wom table row':
				return '';
		}
		return false;
	}

	public function __construct() {
		parent::__construct( SPM_WV_TABLEROW );
	}

	public function registerResourceModules() {
		global $wgResourceModules, $wgSPMIP, $wgSPMScriptPath;

		$moduleTemplate = array(
			'localBasePath' => $wgSPMIP,
			'remoteBasePath' => $wgSPMScriptPath,
			'group' => 'ext.wes.spm_view'
		);

		$wgResourceModules['ext.wes.spm_view.tablerow'] = $moduleTemplate + array(
			'scripts' => array( 'scripts/wf_designer/widgets/view/tablerow.js' ),
		);
	}

	public function addHTMLHeader() {
		global $wgOut, $wgSPMScriptPath;

		// FIXME: MW 1.17 resource loader cannot handle dynamic script inside lazy load scripts

//		// MediaWiki 1.17 introduces the Resource Loader.
//		$realFunction = array( 'SMWOutputs', 'requireResource' );
//		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
//			$wgOut->addModules('ext.wes.spm_view.tablerow');
//		} else {
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/widgets/view/tablerow.js"></script>' );
//		}
	}

	public function getDesignerHtml( $title_name ) {
		$html = parent::getDesignerHtml( $title_name );
		$html .= '
          <tr>
            <td>
              <label>New table
                <span class="small">Show in a new table?</span>
              </label>
              <input id="spm_table_new" type="checkbox" style="width:auto;">
              <div style="clear:both;"></div>
              <div id="spm_table_new_div" style="display:none;">
              <label style="width:120px;">Type
                <span class="small" style="width:120px;">Built-in table types.</span>
              </label>
              <select id="spm_table_type" style="margin: 2px 0px 0px 10px;width:120px;">
                <!--<option value="infobox">right infobox</option>-->
                <option value="l_infobox">infobox</option>
                <option value="common">regular</option>
                <option value="hidden">hidden</option>
              </select>
              <div style="clear:both;"></div>
              <!--<span class="small" style="text-align:left;margin-left:10px;width:240px;">!!! Cannot be changed.</span>-->
              <label style="text-align:left;">
                <span style="margin-left:10px;">Table Header</span>
                <span class="small" style="width:240px;">Optional. Table header in Wiki text.</span>
              </label>
              <input type="text" value="" id="spm_table_header" style="margin: 2px 0px 0px 10px;width:240px;">
              <div style="clear:both;"></div>
              <label style="text-align:left;">
                <span style="margin-left:10px;">Table Class</span>
                <span class="small" style="width:240px;">Optional. Specify HTML class, e.g., spm_infobox.</span>
              </label>
              <input type="text" value="spm_infobox" readonly="readonly" id="spm_table_class" style="margin: 2px 0px 0px 10px;width:240px;">
              <div style="clear:both;"></div>
              <label style="text-align:left;">
                <span style="margin-left:10px;">Table Header Class</span>
                <span class="small" style="width:240px;">Optional. Specify HTML class, e.g., spm_infobox_header.</span>
              </label>
              <input type="text" value="spm_infobox_header" readonly="readonly" id="spm_table_header_class" style="margin: 2px 0px 0px 10px;width:240px;">
              <div style="clear:both;"></div>
              </div>
            </td>
          </tr> ';

		return $html;
	}

	public function getFieldHtml( $label, $title, $field, &$params, $extra_params, $freetext ) {
		$optional = ( array_shift( $params ) == 'true' );
		$multiple = ( array_shift( $params ) == 'true' );
		$editidx = intval( trim( array_shift( $params ) ) );

		$new_box = ( array_shift( $params ) == 'true' );
		$type = array_shift( $params );
		$table_class = array_shift( $params );
		$header = array_shift( $params );
		$header_class = array_shift( $params );
		$settings = ( $optional ? '|optional=true' : '' ) . ( $multiple ? '|multiple=true' : '' ) . ( $editidx > 0 ? ( '|editidx=' . $editidx ) : '' );

		$default = '';
		foreach ( $extra_params as $k => $p ) {
			if ( $k == '___default' ) {
				$default = $p;
			} else {
				$settings .= "|{$k}={$p}";
			}
		}

		if ( $table_class == '' && $type == 'infobox' ) $table_class = 'spm_infobox';
		if ( $header_class == '' && $type == 'infobox' ) $header_class = 'spm_infobox_header';

		$t = Title::newFromText( $title );
		$viewer = new CategoryWidgetDesignViewer( $t );
		$row_wiki = $field ?
			( '{{Wom table row|' . $label . '|' . $title . '/' . $field . '|{{{' . $field . '|' . $default . '}}}' . $settings . '}}' . $freetext ) :
			'';
		$text = '';
		if ( $new_box == 'true' ) {
			$text = '
{{Wom table begin|class=' . $table_class . '|header class=' . $header_class . '|header=' . $header . '}}
' . $row_wiki . '
{{Wom table end}}
';
		} else {
			$text = '
{|
' . $row_wiki . '
{{Wom table end}}';
		}
		$html = $viewer->getWidgetHtml2( $text, $t );

		// FIXME: hard code here
		if ( $new_box == 'true' ) {
			$html = substr( $html, strpos( $html, '</div>' ) + 6 );
		} else {
			$html = substr( $html, strpos( $html, '<tr' ) );
			$html = substr( $html, 0, strrpos( $html, '</tr' ) );
			$html .= '</tr>';
		}

		return $html;
	}
}
