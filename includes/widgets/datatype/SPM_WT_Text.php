<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 *
 */

class SPMWidgetTextType extends SPMWidgetDataType {
	public function __construct() {
		parent::__construct( SPM_WT_TYPE_TEXT );
	}

	public function getSMWTypeID() {
		return '_txt';
	}

	public function getDesignerHtml( $title_name ) {
		return '
          <tr>
            <td>
              <label style="width:200px;text-align: left; margin-left: 10px;">Default value
              </label>
              <textarea id="spm_wf_field_default" style="margin: 2px 0px 0px 10px;"></textarea>
              <input type="hidden" id="spm_wf_prop_allows"/>
              <div style="clear:both;"></div>
            </td>
          </tr> ';
	}

	public function getSampleWiki( $proptitle = null, $default_val = '' ) {
		$wiki = parent::getSampleWiki( $proptitle, $default_val );

		return ( trim( $wiki ) == '__ALWAYS_NEW__' ) ? $this->getSampleWikiOnEmpty() : $wiki;
	}

	public function getDefaultValue( Title $proptitle, $extra_semdata = null ) {
		$default = parent::getDefaultValue( $proptitle, $extra_semdata );

		return ( trim( $default ) == '__ALWAYS_NEW__' ) ? '' : $default;
	}

	public function getFieldParameters( &$params ) {
		$default = array_shift( $params );
		return array( '___default' => ( trim( $default ) == '__ALWAYS_NEW__' ) ? '' : $default );
	}

	public function getEditorUI( $title,
			$id, $label,
			$tmpl_name, $field_name, $current_value,
			Title $proptitle,
			$extra_semdata = null,
			$params = array() ) {

		if ( parent::getDefaultValue( $proptitle, $extra_semdata ) == '__ALWAYS_NEW__' ) $current_value = '';

		if ( !$this->isEditable( $proptitle ) ) {
			$name = str_replace( '"', '\"', "{$tmpl_name}[0][{$field_name}]" );
			return '<textarea style="display:none;" class="spm_wf_val" name="' . $name . '">
' . htmlspecialchars( $current_value ) . '
</textarea>';
		}

		$optional = $params['optional'];

		$store = smwfGetStore();
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$descriptions = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_HD' )->getDataItem() );
		} else {
			$descriptions = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '___SPM_WF_HD' ) );
		}
		if ( count( $descriptions ) > 0 ) {
			// always use the first description value
			$description = SPMUtils::getWikiValue( $descriptions[0] );
		}
		$hint1 = wfMessage( 'wf_spm_field_description', $description )->parse();
		$hint2 = ( $multiple ? wfMessage( 'wf_spm_hint_field_multiple' )->text() : '' ) .
				wfMessage( 'wf_spm_hint_field_description',
					SMWDataValueFactory::findTypeLabel( $this->getSMWTypeID() ),
					$this->getSampleWikiOnEmpty() )->parse();

		$sample = '';
		foreach ( SPMArticleUtils::parseTemplatePage( $params['raw'] ) as $tf ) {
			if ( !is_array( $tf ) ) {
				$sample .= $tf;
				continue;
			}
			$sample .= '{{{' . $tf['field'] . '|' . $this->getSampleWiki( $proptitle, $tf['default'] ) . '}}}';
		}
		// FIXME: hard code here
		if ( strpos( $params['tmpl_name'], 'table row' ) !== false ) {
			$sample = "{|class='spm_infobox'\n{$sample}\n|}";
		}

		global $wgParser, $wgUser;
		$options = ParserOptions::newFromUser( $wgUser );
		$html = $wgParser->parse( $sample, $title, $options );
		$sample = $html->getText();

		global $wgSPMScriptPath, $wgOut;
		return '
<tr><td>' . $wgOut->parse( "[[{$proptitle}|{$label}]]" . ( $optional ? '' : '*' ), true ) . '
' . ( $hint1 ? "<p>{$hint1}</p>":'' ) . '
</td><td>
<div id="' . $id . '" class="spm_wf_fld">
' . $this->getEditorHtml( $title, $tmpl_name, $field_name, $current_value, $proptitle, $extra_semdata, $params ) . '
</div>
</td></tr>';
//		return '
// <tr><td>
// <label>' . $wgOut->parse("[[{$proptitle}|{$label}]]" . ($optional ? '' : '*'), true) . '</label>
// <img class="popupIcon" src="' . $wgSPMScriptPath . '/skins/wf_editor/hint.png"/>
// <div class="tooltip">
//	<div class="label" style="width:310px;">
//		' . $hint1 . '
//		<b>settings</b>' . $hint2 . '
//		<b>Output sample</b>
//		<div class="spm_wf_formfldsmpl">
//			' . $sample . '
//		</div>
//	</div>
// </div>
// </td></tr>
// <tr><td>
// <div id="' . $id . '" class="spm_wf_fld">
// ' . $this->getEditorHtml($title, $tmpl_name, $field_name, $current_value, $proptitle, $extra_semdata, $params) . '
// </div>
// </td></tr>';
	}

	protected function renderNonPossibleValuesField(
		&$js,
		$name, $current_value, $possible_values,
		Title $proptitle,
		$extra_semdata = null, $params = array() ) {

		return '<textarea class="spm_wf_val' . ( $params['optional'] ? ' spm_wf_optional_val' : '' ) . '" name="' . $name . '">
' . htmlspecialchars( $current_value ) . '
</textarea>';
	}

	protected function getSampleWikiOnEmpty() {
		return '<span style="color:#555555">Text content</span>';
	}
}
