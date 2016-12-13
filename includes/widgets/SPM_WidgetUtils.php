<?php
/**
 * This file contains a static class for accessing functions for
 * widget utilities.
 *
 * @author dch
 */

class SPMWidgetUtils {
	static $widgetViewingTemplatesCategory = 'SPM viewing templates';

	static $boundTemplates = array();
	static $widgetTemplates = array();

	static function parserOnTemplateLoopCheck( &$parser, $title, &$checked ) {
		SPMWidgetViewUtils::initialize();
		if ( $title->getNamespace() == NS_TEMPLATE ) {
			if ( array_key_exists( $title->getText(), self::$widgetTemplates ) ) $checked = true;
		}
		return true;
	}
	static function displayTabVector( &$obj, &$links ) {
		global $wgSPMHideEditTab, $wgSPMShowConnectorEditTab;
		if ( isset( $obj->mTitle ) ) {
			$main_action = array();
			if ( $obj->mTitle->getNamespace() == NS_CATEGORY ) {
				$title = Title::makeTitle( NS_CATEGORY_WIDGET, $obj->mTitle->getDBkey() );
				$classes = array();
				$query = '';
				if ( !$title->isKnown() ) {
					$classes[] = 'new';
					$query = 'action=wfedit&redlink=1';
				}
				$links['namespaces']['nstab-category_widget'] = array(
					'class' => implode( ' ', $classes ),
					'text' => wfMessage( 'nstab-category_widget' )->text(),
					'href' => $title->getLocalUrl( $query )
				);
			} else if ( $obj->mTitle->getNamespace() == NS_CATEGORY_WIDGET ) {
				$content_actions = $links['views'];
				if ( isset( $content_actions['edit'] ) ) {
					global $wgRequest;
					$edit_tab_location = array_search( 'edit', array_keys( $content_actions ) );
					$wfedit_action['wfedit'] = array(
						'class' => ( $wgRequest->getVal( 'action' ) == 'wfedit' ) ? 'selected' : '',
						'text' => wfMessage( 'wfedit_tab' )->text(),
						'href' => preg_replace( '/\baction=edit\b/', 'action=wfedit', $content_actions['edit']['href'] )
					);
					if ( $wgSPMShowConnectorEditTab ) {
						$wfedit_action['wcedit'] = array(
							'class' => ( $wgRequest->getVal( 'action' ) == 'wcedit' ) ? 'selected' : '',
							'text' => wfMessage( 'wcedit_tab' )->text(),
							'href' => preg_replace( '/\baction=edit\b/', 'action=wcedit', $content_actions['edit']['href'] )
						);
					}
					$beforeedit = array_slice( $content_actions, 0, $edit_tab_location );
					$afteredit = array_slice(
							$content_actions,
							( $wgSPMHideEditTab ? $edit_tab_location + 1 : $edit_tab_location ),
							count( $content_actions ) );
					// Merge array with new action
					$content_actions = array_merge( $beforeedit, $wfedit_action );   // add a new action
					$content_actions = array_merge( $content_actions, $afteredit );
				}

				if ( isset( $content_actions['move'] ) ) {
					global $wgRequest;
					$move_tab_location = array_search( 'move', array_keys( $content_actions ) );
					$beforeedit = array_slice( $content_actions, 0, $move_tab_location );
					$afteredit = array_slice( $content_actions, $move_tab_location + 1, count( $content_actions ) );
					// Remove 'move' tab
					$content_actions = array_merge( $beforeedit, $afteredit );
				}
				$title = Title::makeTitle( NS_CATEGORY, $obj->mTitle->getDBkey() );
				$classes = array();
				$query = '';
				if ( !$title->isKnown() ) {
					$classes[] = 'new';
					$query = 'action=edit&redlink=1';
				}
				$links['namespaces']['nstab-category'] = array(
					'class' => implode( ' ', $classes ),
					'text' => wfMessage( 'nstab-category' )->text(),
					'href' => $title->getLocalUrl( $query )
				);

				// Split array
				$beforeedit = array_slice( $content_actions, 0, 1 );
				$afteredit = array_slice( $content_actions, 1, count( $content_actions ) );
				// Merge array with new action
				$content_actions = array_merge( $beforeedit, $main_action );   // add a new action
				$content_actions = array_merge( $content_actions, $afteredit );

				$links['views'] = $content_actions;
			} else {
				return true;
			}
		}
		return true; // always return true, in order not to stop MW's hook processing!
	}

	/**
	 * Add category widget tab
	 * @param $obj
	 * @param $content_actions
	 */
	static function displayTab( $obj, &$content_actions ) {
		global $wgSPMHideEditTab, $wgSPMShowConnectorEditTab;
		if ( isset( $obj->mTitle ) ) {
			$main_action = array();
			if ( $obj->mTitle->getNamespace() == NS_CATEGORY ) {
				$title = Title::makeTitle( NS_CATEGORY_WIDGET, $obj->mTitle->getDBkey() );
				$classes = array();
				$query = '';
				if ( !$title->isKnown() ) {
					$classes[] = 'new';
					$query = 'action=wfedit&redlink=1';
				}
				$main_action['nstab-category_widget'] = array(
					'class' => implode( ' ', $classes ),
					'text' => wfMessage( 'nstab-category_widget' )->text(),
					'href' => $title->getLocalUrl( $query )
				);
			} else if ( $obj->mTitle->getNamespace() == NS_CATEGORY_WIDGET ) {
				if ( isset( $content_actions['edit'] ) ) {
					global $wgRequest;
					$edit_tab_location = array_search( 'edit', array_keys( $content_actions ) );
					$wfedit_action['wfedit'] = array(
						'class' => ( $wgRequest->getVal( 'action' ) == 'wfedit' ) ? 'selected' : '',
						'text' => wfMessage( 'wfedit_tab' )->text(),
						'href' => preg_replace( '/\baction=edit\b/', 'action=wfedit', $content_actions['edit']['href'] )
					);
					if ( $wgSPMShowConnectorEditTab ) {
						$wfedit_action['wcedit'] = array(
							'class' => ( $wgRequest->getVal( 'action' ) == 'wcedit' ) ? 'selected' : '',
							'text' => wfMessage( 'wcedit_tab' )->text(),
							'href' => preg_replace( '/\baction=edit\b/', 'action=wcedit', $content_actions['edit']['href'] )
						);
					}
					$beforeedit = array_slice( $content_actions, 0, $edit_tab_location );
					$afteredit = array_slice(
							$content_actions,
							( $wgSPMHideEditTab ? $edit_tab_location + 1 : $edit_tab_location ),
							count( $content_actions ) );
					// Merge array with new action
					$content_actions = array_merge( $beforeedit, $wfedit_action );   // add a new action
					$content_actions = array_merge( $content_actions, $afteredit );
				}

				if ( isset( $content_actions['move'] ) ) {
					global $wgRequest;
					$move_tab_location = array_search( 'move', array_keys( $content_actions ) );
					$beforeedit = array_slice( $content_actions, 0, $move_tab_location );
					$afteredit = array_slice( $content_actions, $move_tab_location + 1, count( $content_actions ) );
					// Remove 'move' tab
					$content_actions = array_merge( $beforeedit, $afteredit );
				}
				$title = Title::makeTitle( NS_CATEGORY, $obj->mTitle->getDBkey() );
				$classes = array();
				$query = '';
				if ( !$title->isKnown() ) {
					$classes[] = 'new';
					$query = 'action=edit&redlink=1';
				}
				$main_action['nstab-category'] = array(
					'class' => implode( ' ', $classes ),
					'text' => wfMessage( 'nstab-category' )->text(),
					'href' => $title->getLocalUrl( $query )
				);
			} else {
				return true;
			}

			// Split array
			$beforeedit = array_slice( $content_actions, 0, 1 );
			$afteredit = array_slice( $content_actions, 1, count( $content_actions ) );
			// Merge array with new action
			$content_actions = array_merge( $beforeedit, $main_action );   // add a new action
			$content_actions = array_merge( $content_actions, $afteredit );
		}
		return true; // always return true, in order not to stop MW's hook processing!
	}

	/**
	 * Apply wfedit instead of common edit to category widget tab
	 */
	static function tabAction(  &$sk_tmpl,
				$title, $message, $selected, $checkEdit,
				&$classes, &$query, &$text, &$result ) {
		if ( $title->getNamespace() == NS_CATEGORY_WIDGET ) {
			$query = preg_replace( '/\baction=edit\b/', 'action=wfedit', $query );
		}
		return true;
	}

	static function applyWidgetDesignAction( $action, $article ) {
		if ( $action != 'wfedit' ) {
			return true;
		}
		$title = $article->getTitle();
		if ( $title->getNamespace() != NS_CATEGORY_WIDGET ) {
			return true;
		}
		$title->invalidateCache();
		$article = new SPMWidgetDesignPage( $title );
		$article->view();

		// The resolution of timestamps for the cache is only in seconds. Invalidate
		// the cache by setting a timestamp 2 seconds from now.
		$now = wfTimestamp( TS_MW, time() + 2 );
		$dbw = wfGetDB( DB_MASTER );
		$success = $dbw->update( 'page',
			array( /* SET */
						'page_touched' => $now
			), array( /* WHERE */
						'page_namespace' => $title->getNamespace() ,
						'page_title' => $title->getDBkey()
			), 'SPMWidgetUtils::applyWidgetDesignAction'
		);

		return false;
	}

	static function applyWidgetDesignAction2( $action, $article ) {
		if ( $action != 'wcedit' ) {
			return true;
		}
		$title = $article->getTitle();
		if ( $title->getNamespace() != NS_CATEGORY_WIDGET ) {
			return true;
		}
		$title->invalidateCache();
		$article = new SPMWidgetDesignPage2( $title );
		$article->view();

		// The resolution of timestamps for the cache is only in seconds. Invalidate
		// the cache by setting a timestamp 2 seconds from now.
		$now = wfTimestamp( TS_MW, time() + 2 );
		$dbw = wfGetDB( DB_MASTER );
		$success = $dbw->update( 'page',
			array( /* SET */
						'page_touched' => $now
			), array( /* WHERE */
						'page_namespace' => $title->getNamespace() ,
						'page_title' => $title->getDBkey()
			), 'SPMWidgetUtils::applyWidgetDesignAction2'
		);

		return false;
	}

	static function widgetViewPage( &$title, &$article ) {
		switch( $title->getNamespace() ) {
			case NS_CATEGORY_WIDGET:
				$article = new SPMWidgetPage( $title );
		}
		return true;
	}

	static function addHTMLHeader( &$out ) {
		// MediaWiki 1.17 introduces the Resource Loader.
		$realFunction = array( 'SMWOutputs', 'requireResource' );
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
			if ( $out->getTitle()->getNamespace() != NS_CATEGORY_WIDGET ) {
				$out->addModules( 'ext.wes.spm_page' );
			} else {
				$out->addModules( 'ext.wes.spm_common' );
			}
		} else {
			global $wgSPMScriptPath;
			$out->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/spm_wf_widgets.css'
					) );
			$out->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/inettuts.css'
					) );
			if ( $out->getTitle()->getNamespace() != NS_CATEGORY_WIDGET ) {
				$out->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/spm_common.js"></script>' );
			}
		}

		return true;
	}

	static function addWFInput( $categoryPage ) {
		global $wgOut;
		$wgOut->addWikiText( wfMessage( 'wf_spm_hint_wfinput' )->text() );
		$wgOut->addWikiText( "{{#wfinput:{$categoryPage->getTitle()->getText()}|}}" );

		return true;
	}

	static function onWidgetMove( $title, $new_title, $user, &$err, $reason ) {
		if ( $title->getNamespace() == NS_CATEGORY_WIDGET ) {
			$err = wfMessage( 'immobile-source-namespace', $title->getNsText() )->text();
			return false;
		}

		return true;
	}


	static function initProperties() {
		global $wgSPMContLang;
		$wf_props = $wgSPMContLang->getPropertyLabels();
		if ( array_key_exists( SPM_WF_SP_HAS_TEMPLATE, $wf_props ) )
			SMWPropertyValue::registerProperty( '___SPM_WF_ST', '___wpw', $wf_props[SPM_WF_SP_HAS_TEMPLATE], true );
		if ( array_key_exists( SPM_WF_SP_HAS_MULTIPLE_TEMPLATE, $wf_props ) )
			SMWPropertyValue::registerProperty( '___SPM_WF_MT', '___wpw', $wf_props[SPM_WF_SP_HAS_MULTIPLE_TEMPLATE], true );
//		if ( array_key_exists( SPM_WF_SP_HAS_VIEW_NAME, $wf_props ) )
//			SMWPropertyValue::registerProperty( '___SPM_WF_VN', '_str', $wf_props[SPM_WF_SP_HAS_VIEW_NAME], true );

		if ( array_key_exists( SPM_WF_SP_HAS_DESCRIPTION, $wf_props ) )
			SMWPropertyValue::registerProperty( '___SPM_WF_HD', '_str', $wf_props[SPM_WF_SP_HAS_DESCRIPTION], true );
		if ( array_key_exists( SPM_WF_SP_HAS_DEFAULT, $wf_props ) )
			SMWPropertyValue::registerProperty( '___SPM_WF_DF', '_txt', $wf_props[SPM_WF_SP_HAS_DEFAULT], true );
		if ( array_key_exists( SPM_WF_SP_ALLOWS_VALUE, $wf_props ) )
			SMWPropertyValue::registerProperty( '___SPM_PVAL', '_txt', $wf_props[SPM_WF_SP_ALLOWS_VALUE], true );

		if ( array_key_exists( SPM_WF_SP_HAS_USER_ACL, $wf_props ) )
			SMWPropertyValue::registerProperty( '___SPM_WF_AC', '_txt', $wf_props[SPM_WF_SP_HAS_USER_ACL], true );

		// also initialize hardcoded English values, if it's a non-English-language wiki
		SMWPropertyValue::registerProperty( '___SPM_WF_ST_BACKUP', '___wpw', 'SPM has template', true );
		SMWPropertyValue::registerProperty( '___SPM_WF_MT_BACKUP', '___wpw', 'SPM has multiple template', true );
//		SMWPropertyValue::registerProperty( '___SPM_WF_VN_BACKUP', '_str', 'SPM has view name', true );

		SMWPropertyValue::registerProperty( '___SPM_WF_HD_BACKUP', '_str', 'SPM has description', true );
		SMWPropertyValue::registerProperty( '___SPM_WF_DF_BACKUP', '_txt', 'SPM has default', true );
		SMWPropertyValue::registerProperty( '___SPM_PVAL_BACKUP', '_txt', 'SPM allows value', true );

		SMWPropertyValue::registerProperty( '___SPM_WF_AC_BACKUP', '_txt', 'SPM has user ACL', true );

		SPMWidgetDataTypeUtils::initProperties();

		return true;
	}

	static function smwInitDatatypes() {
		global $wgAutoloadClasses, $wgSPMIP, $wgSPMContLang;
		$wgAutoloadClasses['SMSPMxtendedWikiPageValue'] = $wgSPMIP . '/includes/widgets/SMW_DV_ExtendedWikiPage.php';
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			SMWDataValueFactory::registerDatatype( '___wpw', 'SMSPMxtendedWikiPageValue', SMWDataItem::TYPE_WIKIPAGE );
			// in SMW 1.6, have to register default _wpg back, to SMWDataValueFactory::$mNewDataItemIds
			// could be a bug in SMW 1.6
			SMWDataValueFactory::registerDatatype( '_wpg', 'SMWWikiPageValue', SMWDataItem::TYPE_WIKIPAGE );
		} else {
			SMWDataValueFactory::registerDatatype( '___wpw', 'SMSPMxtendedWikiPageValue' );
		}

		return true;
	}




	/**
	 * Widget hierarchy based on category name
	 *
	 * @param $cate_name category name
	 * @param $cateset category name set
	 * @param $widgets widget property settings, array( 'prop' => x, 'value' => x, 'category' => x
	 *
	 * returns category hierarchy
	 */
	static function getSuperWidgetProperties( $cate_name, &$widgets ) {
		$cateset = array( $cate_name );
		$cates = self::getCategoryHierarchy( $cate_name, $cateset, $widgets );

		// popup the current category
		array_shift( $cateset );

		// array( 'prop' => , 'value' =>  'category' => )
		$widgets = array();
		// upside down
		foreach ( array_reverse( $cateset ) as $c ) {
			$ws = self::getWidgetProperties( $c );
			foreach ( $ws as $w ) {
				$w['category'] = $c;
				$widgets[] = $w;
			}
		}

		return $cates;
	}
	/**
	 * Get category hierarchy, apart from given category name
	 *
	 * @param string $cate_name
	 * @param array &$cateset, all category names in commen set
	 * return
	 *  	category hierarchy in tree. e.g.,
	 *  	array(
	 *  		'father' => array(
	 *  			'grandpa' => null,
	 *  			'ancestor' => null
	 *  		),
	 *  		'mother' => null
	 *  	)
	 */
	static function getCategoryHierarchy( $cate_name, &$cateset ) {
		$fname = "SPMWidgetUtils::getCategoryHierarchy";

		$cates = array();
		// merge categories
		$categoryTitle = Title::newFromText( $cate_name, NS_CATEGORY );
		$db =& wfGetDB( DB_SLAVE );
		extract( $db->tableNames( 'categorylinks' ) );
		$res = $db->query( "SELECT $categorylinks.cl_to FROM $categorylinks WHERE
			$categorylinks.cl_from = {$categoryTitle->getArticleID()}", $fname );
		if ( $db->numRows( $res ) > 0 ) {
			while ( $row = $db->fetchObject( $res ) ) {
				$c = str_replace( '_', ' ', ucfirst( $row->cl_to ) );
				if ( !in_array( $c, $cateset ) ) {
					$cates[] = $c;
					$cateset[] = $c;
				}
			}
		}
		$db->freeResult( $res );

		$ret = array();
		foreach ( $cates as $c ) {
			$ret[$c] = self::getCategoryHierarchy( $c, $cateset );
		}

		return $ret;
	}
	static function getWidgetProperties( $cate_name ) {
		$cate_name = Title::newFromText( $cate_name, NS_CATEGORY )->getText();
		$title = Title::newFromText( $cate_name, NS_CATEGORY_WIDGET );

		$widgets = array();

		$store = smwfGetStore();
		foreach ( array( '___SPM_WF_ST', '___SPM_WF_MT' ) as $ptxt ) {
			$property = SMWPropertyValue::makeProperty( $ptxt );
			if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
				$props = $store->getPropertyValues( new SMWDIWikiPage( $title->getDBkey(), $title->getNameSpace(), '' ), $property->getDataItem() );
			} else {
				$props = $store->getPropertyValues( $title, $property );
			}
			foreach ( $props as $propvalue ) {
				$widgets[] = array(
					'prop' => $property->getWikiValue(),
					'value' => self::getPrefixedText( $propvalue )
				);
			}
		}

		return $widgets;
	}

	public static function getWidgetData( $title ) {
		if ( $title->getNamespace() != NS_CATEGORY_WIDGET ) return array();

		$cate_name = $title->getText();
		SPMWidgetUtils::getSuperWidgetProperties( $cate_name, $widgets );
		foreach ( SPMWidgetUtils::getWidgetProperties( $cate_name ) as $w ) {
			$widgets[] = $w;
		}

		return self::getPageTemplateData( $widgets );
	}

	private static function getPageTemplateData( $widgets, WOMPageModel $page_obj = null, $get_value = false, $t_vals = array() ) {
		SPMWidgetViewUtils::initialize();

		foreach ( $t_vals as $k => $v ) {
			$t_vals[Title::newFromText( $k )->getDBkey()] = $v;
		}

		$wom_tmpls = array();
		if ( $page_obj != null ) {
			$wom_tmpls = $page_obj->getObjectsByTypeID( WOM_TYPE_TEMPLATE );
		}

		global $wgSPMContLang;
		$wf_props = $wgSPMContLang->getPropertyLabels();

		$tmpl_data = array();
		foreach ( $widgets as $w ) {
			if ( $w['prop'] == $wf_props[SPM_WF_SP_HAS_TEMPLATE] ||
				$w['prop'] == $wf_props[SPM_WF_SP_HAS_MULTIPLE_TEMPLATE]
				) {
					$title = Title::newFromText( $w['value'], NS_TEMPLATE );
					$revision = Revision::newFromTitle( $title );
					if ( $revision == null ) continue;

					$name = $title->getText();
					$vals = array();
					if ( $get_value ) {
						foreach ( $wom_tmpls as $page_tmpl ) {
							$tname = Title::newFromText( $page_tmpl->getName(), NS_TEMPLATE )->getText();
							if ( $tname == $name ) {
								foreach ( $page_tmpl->getObjects() as $tmpl_field ) {
									$val = $tmpl_field->getValueText();
									if ( $tmpl_field->getKey() == '' ) {
										$vals[] = $val;
									} else {
										$vals[$tmpl_field->getKey()] = $val;
									}
								}
								break;
							}
						}
					}

					$text = $revision->getText();

					$tmpl_name = $title->getDBkey();
					$tmpl_data[$tmpl_name] = array();
					foreach ( SPMArticleUtils::parsePageTemplates( $text ) as $t ) {
						if ( is_array( $t ) && array_key_exists( $t['name'], self::$widgetTemplates ) ) {
							$r = preg_match( '/\{\{\{([^|}]+)/', $t['fields'][2], $m );
							if ( $r ) {
								$key = trim( $m[1] );

								$value = isset( $vals[$key] ) ? $vals[$key] : '';
								// apply default
								$prop_settings = self::getPropertySettings( $t['fields'][1] );

								if ( isset( $t_vals[$tmpl_name] ) && isset( $t_vals[$tmpl_name][$key] ) ) {
									$replace = isset( $vals[$key] ) ? $vals[$key] : '';
									$value = str_replace( '%_VAL_%', $replace, $t_vals[$tmpl_name][$key] );
								} else {
									$value = isset( $vals[$key] ) ?
										$vals[$key] :
										$prop_settings['prop_instance']->getDefaultValue( $prop_settings['title'] );
								}

								$tmpl_data[$tmpl_name][$key] = array(
									'label' => $t['fields'][0],
									'property' => $t['fields'][1],
									'value' => $value,
									'params' => array(
										'multiple' => ( isset( $t['fields']['multiple'] ) && ( 'true' == strtolower( $t['fields']['multiple'] ) ) ),
										'optional' => ( isset( $t['fields']['optional'] ) && ( 'true' == strtolower( $t['fields']['optional'] ) ) ),
										'editidx' => ( isset( $t['fields']['editidx'] ) ? intval( trim( $t['fields']['editidx'] ) ) : '' ),
										't_vals' => $t_vals,
										'raw' => $t['raw'],
										'tmpl_name' => $t['name'],
									)
								);
							}
						}
					}
				}
		}
		return $tmpl_data;
	}
	static function updateWidgetValues( $widget_name, WOMPageModel $page_obj ) {
		// get category widget templates
		$cate_name = Title::newFromText( $widget_name, NS_CATEGORY )->getText();
		self::getSuperWidgetProperties( $cate_name, $widgets );
		foreach ( self::getWidgetProperties( $cate_name ) as $w ) {
			$widgets[] = $w;
		}

		global $wgRequest, $smwgRecursivePropertyValues;
		$wom_tmpls = $page_obj->getObjectsByTypeID( WOM_TYPE_TEMPLATE );
		$templates = self::getPageTemplateData( $widgets, $page_obj );
		foreach ( $templates as $tmpl_name => $fields ) {
			if ( !$wgRequest->getArray( $tmpl_name ) ) {
				$wom_obj = null;
				foreach ( $wom_tmpls as $wt ) {
					$n = Title::newFromText( $wt->getName(), NS_TEMPLATE )->getDBkey();
					if ( $n == $tmpl_name ) {
						$wom_obj = $wt;
						break;
					}
				}
				if ( $wom_obj == null ) {
					// just parse the content,
					$new_obj = WOMProcessor::parseToWOM( "{{{$tmpl_name}|\n}}" );
					$page_obj->appendChildObject( $new_obj );
				}
				continue;
			}
			foreach ( $wgRequest->getArray( $tmpl_name ) as $id => $tmpl_vals ) {
				// get wom target
				$idx = 0;
				$wom_obj = null;
				foreach ( $wom_tmpls as $wt ) {
					$n = Title::newFromText( $wt->getName(), NS_TEMPLATE )->getDBkey();
					if ( $n == $tmpl_name ) {
						if ( $idx < $id ) {
							++ $idx;
						} else {
							$wom_obj = $wt;
							break;
						}
					}
				}
				$tmpl_text = "{{{$tmpl_name}|";
				if ( count( $fields ) > 0 ) {
					foreach ( $tmpl_vals as $key => $val ) {
						$prop_settings = self::getPropertySettings( $fields[$key]['property'] );

						$value = $prop_settings['prop_instance']->getFieldValue( $val, $prop_settings['title'] );
						if ( !$smwgRecursivePropertyValues ) $value = self::encodeValue( $value );

						if ( $value != '' ) {
							$tmpl_text .= "\n{$key}={$value}|";
						}
					}
				}
				$tmpl_text .= "\n}}";

				// just parse the content,
				$new_obj = WOMProcessor::parseToWOM( $tmpl_text );
				// insert before this object,
				if ( $wom_obj != null ) {
					$page_obj->updatePageObject( $new_obj, $wom_obj->getObjectID() );
				} else {
					$page_obj->appendChildObject( $new_obj );
				}
			}
		}
	}
	public static function encodeValue( $val ) {
		$val = str_replace( '{', '&#123;', $val );
		$val = str_replace( '}', '&#125;', $val );
		$val = str_replace( '|', '&#124;', $val );
		$val = str_replace( '[', '&#91;', $val );
		$val = str_replace( ']', '&#93;', $val );

		return $val;
	}
	public static function decodeValue( $val ) {
		$val = str_replace( '&#123;', '{', $val );
		$val = str_replace( '&#125;', '}', $val );
		$val = str_replace( '&#124;', '|', $val );
		$val = str_replace( '&#91;', '[', $val );
		$val = str_replace( '&#93;', ']', $val );

		return $val;
	}
	private static function validateTransactionProperty( $templates, $key, &$tmpl_name, &$field_name ) {
		$tf = explode( '/', $key, 2 );
		$t = Title::newFromText( $tf[0], NS_TEMPLATE );
		if ( $t == null ) return false;
		$tmpl_name = $t->getDBkey();
		$field_name = $tf[1];
		return ( isset( $templates[$tmpl_name] ) && isset( $templates[$tmpl_name][$field_name] ) );
	}
	private static function loadTransactionConnectorExpressions( $cate_name ) {
		$tmpExps = SPMWidgetParserFunctions::$connectorExpressions;

		$title = Title::newFromText( $cate_name, NS_CATEGORY_WIDGET );
		$r = Revision::newFromTitle( $title );
		if ( $r == null ) return array();

		SPMWidgetParserFunctions::reset();
		global $wgParser, $wgUser;
		$options = ParserOptions::newFromUser( $wgUser );
		$wgParser->parse( $r->getText(), $title, $options );

		$connectorExpressions = SPMWidgetParserFunctions::$connectorExpressions;

		SPMWidgetParserFunctions::$connectorExpressions = $tmpExps;

		return $connectorExpressions;
	}
	private static function loadTransactionConnectors( $connectorExpressions, &$templates ) {
		foreach ( $connectorExpressions as $id => $tc ) {
			// validate transaction
			if ( !self::validateTransactionProperty( $templates, $tc['target'], $tn, $fn ) ) continue;
			$valid = true;
			foreach ( $tc['src'] as $key ) {
				if ( !self::validateTransactionProperty( $templates, $key, $tn, $fn ) ) {
					$valid = false;
					break;
				}
			}
			if ( !$valid ) continue;

			// apply transaction data
			self::validateTransactionProperty( $templates, $tc['target'], $tn, $fn );
			// FIXME: multiple transaction connector on the same target is forbidden
			$templates[$tn][$fn]['trans_target'] = $id;
			foreach ( $tc['src'] as $idx => $key ) {
				self::validateTransactionProperty( $templates, $key, $tn, $fn );
				$templates[$tn][$fn]['trans_src'][] = array(
					'trans_id' => $id,
					'index' => $idx
				);
			}
		}
	}
	static function applyWYSIWYG() {
		global $wgspmWYSIWYGed, $wgOut;
		if ( !$wgspmWYSIWYGed ) {
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wysiwyg/ckeditor/ckeditor.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wysiwyg/script.js"></script>' );
			$wgspmWYSIWYGed = true;
		}
	}

	static function getWidgetAssemblerHtml( $widget_name, WOMPageModel $page_obj, $t_vals = array() ) {
		global $wgSPMScriptPath, $wgOut;
		// FIXME: MW resource loader issue, @import flag will not work in css resource loader
		$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/jquery-ui/base/jquery.ui.all.css'
					) );

		// MediaWiki 1.17 introduces the Resource Loader.
		$realFunction = array( 'SMWOutputs', 'requireResource' );
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
			$wgOut->addModules( 'ext.wes.spm_form' );
		} else {
//			$wgOut->addLink( array(
//						'rel'   => 'stylesheet',
//						'type'  => 'text/css',
//						'media' => 'screen, projection',
//						'href'  => $wgSPMScriptPath . '/skins/jquery-ui/base/jquery.ui.all.css'
//					) );
			$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/style.css'
					) );
			$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/wf_editor/spm_wf_editor.css'
					) );
			$wgOut->addLink( array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'screen, projection',
						'href'  => $wgSPMScriptPath . '/skins/spm_wf_widgets.css'
					) );

	//		$wgOut->addScript('<script type="text/javascript" src="http://code.jquery.com/jquery-1.4.3.js"></script>');
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/jquery-1.4.3.min.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/jquery-ui-1.8.9.custom.min.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_editor/jquery-ui-timepicker-addon.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_editor/jquery.tools.min.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_editor/spm_wf_fld_connector.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/wf_editor/spm_wf_fld_editor.js"></script>' );
		}

		self::applyWYSIWYG();

		$link = Title::newFromText( "Special:WidgetAssembler" );
		$url = $link->getLocalURL();

		$wgOut->addScript( '
<script type="text/javascript">
	spm_wf_field.formUrl = "' . $url . '";
</script>' );

		return self::getWidgetInputHtml( $widget_name, $page_obj, $t_vals );
	}
	static $widgetIdx = 0;
	public static function getWidgetInputHtml( $widget_name, WOMPageModel $page_obj, $t_vals = array() ) {
		$widgetIdx = self::$widgetIdx;

		++ self::$widgetIdx;

		$cate_name = Title::newFromText( $widget_name, NS_CATEGORY )->getText();
		self::getSuperWidgetProperties( $cate_name, $widgets );
		foreach ( self::getWidgetProperties( $cate_name ) as $w ) {
			$widgets[] = $w;
		}

		$templates = self::getPageTemplateData( $widgets, $page_obj, true, $t_vals );
		$connectorExpressions = self::loadTransactionConnectorExpressions( $cate_name );
		self::loadTransactionConnectors( $connectorExpressions, $templates );

		$database = 'spm_wf_connector.data[' . $widgetIdx . ']';
		$js = '
<script type="text/javascript">
' . $database . ' = {
	target: {},
	src: {},
	widget: "' . str_replace( '"', '\"', $widget_name ) . '",
	page: "' . str_replace( '"', '\"', $page_obj->getTitle()->getFullText() ) . '" };';

		$html = '';
		$idx = 0;
		foreach ( $templates as $tmpl_name => $fields ) {
			if ( count( $fields ) == 0 ) continue;
			$html .= '
<!--<h2 class="spm_wf_h2">' . htmlspecialchars( $tmpl_name ) . '</h2>-->
<table class="spm_wf_table">';
			$field_htmls = array();
			$idxs = array();
			$field_idx = 0;
			foreach ( $fields as $key => $f ) {
				$id = 'spm_wf_fld_' . $widgetIdx . '_' . $idx;

				$extra_semdata = null;
				if ( isset( $f['trans_target'] ) ) {
					$exp = $connectorExpressions[$f['trans_target']];
					$params = array();
					foreach ( $exp['src'] as $i => $s ) {
						self::validateTransactionProperty( $templates, $s, $tn, $fn );
						$params[$i] = $templates[$tn][$fn]['value'];
					}
					$extra_semdata = self::getTransactionResult( $exp, $params );
				}
				$prop_settings = self::getPropertySettings( $f['property'] );
				if ( $f['params']['editidx'] !== '' ) {
					$idxs[$f['params']['editidx']] = $field_idx;
				}

				$field_htmls[$field_idx] = $prop_settings['prop_instance']->getEditorUI(
						$page_obj->getTitle(),
						$id, $f['label'],
						$tmpl_name, $key,
						self::decodeValue( $f['value'] ),
						$prop_settings['title'],
						$extra_semdata,
						$f['params'] );
				++ $field_idx;

				if ( isset( $f['trans_src'] ) ) {
					$js .= '
' . $database . '.src.' . $id . ' = [];';
					foreach ( $f['trans_src'] as $ts ) {
						$js .= '
' . $database . '.src.' . $id . '.push("' . $ts['trans_id'] . '_' . $ts['index'] . '");';
					}
				}
				if ( isset( $f['trans_target'] ) ) {
					$js .= '
' . $database . '.target._' . $f['trans_target'] . ' = "' . $id . '";';
				}

				++ $idx;
			}
			ksort( $idxs );
			foreach ( $idxs as $i => $j ) {
				$html .= $field_htmls[$j];
				$field_htmls[$j] = '';
			}
			$html .= implode( '', $field_htmls );
			$html .= '
</table>';
		}
		$js .= '
</script>';

		global $wgOut;
		$wgOut->addScript( $js );

		return '<table id="spm_wf_form_group_' . $widgetIdx . '" class="stylized"><tr><td>' . $html . '</td></tr></table>' . "\n";
	}
	public static function updateDefaultTransaction( $content, $cate_name ) {
		$widgets = array();
		foreach ( self::getWidgetProperties( $cate_name ) as $w ) {
			$widgets[] = $w;
		}
		$page_obj = WOMProcessor::parseToWOM( $content );
		$templates = self::getPageTemplateData( $widgets, $page_obj, true );
		$connectorExpressions = self::loadTransactionConnectorExpressions( $cate_name );
		self::loadTransactionConnectors( $connectorExpressions, $templates );

		$tmpls = array();
		foreach ( $templates as $tmpl => $flds ) {
			foreach ( $flds as $name => $settings ) {
				if ( $settings['value'] != '' ) {
					$val = trim( $settings['value'] );
					$tmpls[$tmpl][$name] = $val;
					if ( isset( $settings['trans_src'] ) ) {
						// FIXME: no recursive or contradictory check
						foreach ( $settings['trans_src'] as $trans ) {
							$connectorExpressions[$trans['trans_id']]['src'][$trans['index']] = $val;
						}
					}
				}
			}
		}
		foreach ( $connectorExpressions as $trans ) {
			$extra_semdata = self::getTransactionResult( $trans, $trans['src'] );
			$prop_settings = self::getPropertySettings( $trans['target'] );
			$vals = $prop_settings['prop_instance']->getAllPossibleValues( $prop_settings['title'], $extra_semdata );
			if ( count( $vals ) == 1 ) {
				$tf = explode( '/', $trans['target'], 2 );
				$tmpl_name = Title::newFromText( $tf[0], NS_TEMPLATE )->getDBkey();
				$field_name = $tf[1];
				if ( isset( $tmpls[$tmpl_name][$field_name] ) ) continue;
				$val = trim( $vals[0] );
				if ( $val === '' ) continue;
				$tmpls[$tmpl_name][$field_name] = $val;
			}
		}
		$content = '';
		foreach ( $tmpls as $t => $fvs ) {
			$content .= "{{{$t}|\n";
			foreach ( $fvs as $f => $v ) {
				$content .= "{$f}={$v}|\n";
			}
			$content .= "}}\n";
		}
		return $content;
	}
	private static function getTransactionResult( $trans_exp, $params ) {
		$exp = $trans_exp['exp'];
		foreach ( $params as $i => $v ) {
			$exp = preg_replace( '/\{\{\{\s*' . ( $i + 1 ) . '\s*\}\}\}/', $v, $exp );
		}
		$title = Title::newFromText( 'SPMWidgetUtils::getTransactionResult' );
		global $wgParser, $wgUser;
		$options = ParserOptions::newFromUser( $wgUser );
		$wgParser->parse( $exp, $title, $options );
		return SMWParseData::getSMWdata( $wgParser );
	}
	static function hitTransaction( $current_val, $field_params, $cate_name, $trans_id, $params ) {
		$connectorExpressions = self::loadTransactionConnectorExpressions( $cate_name );
		$ps = array();
		for ( $i = 0; $i < count( $params ); ++$i ) {
			$idx = $params[$i];
			++$i;
			$ps[$idx] = $params[$i];
		}
		$trans_exp = $connectorExpressions[$trans_id];
		$extra_semdata = self::getTransactionResult( $trans_exp, $ps );

		$prop_settings = self::getPropertySettings( $trans_exp['target'] );

		$tf = explode( '/', $trans_exp['target'], 2 );
		$tmpl_name = Title::newFromText( $tf[0], NS_TEMPLATE )->getDBkey();
		$field_name = $tf[1];

		return $prop_settings['prop_instance']->getEditorHtml(
				null,
				$tmpl_name, $field_name, $current_val,
				$prop_settings['title'],
				$extra_semdata,
				$field_params,
				true
			);
	}
	static function getPropertySettings( $prop_name ) {
		$proptitle = Title::newFromText( $prop_name, SMW_NS_PROPERTY );

		$store = smwfGetStore();
		if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
			$types = $store->getPropertyValues( new SMWDIWikiPage( $proptitle->getDBkey(), $proptitle->getNameSpace(), '' ), SMWPropertyValue::makeProperty( '_TYPE' )->getDataItem() );
		} else {
			$types = $store->getPropertyValues( $proptitle, SMWPropertyValue::makeProperty( '_TYPE' ) );
		}

		// FIXME: - more than one type not handled
		$prop_instance = '_wpg';
		if ( count( $types ) > 0 ) {
			if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
				$prop_instance = $types[0]->getFragment();
			} else {
				$field_type = $types[0]->getWikiValue();
				$prop_instance = SMWDataValueFactory::findTypeID( $field_type );
			}
		}
		$prop_instance = SPMWidgetDataTypeUtils::getDataTypeBySMWTypeID( $prop_instance );

		return array(
			'title' => $proptitle,
			'prop_instance' => $prop_instance,
		);
	}

	static function getFieldConnectorHtml( $name = null ) {
		$conn_html = '';
		if ( $name != null ) {
			$connectorExpressions = self::loadTransactionConnectorExpressions( $name );
		} else {
			$connectorExpressions = SPMWidgetParserFunctions::$connectorExpressions;
		}

		foreach ( $connectorExpressions as $exp ) {
			$src_html = '';
			foreach ( $exp['src'] as $src ) {
				$src_html .= wfMessage( 'wf_wc_html_exp_src', $src )->escaped();
			}

			$conn_html .= wfMessage( 'wf_wc_html_exp',
				$exp['target'],
				str_replace( "\n", "<br/>\n", $exp['exp'] ),
				$src_html )->escaped();
		}

		return $conn_html;
	}

	/**
	 * update widget connector expressions
	 *
	 * @param Title $title
	 * @param array $wfexps
	 */
	static function updateWidgetConnectors( Title $title, array $wfexps ) {
		$revision = Revision::newFromTitle( $title );
		if ( $revision != null ) $text = $revision->getText();
		// remove all wfexp parser functions
		$wom = WOMProcessor::parseToWOM( $text );
		foreach ( $wom->getObjectsByTypeID( WOM_TYPE_PARSERFUNCTION ) as $pf ) {
			if ( strtolower( $pf->getFunctionKey() ) == 'wfexp' ) {
				$wom->removePageObject( $pf->getObjectID() );
			}
		}

		$pf_text = '';
		foreach ( $wfexps as $conn ) {
			$conn = html_entity_decode( $conn );
			// no LF here, just one by one
			$pf_text .= "{{#wfexp:{$conn}}}";
		}
		$wom->appendChildObject( new WOMTextModel( $pf_text ) );

		$page = WikiPage::factory( $title );
		$ret = $page->doEditContent(
			ContentHandler::makeContent( $wom->getWikiText(), $title ),
			'Edit by Widget Connector Designer'
		);
		if ( !$ret->isOK() ) {
			return $ret->getWikiText();
		}

		return true;
	}

	static function getPrefixedText( $smw_value ) {
		if ( !( $smw_value instanceof SMWWikiPageValue ) ) {
			if ( version_compare( SMW_VERSION, '1.6', '>=' ) ) {
				if ( $smw_value instanceof SMWDIWikiPage ) {
					return self::getTitlePrefixedText( $smw_value->getTitle() );
				}
			}

			return $smw_value->getWikiValue();
		}
		if ( floatval( SMW_VERSION ) < 1.5 ) {
			$smw_value->setUserValue( $smw_value->getWikiValue() );
		}

		return self::getTitlePrefixedText( $smw_value->getTitle() );
	}

	static function getTitlePrefixedText( $title ) {
		if ( !( $title instanceof Title ) ) {
			return '';
		}

		if ( floatval( SMW_VERSION ) < 1.5 ) {
			$p = '';
			if ( $title->getInterwiki() != '' ) {
				$p = $title->getInterwiki() . ':';
			}
			if ( $title->getNamespace() != NS_MAIN ) {
				$p = $title->getNsText() . ':';
			}
			$p .= $title->getText();
			return str_replace( '_', ' ', $p );
		} else {
			return $title->getPrefixedText();
		}
	}

	static function getWidgetTemplateFieldViewSettings( $template_name ) {
		SPMWidgetViewUtils::initialize();
		$view_tmpls = array();
		foreach ( self::$widgetTemplates as $t => $v ) {
			if ( $v == '' ) continue;
			$view_tmpls[] = Title::newFromText( $t, NS_TEMPLATE )->getText();
		}

		$fields = array();
		$tmpl_title = Title::newFromText( $template_name, NS_TEMPLATE );
		try {
			$wf_tmpls = WOMProcessor::getPageTemplates( $tmpl_title );
			foreach ( $wf_tmpls as $t ) {
				if ( !in_array( $t->getName(), $view_tmpls ) ) continue;

				$fs = $t->getObjects();
				if ( count( $fs ) < 3 ) continue;

				$field = $fs[2]->getValueText();
				if ( preg_match( '/\{\{\{\s*([^}|]+)\s*(?:\|.*)?\}\}\}/', $field, $m ) ) {
					$field = $m[1];
				}

				$fields[] = array(
					'label' => $fs[0]->getValueText(),
					'property' => $fs[1]->getValueText(),
					'field' => $field
				);
			}
		} catch ( Exception $e ) {
		}

		return $fields;
	}

	public static function registerResourceModules() {
		global $wgResourceModules, $wgSPMIP, $wgSPMScriptPath;
		wfSPMGetLocalJSLanguageScripts( $pathlng, $userpathlng );

		$moduleTemplate = array(
			'localBasePath' => $wgSPMIP,
			'remoteBasePath' => $wgSPMScriptPath,
			'group' => 'ext.wes'
		);

		$wgResourceModules['ext.wes.spm_common'] = $moduleTemplate + array(
			'styles' => array( 'skins/inettuts.css', 'skins/spm_wf_widgets.css' ),
		);
		$wgResourceModules['ext.wes.spm_page'] = $moduleTemplate + array(
			'scripts' => array(
				'scripts/spm_common.js',
				),
			'dependencies' => array(
				'ext.wes.spm_common'
			)
		);

		// FIXME: MW resource loader issue, @import flag will not work in css resource loader
		$wgResourceModules['ext.wes.spm_view'] = $moduleTemplate + array(
			'scripts' => array(
				'scripts/Language/SPMLanguage.js',
				$pathlng,
				$userpathlng,
				'scripts/jquery-ui-1.8.9.custom.min.js'
				),
			'styles' => array(
//				'skins/jquery-ui/base/jquery.ui.all.css',
				'skins/style.css',
				),
			'dependencies' => array(
				'ext.wes.spm_common'
			)
		);
		$wgResourceModules['ext.wes.spm_designer'] = $moduleTemplate + array(
			'scripts' => array(
				'scripts/wf_designer/fg.menu.js',
				'scripts/colorpicker/js/colorpicker.js',
				'scripts/wysiwyg/ckeditor/ckeditor.js',
				'scripts/wysiwyg/script.js',
				'scripts/wf_designer/spm_wf_designer.js',
				'scripts/wf_designer/inettuts.js',
				),
			'styles' => array(
				'skins/wf_designer/fg.menu.css',
				'scripts/colorpicker/css/colorpicker.css',
				'skins/wf_designer/spm_wf_designer.css',
				'skins/wf_designer/inettuts.css',
				'skins/wf_designer/inettuts.js.css',
				),
			'dependencies' => array(
				'ext.wes.spm_view'
			)
		);
		$wgResourceModules['ext.wes.spm_conn'] = $moduleTemplate + array(
			'scripts' => array(
				'scripts/wf_conn_designer/spm_wf_fld_conn_designer.js'
				),
			'styles' => array(
				'/skins/wf_conn_designer/spm_wf_fld_conn_designer.css',
				'skins/wf_designer/spm_wf_designer.css'
				),
			'dependencies' => array(
				'ext.wes.spm_view',
				'ext.jquery.fancybox'
			)
		);

		$wgResourceModules['ext.wes.spm_form'] = $moduleTemplate + array(
			'scripts' => array(
				'scripts/jquery-ui-1.8.9.custom.min.js',
				'scripts/wf_editor/jquery-ui-timepicker-addon.js',
				'scripts/wf_editor/jquery.tools.min.js',
				'scripts/wf_editor/spm_wf_fld_connector.js',
				'scripts/wf_editor/spm_wf_fld_editor.js'
				),
			'styles' => array(
//				'skins/jquery-ui/base/jquery.ui.all.css',
				'skins/style.css',
				'skins/wf_editor/spm_wf_editor.css',
				),
			'dependencies' => array(
				'ext.wes.spm_common'
			)
		);
		$wgResourceModules['ext.wes.spm_cate'] = $moduleTemplate + array(
			'scripts' => array( 'scripts/spm_wf_input.js' ),
			'dependencies' => array( 'ext.jquery.fancybox' )
		);

		SPMWidgetDataTypeUtils::initialize();
		SPMWidgetViewUtils::initialize();
		SPMWidgetExtraUtils::initialize();

		foreach ( SPMWidgetDataTypeUtils::$datatypes as $dt ) {
			$dt->registerResourceModules();
		}
		foreach ( SPMWidgetViewUtils::$views as $v ) {
			$v->registerResourceModules();
		}
		foreach ( SPMWidgetExtraUtils::$extras as $e ) {
			$e->registerResourceModules();
		}
	}
}
