<?php
/**
 * This file contains a static class for accessing functions for
 * article utilities.
 *
 * @author dch
 */

class SPMArticleUtils {
	private static function parsePageTemplate( $text, &$offset ) {
		++ $offset;
		$start = $offset;
		$curly_brackets = 1;
		$square_brackets = 0;
		$len = strlen( $text );
		$group = array();
		do {
			$min = $len;
			$type = FALSE;
			$idx_nowiki = stripos( $text, '<nowiki>', $offset );
			if ( $idx_nowiki !== FALSE ) {
				$min = $idx_nowiki;
				$type = 1;
			}
			$idx_noinclude = stripos( $text, '<noinclude>', $offset );
			if ( $idx_noinclude !== FALSE && $idx_noinclude < $min ) {
				$min = $idx_noinclude;
				$type = 2;
			}
			for ( ; $offset < $min && $curly_brackets > 0; ++$offset ) {
				$c = $text[$offset];
				if ( $c == '[' ) ++$square_brackets;
				else if ( $c == ']' ) --$square_brackets;
				else if ( $square_brackets == 0 ) {
					if ( $c == '{' ) ++$curly_brackets;
					else if ( $c == '}' ) --$curly_brackets;
					else if ( $curly_brackets == 2 && $c == '|' ) {
						$group[] = trim( substr( $text, $start + 1, $offset - $start - 1 ) );
						$start = $offset;
					}
				}
			}
			if ( $curly_brackets == 0 ) break;
			if ( $type !== FALSE ) {
				$offset = $min;
				if ( $type == 1 ) {
					$offset = stripos( $text, '</nowiki>', $offset );
				} else if ( $type == 2 ) {
					$offset = stripos( $text, '</noinclude>', $offset );
				}
			}
		} while ( $offset < $len );
		$group[] = trim( substr( $text, $start + 1, $offset - $start - 3 ) );

		$result = array(
			'name' => trim( $group[0] )/*Title::newFromText($group[0])->getText()*/,
			'fields' => array() );
		foreach ( $group as $k => $tdata ) {
			if ( $k > 0 ) {
				$f = explode( '=', $tdata, 2 );
				if ( count( $f ) == 1 ) {
					$result['fields'][] = $f[0];
				} else {
					$result['fields'][trim( $f[0] )] = $f[1];
				}
			}
		}
		return $result;
	}

	static function parsePageTemplates( $text, $return_pf = false ) {
		$result = array();
		$start = 0;
		$offset = 0;
		$len = strlen( $text );
		do {
			$min = $len;
			$type = FALSE;
			$idx_nowiki = stripos( $text, '<nowiki>', $offset );
			if ( $idx_nowiki !== FALSE ) {
				$min = $idx_nowiki;
				$type = 1;
			}
			$idx_noinclude = stripos( $text, '<noinclude>', $offset );
			if ( $idx_noinclude !== FALSE && $idx_noinclude < $min ) {
				$min = $idx_noinclude;
				$type = 2;
			}
			$idx_triplebracket = strpos( $text, '{{{', $offset );
			$idx_doublebracket = strpos( $text, '{{', $offset );
			if ( $idx_triplebracket !== FALSE && $idx_triplebracket < $min && $idx_triplebracket === $idx_doublebracket ) {
				$min = $idx_triplebracket + 3;
				$type = 3;
			} elseif ( $idx_doublebracket !== FALSE && $idx_doublebracket < $min ) {
				$min = $idx_doublebracket;
				$type = 4;
				$result[] = substr( $text, $start, $min - $start );
				$is_parserfunc = false;
				if ( substr( $text, $min + 2, 1 ) == '#' ) {
					$is_parserfunc = true;
					$pfstart = $min;
				}

				$tmpl_start = $min;
				$template = self::parsePageTemplate( $text, $min );
				$template['raw'] = substr( $text, $tmpl_start, $min - $tmpl_start );

				$start = $min;
				if ( $is_parserfunc ^ $return_pf ) {
					$result[] = substr( $text, $pfstart, $min - $pfstart );
				} else {
					$result[] = $template;
				}
			}
			if ( $type !== FALSE ) {
				$offset = $min;
				if ( $type == 1 ) {
					$offset = stripos( $text, '</nowiki>', $offset );
				} else if ( $type == 2 ) {
					$offset = stripos( $text, '</noinclude>', $offset );
				}
			}
		} while ( $type !== FALSE );
		$result[] = substr( $text, $start, $len - $start );

		return $result;
	}

	static function parseTemplatePage( $text ) {
		$result = array();
		$start = 0;
		$offset = 0;
		$len = strlen( $text );
		do {
			$min = $len;
			$type = FALSE;
			$idx_nowiki = stripos( $text, '<nowiki>', $offset );
			if ( $idx_nowiki !== FALSE ) {
				$min = $idx_nowiki;
				$type = 1;
			}
			$idx_noinclude = stripos( $text, '<noinclude>', $offset );
			if ( $idx_noinclude !== FALSE && $idx_noinclude < $min ) {
				$min = $idx_noinclude;
				$type = 2;
			}
			$idx_triplebracket = FALSE;
			if ( preg_match( '/\{\{\{([^{}|]+)(\|([^{}]*))?\}\}\}/', $text, $m, PREG_OFFSET_CAPTURE, $offset ) ) {
				$idx_triplebracket = $m[0][1];
			}
			if ( $idx_triplebracket !== FALSE && $idx_triplebracket < $min ) {
				$min = $idx_triplebracket;
				$type = 3;
				$result[] = substr( $text, $start, $min - $start );
				$min = $idx_triplebracket + strlen( $m[0][0] );
				$result[] = array( 'field' => $m[1][0], 'default' => ( isset( $m[3] ) ? $m[3][0]:null ) );
				$start = $min;
			}
			if ( $type !== FALSE ) {
				$offset = $min;
				if ( $type == 1 ) {
					$offset = stripos( $text, '</nowiki>', $offset );
				} else if ( $type == 2 ) {
					$offset = stripos( $text, '</noinclude>', $offset );
				}
			}
		} while ( $type !== FALSE );
		$result[] = substr( $text, $start, $len - $start );

		return $result;
	}

	static function templateToWiki( $template ) {
		if ( !is_array( $template ) ) return $template;

		$text = '{{' . $template['name'] . "\n";
		foreach ( $template['fields'] as $k => $f ) {
			$text .= '|';
			if ( ( !is_int( $k ) ) || ( strpos( $f, '=' ) !== false ) ) {
				$text .= $k . '=';
			}
			$text .= $f;
		}
		$text .= '}}';

		return $text;
	}
}
