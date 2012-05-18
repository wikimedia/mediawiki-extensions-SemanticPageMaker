<?php
/**
 * @author ning
 */

/**
 * Base class for all language classes.
 */
abstract class SPMLanguage {

	// the message arrays ...
	protected $wContentMessages;
	protected $wUserMessages;

	protected $m_SpecialProperties;
	protected $m_Namespaces;
	// / By default, every language has English-language aliases for
	// / special properties and namespaces
	protected $m_SpecialPropertyAliases = array();
	protected $m_NamespaceAliases = array();
	protected $m_Datatypes;

	// / Should English default aliases be used in this language?
	protected $m_useEnDefaultAliases = true;
	// / Default English aliases for properties (typically used in all languages)
	static protected $enSpecialProperties = array(
		'SPM has template' => SPM_WF_SP_HAS_TEMPLATE,
		'SPM has multiple template' => SPM_WF_SP_HAS_MULTIPLE_TEMPLATE,

		'SPM has description' => SPM_WF_SP_HAS_DESCRIPTION,
		'SPM has default' => SPM_WF_SP_HAS_DEFAULT,

		'SPM has widget attribute' => SPM_WF_SP_HAS_WIDGET_ATTRIBUTE,
		'SPM has number range' => SPM_WF_SP_HAS_NUM_RANGE,

		'SPM has range namespace' => SPM_WF_SP_HAS_RANGE_NAMESPACE,
		'SPM has range category' => SPM_WF_SP_HAS_RANGE_CATEGORY,
		'SPM has range property' => SPM_WF_SP_HAS_RANGE_PROPERTY,
	);
	// / Default English aliases for namespaces (typically used in all languages)
	static protected $enNamespaceAliases = array(
		'Category_widget'      => NS_CATEGORY_WIDGET,
		'Category_widget_talk' => NS_CATEGORY_WIDGET_TALK,
	);

	function getNamespaces() {
		return $this->m_Namespaces;
	}

	/**
	 * Function that returns an array of namespace aliases, if any.
	 */
	function getNamespaceAliases() {
		return $this->m_useEnDefaultAliases ?
		       $this->m_NamespaceAliases + SPMLanguage::$enNamespaceAliases:
			   $this->m_NamespaceAliases;
	}
	/**
	 * Function that returns the labels for the special properties.
	 */
	function getPropertyLabels() {
		return $this->m_SpecialProperties;
	}
	/**
	 * Aliases for special properties, if any.
	 */
	function getPropertyAliases() {
		return $this->m_useEnDefaultAliases ?
		       $this->m_SpecialPropertyAliases + SPMLanguage::$enSpecialProperties:
			   $this->m_SpecialPropertyAliases;
	}

	function getDatatype( $datatypeID ) {
		return $this->m_Datatypes[$datatypeID];
	}

	/**
	 * Function that returns all content messages (those that are stored
	 * in wome article, and can thus not be translated to individual users).
	 */
	function getContentMsgArray() {
		return $this->wContentMessages;
	}

	/**
	 * Function that returns all user messages (those that are given only to
	 * the current user, and can thus be given in the individual user language).
	 */
	function getUserMsgArray() {
		return $this->wUserMessages;
	}
}