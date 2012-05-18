<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 */

SPMWidgetUtils::$boundTemplates[] = 'Wom section';
// meaningful templates
SPMWidgetUtils::$widgetTemplates['Wom section'] = 'section';

class SPMWidgetSectionView extends SPMWidgetView {
	public function __construct() {
		parent::__construct( SPM_WV_SECTION );
	}

	public function registerResourceModules() {
		global $wgResourceModules, $wgSPMIP, $wgSPMScriptPath;

		$moduleTemplate = array(
			'localBasePath' => $wgSPMIP,
			'remoteBasePath' => $wgSPMScriptPath,
			'group' => 'ext.wes.spm_view'
		);

		$wgResourceModules['ext.wes.spm_view.section'] = $moduleTemplate + array(
			'scripts' => array( 'scripts/wf_designer/widgets/view/section.js' ),
		);
	}

	public function addHTMLHeader() {
		global $wgOut, $wgSPMScriptPath;

		// FIXME: MW 1.17 resource loader cannot handle dynamic script inside lazy load scripts

//		// MediaWiki 1.17 introduces the Resource Loader.
//		$realFunction = array( 'SMWOutputs', 'requireResource' );
//		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
//			$wgOut->addModules('ext.wes.spm_view.section');
//		} else {
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/widgets/view/section.js"></script>' );
//		}
	}

	public function getDesignerHtml( $title_name ) {
		$html = parent::getDesignerHtml( $title_name );
		$html .= '
          <tr>
            <td>
              <label style="margin-left:10px;">
                Section Level
              </label>
              <input type="text" value="2" id="spm_section_level" style="margin: 2px 0px 0px 10px;width:20px;">
              <div style="clear:both;"></div>
              <span class="small" style="text-align:left;margin-left:10px;width:180px;">
              Section header level, from 1 to 6.</span>
            </td>
          </tr> ';

		return $html;
	}

	public function getFieldSettings( $params ) {
		$settings = parent::getFieldSettings( $params ) . "\n";
		$settings .= $params['level'];

		return $settings;
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
{{Wom section|' . $label . '|' . $title . '/' . $field . '|{{{' . $field . '|' . $default . '}}}' . $settings . '|level=' . $level . '}}
' . $freetext;

		$html = $viewer->getWidgetHtml2( $text, $t );

		// FIXME: hard code here
		$html = substr( $html, strpos( $html, '</div>' ) + 6 );

		return $html;
	}
}
