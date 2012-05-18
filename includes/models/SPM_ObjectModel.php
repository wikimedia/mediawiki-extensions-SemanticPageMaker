<?php
/**
 * File holding abstract class SPMObjectModel, the base for all object model in SPM.
 *
 * @author Ning
 *
 * @file
 * @ingroup WikiObjectEditors
 */

abstract class SPMObjectModel {
	protected $m_typeid;

	/**
	 * Constructor.
	 *
	 * @param string $typeid
	 */
	public function __construct( $typeid ) {
		$this->m_typeid = $typeid;
	}

// /// Get methods /////
	public function getTypeID() {
		return $this->m_typeid;
	}

	public function getEditorHtml( WikiObjectModel $obj, $name_prefix = 'spm_obj', &$onSubmit = '' ) {
		$html = '
<textarea name="' . $name_prefix . '[val]" rows="25" cols="70">' . htmlspecialchars( $obj->getWikiText() ) . '</textarea>';
		return $html;
	}

	public abstract function updateValues( WikiObjectModel $obj, $values );

	public function getInlineEditText( WikiObjectModel $obj, $prefix = '' ) {
		return "<div class='spm_inline_div' id='spm_inline_{$prefix}{$obj->getObjectID()}'>" . htmlspecialchars( $obj->getWikiText() ) . "</div>";
	}
}
