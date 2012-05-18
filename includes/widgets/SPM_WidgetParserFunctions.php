<?php

class SPMWidgetParserFunctions {

	static function registerFunctions( &$parser ) {
		$parser->setFunctionHook( 'wfexp', array( 'SPMWidgetParserFunctions', 'renderWidgetExpression' ) );
		$parser->setFunctionHook( 'wfinput', array( 'SPMWidgetParserFunctions', 'renderWidgetInput' ) );
		$parser->setFunctionHook( 'wfupdate', array( 'SPMWidgetParserFunctions', 'renderWidgetUpdate' ) );
		$parser->setFunctionHook( 'wfinputurl', array( 'SPMWidgetParserFunctions', 'renderWidgetInputUrl' ) );
		$parser->setFunctionHook( 'wfallowsvalue', array( 'SPMWidgetParserFunctions', 'renderAllowsValue' ) );
//		$parser->setFunctionHook( 'wfwidget', array( 'SPMWidgetParserFunctions', 'renderWidget' ) );

		// the following code copied from SemanticForms extension, in case SemanticForms extension is not deployed
		if ( !defined( 'SF_VERSION' ) ) {
			if ( defined( get_class( $parser ) . '::SFH_OBJECT_ARGS' ) ) {
				$parser->setFunctionHook( 'arraymap', array( 'SPMWidgetParserFunctions', 'renderArrayMapObj' ), SFH_OBJECT_ARGS );
				$parser->setFunctionHook( 'arraymaptemplate', array( 'SPMWidgetParserFunctions', 'renderArrayMapTemplateObj' ), SFH_OBJECT_ARGS );
			} else {
				$parser->setFunctionHook( 'arraymap', array( 'SPMWidgetParserFunctions', 'renderArrayMap' ) );
				$parser->setFunctionHook( 'arraymaptemplate', array( 'SPMWidgetParserFunctions', 'renderArrayMapTemplate' ) );
			}
		}

		return true;
	}

	// FIXME: Can be removed when new style magic words are used (introduced in r52503)
	static function languageGetMagic( &$magicWords, $langCode = "en" ) {
		switch ( $langCode ) {
		default:
			$magicWords['wfexp'] = array ( 0, 'wfexp' );
			$magicWords['wfinput'] = array ( 0, 'wfinput' );
			$magicWords['wfupdate'] = array ( 0, 'wfupdate' );
			$magicWords['wfinputurl'] = array ( 0, 'wfinputurl' );
			$magicWords['wfallowsvalue'] = array ( 0, 'wfallowsvalue' );

			if ( !defined( 'SF_VERSION' ) ) {
				$magicWords['arraymap']	= array ( 0, 'arraymap' );
				$magicWords['arraymaptemplate'] = array ( 0, 'arraymaptemplate' );
			}
		}
		return true;
	}

	static $connectorExpressions = array();
	static function reset() {
		SPMWidgetParserFunctions::$connectorExpressions = array();
	}
	static function renderWidgetExpression ( &$parser ) {
		$params = func_get_args();
		array_shift( $params ); // don't need the parser

		$target = array_shift( $params );
		$src = array();
		$exp = '';
		while ( !empty( $params ) ) {
			$s = array_shift( $params );
			if ( preg_match( '/^\s*(' . $parser->uniqPrefix() . '.+' . Parser::MARKER_SUFFIX . ')\s*$/', $s, $m ) ) {
				$nowiki = $parser->mStripState->nowiki->getArray();
				if ( isset( $nowiki[$m[1]] ) ) $exp .= $nowiki[$m[1]];
				break;
			}
			$src[] = $s;
		}
		self::$connectorExpressions[] = array(
			'target' => $target,
			'src' => $src,
			'exp' => $exp
		);

		return array( '', 'noparse' => false, 'isHTML' => false );
	}

	static $input_index = 0;
	private static function applyInputJs() {
		global $wgOut, $wgSPMScriptPath;

		// MediaWiki 1.17 introduces the Resource Loader.
		$realFunction = array( 'SMWOutputs', 'requireResource' );
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
			$wgOut->addModules( 'ext.wes.spm_cate' );
		} else {
			$wgOut->addLink( array(
					'rel'   => 'stylesheet',
					'type'  => 'text/css',
					'media' => 'screen, projection',
					'href'  => $wgSPMScriptPath . '/scripts/fancybox/jquery.fancybox-1.3.4.css'
				) );

			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/jquery-1.4.3.min.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/fancybox/jquery.fancybox-1.3.4.pack.js"></script>' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $wgSPMScriptPath . '/scripts/spm_wf_input.js"></script>' . "\n" );
		}

		$link = Title::newFromText( "Special:WidgetAssembler" );
		$wgOut->addScript( '
<script type="text/javascript">
spm_wf_input.url = "' . str_replace( '"' , '\"', $link->getFullUrl() ) . '";
</script>
' );

	}

	static function renderWidgetInput ( &$parser ) {
		$params = func_get_args();
		array_shift( $params ); // don't need the parser

		$widgetName = $defaultValue = $field_value = '';
		$buttonStr = 'Create or edit';
		$inputSize = 25;
		// assign params - support unlabelled params, for backwards compatibility
		foreach ( $params as $i => $param ) {
			$elements = explode( '=', $param, 2 );
			$param_name = $i;
			if ( count( $elements ) > 1 ) {
				$param_name = trim( $elements[0] );
				$value = trim( $elements[1] );
			} else {
				$value = trim( $param );
			}
			if ( $value == '' ) continue;
			if ( $param_name == 'name' )
				$widgetName = $value;
			elseif ( $param_name == 'default' )
				$defaultValue = $value;
			elseif ( $param_name == 'button text' )
				$buttonStr = $value;
			elseif ( $param_name == 'size' )
			{ if ( intval( $value ) >= 0 ) $inputSize = intval( $value ); }
			elseif ( $param_name == 'value' )
				$field_value = $value;
			elseif ( $i == 0 )
				$widgetName = $value;
			elseif ( $i == 1 )
			{ if ( intval( $value ) >= 0 ) $inputSize = intval( $value ); }
			elseif ( $i == 2 )
				$defaultValue = $value;
			elseif ( $i == 3 )
				$buttonStr = $value;
		}
		++ self::$input_index;
		if ( self::$input_index == 1 ) {
			$parser->disableCache();
			self::applyInputJs();
		}

		// FIXME: hard code for UID support
		if ( strpos( $defaultValue, '___UID=' ) === 0 ) {
			$prefix = substr( $defaultValue, strlen( '___UID=' ) );
			$defaultValue = SPMWidgetUidType::getId( $prefix );
		}
		$type = ( $inputSize == 0 ) ? "hidden" : "text";
		$str = '
<input id="spm_wf_new_title_' . self::$input_index . '" type="' . $type . '" value="' . str_replace( '"', '\"', $defaultValue ) . '" size="' . $inputSize . '"/><a id="spm_wf_new_' . self::$input_index . '" style="cursor:pointer">' . htmlspecialchars( $buttonStr ) . '</a>
';
		$javascript_text = '
<script type="text/javascript">
/*<![CDATA[*/
spm_wf_input.objs.push({
	id:' . self::$input_index . ',
	widget:"' . str_replace( '"', '\"', $widgetName ) . '",
	value:"' . str_replace( '"', '\"', $field_value ) . '",
	params:{}});
/*]]>*/</script>
';
		global $wgOut;
		$wgOut->addScript( $javascript_text );

		// hack to remove newline from beginning of output, thanks to
		// http://jimbojw.com/wiki/index.php?title=Raw_HTML_Output_from_a_MediaWiki_Parser_Function
		return $parser->insertStripItem( trim( $str ), $parser->mStripState );
	}

	static function renderWidgetInputUrl ( &$parser ) {
		$params = func_get_args();
		array_shift( $params ); // don't need the parser

		$widgetName = $defaultValue = $field_value = '';
		// assign params - support unlabelled params, for backwards compatibility
		foreach ( $params as $i => $param ) {
			$elements = explode( '=', $param, 2 );
			$param_name = $i;
			if ( count( $elements ) > 1 ) {
				$param_name = trim( $elements[0] );
				$value = trim( $elements[1] );
			} else {
				$value = trim( $param );
			}
			if ( $value == '' ) continue;
			if ( $param_name == 'name' )
				$widgetName = $value;
			elseif ( $param_name == 'default' )
				$defaultValue = $value;
			elseif ( $param_name == 'value' )
				$field_value = $value;
			elseif ( $i == 0 )
				$widgetName = $value;
			elseif ( $i == 1 )
				$defaultValue = $value;
		}

		// FIXME: hard code for UID support
		if ( strpos( $defaultValue, '___UID=' ) === 0 ) {
			$prefix = substr( $defaultValue, strlen( '___UID=' ) );
			$defaultValue = SPMWidgetUidType::getId( $prefix );
		}

		$link = Title::newFromText( "Special:WidgetAssembler" );
		$url = $link->getFullUrl();
		$rand = mt_rand();
		$url .= "?spm_w={$widgetName}&spm_t={$defaultValue}&{$field_value}&{$rand}";
		return array( $url, 'noparse' => true );
	}

	static function renderWidgetUpdate ( &$parser ) {
		$params = func_get_args();
		array_shift( $params ); // don't need the parser

		global $wgTitle;
		$defaultValue = $wgTitle->getPrefixedText();
		$widgetName = $field_value = $extra = '';
		$buttonStr = 'Create or edit';
		// assign params - support unlabelled params, for backwards compatibility
		foreach ( $params as $i => $param ) {
			$elements = explode( '=', $param, 2 );
			$param_name = $i;
			if ( count( $elements ) > 1 ) {
				$param_name = trim( $elements[0] );
				$value = trim( $elements[1] );
			} else {
				$value = trim( $param );
			}
			if ( $value == '' ) continue;
			if ( $param_name == 'name' )
				$widgetName = $value;
			elseif ( $param_name == 'button text' )
				$buttonStr = $value;
			elseif ( $param_name == 'value' )
				$field_value = $value;
			elseif ( $param_name == 'extra' )
				$extra = $value;
			elseif ( $i == 0 )
				$widgetName = $value;
			elseif ( $i == 1 )
				$buttonStr = $value;
		}
		++ self::$input_index;
		if ( self::$input_index == 1 ) {
			$parser->disableCache();
			self::applyInputJs();
		}

		$str = '
<input id="spm_wf_new_title_' . self::$input_index . '" type=hidden value="' . str_replace( '"', '\"', $defaultValue ) . '"/>
<a id="spm_wf_new_' . self::$input_index . '" style="cursor:pointer">' . htmlspecialchars( $buttonStr ) . '</a>
';
		$javascript_text = '
<script type="text/javascript">
/*<![CDATA[*/
spm_wf_input.objs.push({
	id:' . self::$input_index . ',
	widget:"' . str_replace( '"', '\"', $widgetName ) . '",
	value:"' . str_replace( '"', '\"', $field_value ) . '",
	params:"' . str_replace( '"', '\"', $extra ) . '"});
/*]]>*/</script>
';
		global $wgOut;
		$wgOut->addScript( $javascript_text );

		// hack to remove newline from beginning of output, thanks to
		// http://jimbojw.com/wiki/index.php?title=Raw_HTML_Output_from_a_MediaWiki_Parser_Function
		return $parser->insertStripItem( $str, $parser->mStripState );
	}

	// FIXME: use MW cache + SMWNotifyMe instead
	static function renderAllowsValue ( &$parser ) {
		$title = $parser->getTitle();

		$params = func_get_args();
		array_shift( $params ); // don't need the parser

		$str = '';
		$allows_values = array();
		global $wgParser, $smwgQDefaultNamespaces, $smwgQFeatures;
		$options = new ParserOptions;
		foreach ( $params as $param ) {
			if ( trim( $param ) == '' ) continue;
			if ( preg_match( '/^\s*\[\[/', $param ) ) {
				/**
				 * special property
				 * We can have individual pages or queries to be allowed values.
				 * Note:
				 * 1. The examples are disjunctive (OR)
				 * 2. Domain is all On Categories unless restricted by Category:=Cat1/Cat2
				 * 3. + means existence of a property
				 * 4. + {value} means the value must be set (but can have other values)
				 * 5. =={value} means it must be exactly one value set
				 * 6. >, <, >=, <= etc. means comparison calculation (for numbers at least)
				 * E.g.,
				 * [[Allowed values: Jesse Wang]]
				 * {{#wfallowsvalue: [[Skill:+PHP]][[Experience:>5]] }}
				 */

				// query, get query result
				$s = $wgParser->preprocess( $param, $parser->getTitle(), $options );
				$b = 0;
				for ( $i = 0; $i < strlen( $s ); ++$i ) {
					if ( $s { $i } == '[' ) {
						++ $b;
					} elseif ( $s { $i } == ']' ) {
						-- $b;
					} elseif ( $s { $i } == '|' ) {
						if ( $b == 0 ) break;
					}
				}
				$rawparams = array( substr( $s, 0, $i ) );
				if ( $i < strlen( $s ) ) $rawparams = array_merge( $rawparams, explode( '|', substr( $s, $i + 1 ) ) );
				SMWQueryProcessor::processFunctionParams( $rawparams, $querystring, $params, $printouts );

				$qp = new SMWQueryParser( $smwgQFeatures );
				$qp->setDefaultNamespaces( $smwgQDefaultNamespaces );
				$desc = $qp->getQueryDescription( $querystring );

				$mainlabel = array_key_exists( 'mainlabel', $params ) ? $params['mainlabel'] : '';

				if ( !$desc->isSingleton() && ( $mainlabel != '-' ) ) {
					$desc->prependPrintRequest( new SMWPrintRequest( SMWPrintRequest::PRINT_THIS, '' ) );
				}

				$query = new SMWQuery( $desc, true, false );
				$query->setQueryString( $querystring );
				$query->setExtraPrintouts( $printouts );
				$query->addErrors( $qp->getErrors() ); // keep parsing errors for later output

				$is_count = ( isset( $params['format'] ) && $params['format'] == 'count' );
				if ( $is_count ) {
					global $smwgQMaxLimit;
					$query->setOffset( 0 );
					$query->setLimit( $smwgQMaxLimit, false );
					$query->querymode = SMWQuery::MODE_COUNT;
				}
				if ( count( $qp->getErrors() ) == 0 ) {
					$res = smwfGetStore()->getQueryResult( $query );
					if ( $is_count ) {
						$allows_values[] = $res;
					} else {
						while ( $row = $res->getNext() ) {
							$field = $row[0];
							while ( ( $object = $field->getNextObject() ) !== false ) {
								$allows_values[] = $object->getShortText( SMW_OUTPUT_WIKI );
							}
						}
					}
				}
				// FIXME: hard code here
				global $smwgQMaxInlineLimit;
				$str .= '
* Allows value query: <nowiki>' . $param . '</nowiki>
{{#ask:' . $param . '
| default=n/a
| limit=' . $smwgQMaxInlineLimit . '
}}';
			} else {
				$allows_values[] = $param;
				$str .= '
* Allows value query: ' . $param;
			}
		}

		// empty resultset
		// FIXME: what if [[allows value::xxx]] in wiki text, in this case, shall not set n/a flag
		if ( count( $allows_values ) == 0 ) {
			$allows_values[] = '__n/a';
		} else {
			// save allows values here
			$smwdatatype = SMWPropertyValue::makeProperty( '___SPM_PVAL' );
			foreach ( $allows_values as $val ) {
				$str .= "[[{$smwdatatype->getWikiValue()}::{$val}| ]]";
			}
		}

		return array( $str, 'noparse' => false );
	}

//	static function renderWidget( &$parser ) {
//		$params = func_get_args();
//		array_shift( $params ); // don't need the parser
//
//		$widget_name = trim( $params[0] );
//
//
//		$widget_templates = array();
//		$tmpl_wiki = '';
//
//		return array($tmpl_wiki, 'noparse' => false);
//	}


	// copied from SemanticForms extension
	/**
	 * {{#arraymap:value|delimiter|var|formula|new_delimiter}}
	 */
	static function renderArrayMap( &$parser, $value = '', $delimiter = ',', $var = 'x', $formula = 'x', $new_delimiter = ', ' ) {
		// let '\n' represent newlines - chances that anyone will
		// actually need the '\n' literal are small
		$delimiter = str_replace( '\n', "\n", $delimiter );
		$actual_delimiter = $parser->mStripState->unstripNoWiki( $delimiter );
		$new_delimiter = str_replace( '\n', "\n", $new_delimiter );

		if ( $actual_delimiter == '' ) {
			$values_array = preg_split( '/(.)/u', $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		} else {
			$values_array = explode( $actual_delimiter, $value );
		}

		$results = array();
		foreach ( $values_array as $cur_value ) {
			$cur_value = trim( $cur_value );
			// ignore a value if it's null
			if ( $cur_value != '' ) {
				// remove whitespaces
				$results[] = str_replace( $var, $cur_value, $formula );
			}
		}
		return implode( $new_delimiter, $results );
	}

	/**
	 * SFH_OBJ_ARGS
	 * {{#arraymap:value|delimiter|var|formula|new_delimiter}}
	 */
	static function renderArrayMapObj( &$parser, $frame, $args ) {
		# Set variables
		$value         = isset( $args[0] ) ? trim( $frame->expand( $args[0] ) ) : '';
		$delimiter     = isset( $args[1] ) ? trim( $frame->expand( $args[1] ) ) : ',';
		$var           = isset( $args[2] ) ? trim( $frame->expand( $args[2], PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) : 'x';
		$formula       = isset( $args[3] ) ? $args[3] : 'x';
		$new_delimiter = isset( $args[4] ) ? trim( $frame->expand( $args[4] ) ) : ', ';
		# Unstrip some
		$delimiter = $parser->mStripState->unstripNoWiki( $delimiter );
		# let '\n' represent newlines
		$delimiter = str_replace( '\n', "\n", $delimiter );
		$new_delimiter = str_replace( '\n', "\n", $new_delimiter );

		if ( $delimiter == '' ) {
			$values_array = preg_split( '/(.)/u', $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		} else {
			$values_array = explode( $delimiter, $value );
		}

		$results_array = array();
		// add results to the results array only if the old value was
		// non-null, and the new, mapped value is non-null as well.
		foreach ( $values_array as $old_value ) {
			$old_value = trim( $old_value );
			if ( $old_value == '' ) continue;
			$result_value = $frame->expand( $formula, PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES );
			$result_value  = str_replace( $var, $old_value, $result_value );
			$result_value  = $parser->preprocessToDom( $result_value, $frame->isTemplate() ? Parser::PTD_FOR_INCLUSION : 0 );
			$result_value = trim( $frame->expand( $result_value ) );
			if ( $result_value == '' ) continue;
			$results_array[] = $result_value;
		}
		return implode( $new_delimiter, $results_array );
	}

	/**
	 * {{#arraymaptemplate:value|template|delimiter|new_delimiter}}
	 */
	static function renderArrayMapTemplate( &$parser, $value = '', $template = '', $delimiter = ',', $new_delimiter = ', ' ) {
		# let '\n' represent newlines
		$delimiter = str_replace( '\n', "\n", $delimiter );
		$actual_delimiter = $parser->mStripState->unstripNoWiki( $delimiter );
		$new_delimiter = str_replace( '\n', "\n", $new_delimiter );

		if ( $actual_delimiter == '' ) {
			$values_array = preg_split( '/(.)/u', $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		} else {
			$values_array = explode( $actual_delimiter, $value );
		}

		$results = array();
		$template = trim( $template );

		foreach ( $values_array as $cur_value ) {
			$cur_value = trim( $cur_value );
			// ignore a value if it's null
			if ( $cur_value != '' ) {
				// remove whitespaces
				$results[] = '{{' . $template . '|' . $cur_value . '}}';
			}
		}

		return array( implode( $new_delimiter, $results ), 'noparse' => false, 'isHTML' => false );
	}

	/**
	 * SFH_OBJ_ARGS
	 * {{#arraymaptemplate:value|template|delimiter|new_delimiter}}
	 */
	static function renderArrayMapTemplateObj( &$parser, $frame, $args ) {
		# Set variables
		$value         = isset( $args[0] ) ? trim( $frame->expand( $args[0] ) ) : '';
		$template      = isset( $args[1] ) ? trim( $frame->expand( $args[1] ) ) : '';
		$delimiter     = isset( $args[2] ) ? trim( $frame->expand( $args[2] ) ) : ',';
		$new_delimiter = isset( $args[3] ) ? trim( $frame->expand( $args[3] ) ) : ', ';
		# Unstrip some
		$delimiter = $parser->mStripState->unstripNoWiki( $delimiter );
		# let '\n' represent newlines
		$delimiter = str_replace( '\n', "\n", $delimiter );
		$new_delimiter = str_replace( '\n', "\n", $new_delimiter );

		if ( $delimiter == '' ) {
			$values_array = preg_split( '/(.)/u', $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		} else {
			$values_array = explode( $delimiter, $value );
		}

		$results_array = array();
		foreach ( $values_array as $old_value ) {
			$old_value = trim( $old_value );
			if ( $old_value == '' ) continue;
			$bracketed_value = $frame->virtualBracketedImplode( '{{', '|', '}}',
				$template, '1=' . $old_value );
			// special handling if preprocessor class is set to
			// 'Preprocessor_Hash'
			if ( $bracketed_value instanceof PPNode_Hash_Array ) {
				$bracketed_value = $bracketed_value->value;
			}
			$results_array[] = $parser->replaceVariables(
				implode( '', $bracketed_value ), $frame );
		}
		return implode( $new_delimiter, $results_array );
	}
}
