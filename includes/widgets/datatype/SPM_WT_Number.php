<?php
/**
 * @author Ning
 * @file
 * @ingroup SemanticPageMaker
 *
 */

class SPMWidgetNumberType extends SPMWidgetDataType {
	public function __construct() {
		parent::__construct( SPM_WT_TYPE_NUMBER );
	}

	public function getSMWTypeID() {
		return '_num';
	}

	public function initProperties() {
		global $wgSPMContLang;
		$wf_props = $wgSPMContLang->getPropertyLabels();

		if ( array_key_exists( SPM_WF_SP_HAS_NUM_RANGE, $wf_props ) )
			SMWPropertyValue::registerProperty( '___SPM_WF_NR', '_str', $wf_props[SPM_WF_SP_HAS_NUM_RANGE], true );

		// also initialize hardcoded English values, if it's a non-English-language wiki
		SMWPropertyValue::registerProperty( '___SPM_WF_NR_BACKUP', '_str', 'SPM has number range', true );
	}

	public function registerResourceModules() {
		global $wgResourceModules, $wgSPMIP, $wgSPMScriptPath;

		$moduleTemplate = array(
			'localBasePath' => $wgSPMIP,
			'remoteBasePath' => $wgSPMScriptPath,
			'group' => 'ext.wes.spm_dt'
		);

		$wgResourceModules['ext.wes.spm_dt.number'] = $moduleTemplate + array(
			'scripts' => array( 'scripts/wf_designer/widgets/datatype/number.js' ),
		);
	}

	public function addHTMLHeader() {
		global $wgOut, $wgSPMScriptPath;

		// FIXME: MW 1.17 resource loader cannot handle dynamic script inside lazy load scripts

//		// MediaWiki 1.17 introduces the Resource Loader.
//		$realFunction = array( 'SMWOutputs', 'requireResource' );
//		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
//			$wgOut->addModules('ext.wes.spm_dt.number');
//		} else {
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_designer/widgets/datatype/number.js"></script>' );
//		}
	}

	public function getDesignerHtml( $title_name ) {
		$html = parent::getDesignerHtml( $title_name );
		$html .= '
          <tr>
            <td>
              <label style="text-align:left;margin-left:10px;">
                Number range
                <span class="small">E.g., ">10, <=100", ">200, !=222"</span>
              </label>
              <textarea id="spm_wf_prop_range" style="margin: 2px 0px 0px 10px;"></textarea>
              <div style="clear:both;"></div>
              <span class="small" style="text-align:left;margin-left:10px;width:260px;">Line break stands for "OR".</span>
            </td>
          </tr> ';

		return $html;
	}

	public function getFieldSettings( $proptitle, $params ) {
		$settings = parent::getFieldSettings( $proptitle, $params );

		$ranges = '';
		$store = smwfGetStore();
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$num_ranges = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '___SPM_WF_NR' )->getDataItem() );
		} else {
			$num_ranges = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '___SPM_WF_NR' ) );
		}
		foreach ( $num_ranges as $r ) {
			$ranges .= SPMUtils::getWikiValue( $r ) . '\n';
		}
		$settings .= $ranges . "\n";

		return $settings;
	}

	public function getPropertyWiki( &$params ) {
		$wiki = parent::getPropertyWiki( $params );

		$prop_range = SMWPropertyValue::makeProperty( '___SPM_WF_NR' )->getWikiValue();
		$ranges = explode( "\n", array_shift( $params ) );
		foreach ( $ranges as $r ) {
			$r = trim( $r );
			if ( $r == '' ) continue;
			$wiki .= "
* Has range: [[{$prop_range}::{$r}]]";
		}

		return $wiki;
	}

	protected function getSampleWikiOnEmpty() {
		return '100';
	}

	protected function getRanges( $semdata ) {
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$valid_range = $semdata->getPropertyValues( SMWPropertyValue::makeProperty( '___SPM_WF_NR' )->getDataItem() );
		} else {
			$valid_range = $semdata->getPropertyValues( SMWPropertyValue::makeProperty( '___SPM_WF_NR' ) );
		}
		$ranges = array();
		foreach ( $valid_range as $r ) {
			$val = SPMUtils::getWikiValue( $r );
			$ranges[] = html_entity_decode( $val );
		}

		return count( $ranges ) > 0 ? $ranges : false;
	}

	/**
	 * special property
	 * Each ¡°Allowed values¡± indicate a condition
	 * within each condition, it is an ¡°AND¡± relationship.
	 * For multiple ¡°Allowed values¡±, it is an ¡°OR¡± relationship.
	 *
	 * E.g.,
	 * [[SPM has number range:: >=1, <7]]
	 * [[SPM has number range:: >101, !=121]]
	 * above means:
	 * [1,7) (101, infinity) except 121.
	 */
	private function getNumberRangeJs( $ranges ) {
		if ( $ranges === false || count( $ranges ) == 0 ) return '';
		$js = '';
		foreach ( $ranges as $range ) {
			$js2 = '';
			foreach ( explode( ',', preg_replace( '/\s+/', ' ', $range ) ) as $r )
				$js2 .= ' && (val' . $r . ')';
			if ( $js2 != '' ) $js .= ' || ( true ' . $js2 . ' )';
		}
		return $js == '' ? '' : ' ( false ' . $js . ' ) ';
	}

	protected function getFieldValidationJs( $name, Title $proptitle, $extra_semdata = null, $params = array() ) {
		$multiple = $params['multiple'];

		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$semdata = smwfGetStore()->getSemanticData( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ) );
		} else {
			$semdata = smwfGetStore()->getSemanticData( $proptitle );
		}
		$ranges = $this->getRanges( $semdata );
		$js = $hint = '';
		if ( $ranges !== false ) {
			$js .= $this->getNumberRangeJs( $ranges );
			$hint .= implode( '<br/>&nbsp;&nbsp;&nbsp; OR ', $ranges );
		}

		if ( $extra_semdata != null ) {
			$ranges = $this->getRanges( $extra_semdata );
			if ( $ranges !== false ) {
				if ( $js != '' ) {
					$js .= ' && ';
					$hint .= '<br/><br/>AND ';
				}
				$js .= $this->getNumberRangeJs( $ranges );
				$hint .= implode( '<br/>&nbsp;&nbsp;&nbsp; OR ', $ranges );
			}
		}

		if ( $js == '' ) return '';

		return '
spm_wf_field.data.push( {
	name : "' . $name . '",
	type : "validate",
	params : [ function(val){' .
( $multiple ? ( '
		var vs = val.split(",");
		for(var i=0;i<vs.length;++i){
			var val=parseInt(vs[i].trim());
			if(!(' . $js . ')) return false;
		}
		return true;' ) :
	( '
		return ' . $js . ';' )
) . '
}, "Value not in range:<br/>' . $hint . '" ]
} );';
	}
}
