<?php
/**
 * @ingroup SMWDataValues
 */

class SMSPMxtendedDatatypeWikiPageValue extends SMWWikiPageValue {
	public function __construct( $typeid ) {
		parent::__construct( $typeid );
		switch ( $typeid ) {
			case '___med':
				$this->m_fixNamespace = NS_MEDIA;
				break;
			case '___img':
				$this->m_fixNamespace = NS_IMAGE; // NS_FILE
				break;
			case '___wdg':
				$this->m_fixNamespace = NS_CATEGORY_WIDGET;
				break;
		}
	}
}

