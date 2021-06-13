<?php
/*
@package: Magma PHP Template Engine
@author: Sören Meier <info@s-me.ch>
@version: 0.1.1 <2019-08-23>
*/

namespace MagmaTemplate;

use DateTime;

class Helper {

	public static function between( int $a, int $b ) {
		$ar = [];

		$up = $a < $b;
		$add = $up ? 1 : -1;

		for ( $i = $a; ( $up ? $i < $b : $i > $b ); $i += $add )
			$ar[] = $i;

		return $ar;
	}

	public static function repeat( int $num, $str ) {
		return str_repeat( $str, $num );
	}

	public static function esc( string $str ) {
		return htmlspecialchars( $str, ENT_HTML5 | ENT_QUOTES, 'UTF-8' );
	}

	public static function has( $d = null ) {

		if ( is_string( $d ) )
			return str_len( $d ) > 0;
		else if ( is_array( $d ) )
			return count( $d ) > 0;

		return !is_null( $d );

	}

	public static function join( array $ar, $char = '' ) {
		return implode( $char, $ar );
	}

	/**
     * ToDo: Untested draft
	 * Sort array by field(s) and order
     * @param &$array
     * @param $order
     * @param $fields
     * @param $ignorecase
	 */
	public static function sort(array &$array, $order = 'asc', $fields = null, $ignorecase = false) {
        if (!is_array($array)) $array = array($array);

        if ($order == null) {
            return;
        } else if ($order == 'rand') {
            shuffle($array);
        } else if ($fields == null) {
            switch ($order) {
                case 'asc':
                    sort($array);
                    break;
                case 'desc':
                    rsort($array);
                    break;
            }
        } else {
            if (!is_array($fields)) $fields = array($fields);

            switch ($order) {
                case 'asc':
                    usort($array, build_sorter($fields, true, $ignorecase));
                    break;
                case 'desc':
                    usort($array, build_sorter($fields, false, $ignorecase));
                    break;
            }
        }
    }
}

/**
 * ToDo: Untested draft
 * @param $keys
 * @param $flip
 * @param bool $ignorecase
 * returns a function comparing 2 entries
 * @return \Closure
 */
function build_sorter($keys, $flip, $ignorecase = false) {
    return function ($a, $b) use ($keys, $flip, $ignorecase) {
        $count = 0;

        foreach ( $keys as $key ) {
            $keyDepth = explode('.', $keys, 2);

            switch (count ($keyDepth)) {
                // could add more or dynamic depth
                case 1:
                    if (array_key_exists($keyDepth[0], $a)) {
                        if (array_key_exists($keyDepth[1], $keyDepth[0])) {
                            $a = $a[$keyDepth[0]][$keyDepth[1]];
                            $b = $b[$keyDepth[0]][$keyDepth[1]];
                            break;
                        } else if (method_exists($keyDepth[0], $keyDepth[1])) {
                            $a = $a[$keyDepth[0]]->$keyDepth[1];
                            $b = $b[$keyDepth[0]]->$keyDepth[1];
                            break;
                        }
                    } else if (method_exists($a, $keyDepth[0])) {
                        if (method_exists($keyDepth[0], $keyDepth[1])) {
                            $a = $a->$keyDepth[0]->$keyDepth[1];
                            $b = $b->$keyDepth[0]->$keyDepth[1];
                            break;
                        } else if (array_key_exists($keyDepth[1], $keyDepth[0])) {
                            $a = $a->$keyDepth[0][$keyDepth[1]];
                            $b = $b->$keyDepth[0][$keyDepth[1]];
                            break;
                        }
                    }
                case 0:
                    if (array_key_exists($keyDepth[0], $a)) {
                        $a = $a[$keyDepth[0]];
                        $b = $b[$keyDepth[0]];
                    } else if (method_exists($a, $keyDepth[0])) {
                        $a = $a->$keyDepth[0];
                        $b = $b->$keyDepth[0];
                    }
            }

            if ($a instanceof DateTime && $b instanceof DateTime) {
                $a = $a->format("U");
                $b = $b->format("U");
            }
            if (!is_string($a) && is_string(strval($a))) {
                $a = strval($a);
            }
            if (!is_string($b) && is_string(strval($b))) {
                $b = strval($b);
            }

            $ignore = is_array($ignorecase) ? $ignorecase[$count] : $ignorecase;
            $result = $ignore ? strnatcasecmp ($a, $b) : strnatcmp($a, $b);
            $flipOrder = is_array($flip) ? $flip[$count] : $flip;
            $result = $flipOrder ? ($result * -1) : $result;

            if ( $result != 0 ) break;

            $count++;
        }

        return $result;
    };
}