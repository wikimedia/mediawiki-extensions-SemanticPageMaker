<?php
/**
 * @ingroup SMWDataValues
 */

class SMSPMxtendedWikiPageValue extends SMWWikiPageValue {
	public function __construct( $typeid ) {
		parent::__construct( $typeid );
		switch ( $typeid ) {
			case '___wpw':
				$this->m_fixNamespace = NS_TEMPLATE;
				break;
		}
	}
}
