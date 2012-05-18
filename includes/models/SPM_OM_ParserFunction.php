<?php
/**
 * This model implements Parser Function models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectEditors
 *
 */

class SPMParserFunctionModel extends SPMObjectModelCollection {
	public function __construct() {
		parent::__construct( WOM_TYPE_PARSERFUNCTION );
	}

	public function getEditorHtml( WikiObjectModel $obj, $name_prefix = 'spm_obj', &$onSubmit = '' ) {
		if ( !( $obj instanceof WOMParserFunctionModel ) ) return '';

		$func_key = $obj->getFunctionKey();

		if ( strtolower( $func_key ) == 'ask' ) {
			if ( defined( 'SMW_HALO_VERSION' ) && version_compare( SMW_HALO_VERSION, '1.5', '>=' ) ) {
				global $wgOut, $wgTitle, $wgUseAjax, $wgStylePath, $smwgHaloScriptPath, $wgSPMScriptPath;
				$title = $wgTitle;
				$wgTitle = Title::newFromText( "Special:QueryInterface" );
				$jsm = SMWResourceManager::SINGLETON();
				if ( $wgUseAjax ) {
					$jsm->addScriptIf( "{$wgStylePath}/common/ajax.js" );
				}
				$jsm->addScriptIf( $smwgHaloScriptPath .  '/scripts/prototype.js' );
				smwfQIAddHTMLHeader( $wgOut );
				smwfHaloAddHTMLHeader( $wgOut );

				$wgTitle = $title;
				$qi = new SMWQueryInterface();
				if ( version_compare( SMW_HALO_VERSION, '1.5.6', '>=' ) )
					$qi->execute( null );
				else
					$qi->execute();

				$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/inline_editor/spm_ask_qi.js"></script>' );
				$onSubmit = 'save_spm_ask_qi()';

				$html = '
<div id="plainask" style="float: left;
    margin-top: 0px;
    position: relative;
    width: 100%;">
<span style="position: absolute; right: 133px;">
<input type="button" onclick="spm_ask_plain_edit()" value="Plain text edit"/>
</span>
</div>
<textarea style="display:none" name="' . $name_prefix . '[val]" rows="15" cols="70">' . htmlspecialchars( $obj->getWikiText() ) . '</textarea>
';
				return $html;

			}
		}

		$html = '
<textarea name="' . $name_prefix . '[val]" rows="25" cols="70">' . htmlspecialchars( $obj->getWikiText() ) . '</textarea>';
		return $html;
	}

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		if ( !( $obj instanceof WOMParserFunctionModel ) ) return '';

		$text = '';
		foreach ( $obj->getObjects() as $o ) {
			$text .= $o->getWikiText();
		}
		return "<div class='spm_inline_div' id='spm_inline_{$prefix}{$obj->getObjectID()}'>{{#{$obj->getFunctionKey()}:{$text}}}</div>";
	}
}
