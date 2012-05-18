<?php
/**
 * This file contains a static class for accessing functions for
 * widget utilities.
 *
 * @author dch
 */

class SPMUtils {
	static function getWikiValue( $data ) {
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$value = SMWCompatibilityHelpers::getDBkeysFromDataItem( $data );
			if ( is_array( $value ) ) $value = $value[0];
		} else {
			$value = $data->getWikiValue();
		}
		return $value;
	}

	static function showCleanWikiOutput() {
		global $wgOut, $wgUser;

		$wgOut->disable();

		$sk = $wgUser->getSkin();
		$sk->initPage( $wgOut ); // need to call this to set skin name correctly

//		global $wgJsMimeType, $wgStylePath, $wgStyleVersion,
//			$wgXhtmlDefaultNamespace, $wgXhtmlNamespaces, $wgLanguageCode, $wgContLang;
//
//		if ( class_exists( 'HTMLTextField' ) ) { // added in MW 1.16
//			$skin_user_js = $sk->generateUserJs();
//			$user_js = <<<END
// <script type="{$wgJsMimeType}">
// $skin_user_js;
// </script>
//
// END;
//		} else {
//			global $wgServer, $wgScript;
//
//			// call to get user JS was changed in MW 1.14
//			if ( method_exists( $sk, 'generateUserJs' ) ) {
//				$skin_user_js = $sk->generateUserJs();
//			} else {
//				$skin_user_js = $sk->getUserJs();
//			}
//			$user_js = <<<END
// <script type="{$wgJsMimeType}">
// $skin_user_js;
// wgServer="{$wgServer}";
// wgScript="{$wgScript}"
// </script>
//
// END;
//		}
//
//		$vars_js = Skin::makeGlobalVariablesScript( array( 'skinname' => $sk->getSkinName() ) );
//		$wikibits_include = "<script type=\"{$wgJsMimeType}\" src=\"{$wgStylePath}/common/wikibits.js?$wgStyleVersion\"></script>";
//		$ajax_include = "<script type=\"{$wgJsMimeType}\" src=\"{$wgStylePath}/common/ajax.js?$wgStyleVersion\"></script>";
//		$ajaxwatch_include = "<script type=\"{$wgJsMimeType}\" src=\"{$wgStylePath}/common/ajaxwatch.js?$wgStyleVersion\"></script>";
//
//		$html = <<<END
// <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
// <html xmlns="{$wgXhtmlDefaultNamespace}"
// END;
//		foreach ( $wgXhtmlNamespaces as $tag => $ns ) {
//			$html .= "xmlns:{$tag}=\"{$ns}\" ";
//		}
//		$dir = $wgContLang->isRTL() ? "rtl" : "ltr";
//		$html .= "xml:lang=\"{$wgLanguageCode}\" lang=\"{$wgLanguageCode}\" dir=\"{$dir}\">";
//
//		$html .= <<<END
//
// <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
// <head>
// {$wgOut->getHeadLinks()}
// $vars_js
// $wikibits_include
// $user_js
// $ajax_include
// $ajaxwatch_include
// {$wgOut->getScript()}
// </head>
// <body>
// {$wgOut->getHTML()}
// </body>
// </html>
//
//
// END;

		global $wgVersion;
		if ( version_compare( $wgVersion, '1.17', '>=' ) ) {
			// code piece from includes/Skin.php
			$wgOut->out( $wgOut->headElement( $sk ) );

			global $wgDebugComments;
			if ( $wgDebugComments ) {
				$wgOut->out( "<!-- Wiki debugging output:\n" .
				  $wgOut->mDebugtext . "-->\n" );
			}
			$wgOut->out( $wgOut->mBodytext . "\n" );
			$wgOut->out( $sk->bottomScripts( $wgOut ) );
			$wgOut->out( wfReportTime() );
			$wgOut->out( "\n</body></html>" );
		} else {
			$header = $wgOut->headElement( $sk );
			$html .= <<<END

{$header}
{$wgOut->getHTML()}
</body>
</html>


END;

		print $html;
		}
	}
}
