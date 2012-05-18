<?php
/*
 * Author: ning
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	exit( 1 );
}

global $wgAjaxExportList;
global $wgSPMIP;

$wgAjaxExportList[] = 'spm_om_ObjectModelAccess';


function spm_om_ObjectModelAccess( $method, $params ) {
	$p_array = explode( ",", $params );

	if ( $method == "editObject" ) {
		return wfMsg( 'spm_ajax_success' );
	}
	else {
		return wfMsg( 'spm_ajax_fail' );
	}
}
