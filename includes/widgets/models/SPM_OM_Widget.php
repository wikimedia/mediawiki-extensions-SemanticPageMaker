<?php
/**
 * This model implements Widget models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectModels
 *
 */

class SPMWidgetModel extends WikiObjectModelCollection {
	protected $m_name;

	public function __construct( $name ) {
		parent::__construct( 'widget' );

		$this->m_name = $name;
	}

	public function getName() {
		return $this->m_name;
	}

	public function getWikiText() {
		return '';
	}

	protected function getXMLAttributes() {
		return "name=\"{$this->m_name}\"";
	}
}
