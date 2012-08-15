<?php
/**
 *    Copyright 2012 MegaFon
 *
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS,
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *   See the License for the specific language governing permissions and
 *    limitations under the License.
 */

/**
 * Project Histone
 * 
 * @package HistoneClasses
 */

/**
 * class SpondeType
 * 
 * @package HistoneClasses
 */
class SpondeType {

	/**
	 * 
	 * @param SpondeUndefined $value
	 * @return type
	 */
	public static function isUndefined($value) {
		return $value instanceof SpondeUndefined;
	}

	/**
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public static function isNull($value) {
		return is_null($value);
	}

	/**
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public static function isBoolean($value) {
		return is_bool($value);
	}

	/**
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public static function isNumber($value) {
		return is_int($value) or is_double($value);
	}

	/**
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public static function isString($value) {
		return is_string($value);
	}

	/**
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public static function isMap($value) {
		return is_array($value);
	}

	/**
	 * 
	 * @param mixed $value
	 * @return string
	 */
	public static function toString($value) {
		if (is_string($value))
			return $value;
		if (is_null($value))
			return 'null';
		if (is_bool($value))
			return ($value ? 'true' : 'false');
		if (is_int($value))
			return SpondeNumber::toString($value);
		if (is_double($value))
			return SpondeNumber::toString($value);
		if (is_array($value))
			return SpondeMap::toString($value);
		return '';
	}

	/**
	 * 
	 * @param mixed $value
	 * @return string
	 */
	public static function toJSON($value) {
		if ($value instanceof SpondeUndefined)
			return 'null';
		return json_encode($value);
	}

}

/**
 * class SpondeUndefined
 * 
 * @package HistoneClasses
 */
class SpondeUndefined extends SpondeType {
	
}

/**
 * class SpondeNull
 * 
 * @package HistoneClasses
 */
class SpondeNull extends SpondeType {
	
}

/**
 * class SpondeBoolean
 * 
 * @package HistoneClasses
 */
class SpondeBoolean extends SpondeType {
	
}

/**
 * class SpondeNumber
 * 
 * @package HistoneClasses
 */
class SpondeNumber extends SpondeType {

	/**
	 * 
	 * @param number $value
	 * @return number
	 */
	public static function toString($value) {
		$value = strtolower((string) (float) ($value));
		if (strpos($value, 'e') === false)
			return $value;
		$value = explode('e', $value);
		$numericPart = rtrim($value[0], '0');
		$exponentPart = $value[1];
		$numericSign = $numericPart[0];
		$exponentSign = $exponentPart[0];
		if ($numericSign === '+' || $numericSign === '-') {
			$numericPart = substr($numericPart, 1);
		} else
			$numericSign = '';
		if ($exponentSign === '+' || $exponentSign === '-') {
			$exponentPart = substr($exponentPart, 1);
		} else
			$exponentSign = '+';
		$decPos = strpos($numericPart, '.');
		if ($decPos === -1) {
			$rDecPlaces = 0;
			$lDecPlaces = strlen($numericPart);
		} else {
			$rDecPlaces = strlen(substr($numericPart, $decPos + 1));
			$lDecPlaces = strlen(substr($numericPart, 0, $decPos));
			$numericPart = str_replace('.', '', $numericPart);
		}
		if ($exponentSign === '+')
			$numZeros = $exponentPart - $rDecPlaces;
		else
			$numZeros = $exponentPart - $lDecPlaces;
		$zeros = str_pad('', $numZeros, '0');
		return (
			$exponentSign === '+' ?
				$numericSign . $numericPart . $zeros :
				$numericSign . '0.' . $zeros . $numericPart
			);
	}

	/**
	 * 
	 * @param number $value
	 * @return number
	 */
	public static function abs($value) {
		return abs($value);
	}

	/**
	 * 
	 * @param number $value
	 * @return number
	 */
	public static function floor($value) {
		return floor($value);
	}

	/**
	 * 
	 * @param number $value
	 * @return number
	 */
	public static function ceil($value) {
		return ceil($value);
	}

	/**
	 * 
	 * @param number $value
	 * @return number
	 */
	public static function round($value) {
		return round($value);
	}

	/**
	 * 
	 * @param number $value
	 * @return string
	 */
	public static function toChar($value) {
		return chr($value);
	}

	/**
	 * 
	 * @param number $value
	 * @return boolean
	 */
	public static function isInteger($value) {
		return is_int($value);
	}

	/**
	 * 
	 * @param number $value
	 * @return boolean
	 */
	public static function isFloat($value) {
		return is_double($value);
	}

}

/**
 * class SpondeString
 * 
 * @package HistoneClasses
 */
class SpondeString extends SpondeType {

	/**
	 * 
	 * @param string $value
	 * @return number
	 */
	public static function length($value) {
		return strlen($value);
	}

	/**
	 * 
	 * @param string $value
	 * @param array $args
	 * @param object $stack
	 * @return array
	 */
	public static function split($value, $args, $stack) {
		$separator = $args[0];
		if (!SpondeType::isString($separator))
			$separator = '';
		if (strlen($separator) === 0)
			return str_split($value);
		else
			return explode($separator, $value);
	}

	/**
	 * 
	 * @param string $value
	 * @param array $args
	 * @return number
	 */
	public static function charCodeAt($value, $args) {
		$index = $args[0];
		if (!is_numeric($index))
			$index = 0;
		if ($index >= 0 && $index < strlen($value)) {
			return ord($value[$index]);
		} else
			return new SpondeUndefined();
	}

	/**
	 * 
	 * @param string $value
	 * @param number $search
	 * @param number $start
	 * @return number
	 */
	public static function indexOf($value, $search = 0, $start = 0) {
		if (!is_string($search))
			return -1;
		if (strlen($search) === 0)
			return -1;
		if (!SpondeType::isNumber($start))
			$start = 0;
		$strLen = strlen($value);
		if ($start < 0)
			$start = $strLen + $start;
		if ($start < 0)
			$start = 0;
		if ($start >= $strLen)
			return -1;
		$result = strpos($value, $search, $start);
		if ($result === false)
			$result = -1;
		return $result;
	}

	/**
	 * 
	 * @param string $value
	 * @param number $search
	 * @param number $start
	 * @return number
	 */
	public static function lastIndexOf($value, $search = 0, $start = false) {
		if (strlen($value) === 0)
			return -1;
		if (!is_string($search))
			return -1;
		if (strlen($search) === 0)
			return -1;
		if (SpondeType::isNumber($start)) {
			if ($start <= 0) {
				$start = strlen($value) + $start;
				if ($start <= 0)
					return -1;
			}
			$value = substr($value, 0, $start);
		}
		$pos = strpos(strrev($value), strrev($search));
		if ($pos === false)
			return -1;
		return strlen($value) - $pos - strlen($search);
	}

	/**
	 * 
	 * @param string $value
	 * @param array $args
	 * @return string
	 */
	public static function strip($value, $args = array()) {
		$chars = '';

		while (count($args)) {
			$arg = array_shift($args);
			if (!is_string($arg))
				continue;
			$chars .= $arg;
		}
		if ($chars)
			return trim($value, $chars);
		else
			return trim($value);
	}

	/**
	 * 
	 * @param string $value
	 * @param array $args
	 * @param object $stack
	 * @return string
	 */
	public static function slice($value, $args = array(), $stack = null) {
		$strLen = strlen($value);
		$start = (isset($args[0])) ? (int) $args[0] : 0;
		$length = (isset($args[1])) ? (int) $args[1] : $strLen;

		if ($start < 0)
			$start = $strLen + $start;
		if ($start < 0)
			$start = 0;
		if ($start >= $strLen)
			return '';

		if ($length === 0)
			$length = $strLen - $start;
		if ($length < 0)
			$length = $strLen - $start + $length;
		if ($length <= 0)
			return '';
		return substr($value, $start, $length);
	}

	/**
	 * 
	 * @param mixed $value
	 * @return number
	 */
	public static function toNumber($value) {
		if (is_numeric(trim($value)))
			return floatval($value);
		return new SpondeUndefined();
	}

	/**
	 * 
	 * @param string $value
	 * @return string
	 */
	public static function toLowerCase($value) {
		return strtolower($value);
	}

	/**
	 * 
	 * @param string $value
	 * @return string
	 */
	public static function toUpperCase($value) {
		return strtoupper($value);
	}

	/**
	 * 
	 * @param string $value
	 * @param array $args
	 * @param object $stack
	 * @return boolean
	 */
	public static function test($value, $args = array(), $stack = null) {
		$regexp = (isset($args[0])) ? '/' . $args[0] . '/' : '';
		if (!$regexp)
			return true;
		return (boolean) preg_match($regexp, $value);
	}

}

/**
 * class SpondeMap
 * 
 * @package HistoneClasses
 */
class SpondeMap extends SpondeType {

	/**
	 * 
	 * @param array $value
	 * @return number
	 */
	public static function length($value) {
		return count($value);
	}

	/**
	 * 
	 * @param array $value
	 * @return string
	 */
	public static function toString($value) {
		$values = array();
		foreach ($value as $value) {
			$value = SpondeType::toString($value);
			if (strlen($value) === 0)
				continue;
			array_push($values, $value);
		}
		return implode(' ', $values);
	}

	/**
	 * 
	 * @param array $value
	 * @return string
	 */
	public static function toJSON($value) {
		if ($value instanceof SpondeUndefined)
			return 'null';

		return json_encode($value);
	}

	/**
	 * 
	 * @param array $value
	 * @param array $args
	 * @return boolean
	 */
	public static function hasIndex($value, $args) {
		$index = $args[0];
		if (!is_int($index))
			return false;
		return ($index >= 0 && $index < count($value));
	}

	/**
	 * 
	 * @param array $value
	 * @param array $args
	 * @return array
	 */
	public static function join($value, $args) {
		$separator = $args[0];
		if (!is_string($separator))
			$separator = '';
		return implode($separator, $value);
	}

	/**
	 * 
	 * @param array $value
	 * @param array $args
	 * @return array
	 */
	public static function slice($value, $args) {
		$start = (int) @$args[0];
		$length = (int) @$args[1];
		$arrLen = count($value);
		if ($start < 0)
			$start = $arrLen + $start;
		if ($start < 0)
			$start = 0;
		if ($start > $arrLen)
			return array();
		if ($length === 0)
			$length = $arrLen - $start;
		if ($length < 0)
			$length = $arrLen - $start + $length;
		if ($length <= 0)
			return array();
		return array_slice((array) $value, $start, $length);
	}

	/**
	 * 
	 * @param array $value
	 * @return array
	 */
	public static function keys($value) {
		return array_keys($value);
	}

	/**
	 * 
	 * @param array $value
	 * @return array
	 */
	public static function values($value) {
		return array_values($value);
	}

	/**
	 * 
	 * @param array $value
	 * @param array $args
	 * @return boolean
	 */
	public static function hasKey($value, $args) {
		$key = $args[0];
		return array_key_exists($key, $value);
	}

}

/**
 * class SpondeGlobal
 * 
 * @package HistoneClasses
 */
class SpondeGlobal extends SpondeType {

	private static $value = '';
	public static $WEEK_DAYS_SHORT = array(1 => 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс');
	public static $WEEK_DAYS_LONG = array(
		1 => 'Понедельник', 'Вторник', 'Среда', 'Четверг',
		'Пятница', 'Суббота', 'Воскресенье'
	);
	public static $MONTH_NAMES_SHORT = array(
		1 => 'Янв', 'Фев', 'Мар',
		'Апр', 'Май', 'Июн',
		'Июл', 'Авг', 'Сен',
		'Окт', 'Ноя', 'Дек'
	);
	public static $MONTH_NAMES_LONG = array(
		1 => 'Январь', 'Февраль', 'Март',
		'Апрель', 'Май', 'Июнь',
		'Июль', 'Август', 'Сентябрь',
		'Октябрь', 'Ноябрь', 'Декабрь'
	);

	/**
	 * 
	 * @param object $obj
	 * @param object $stack
	 * @return string
	 */
	public static function baseURI($obj, $stack) {
		return $stack->getBaseURI();
	}

	/**
	 * 
	 * @return object
	 */
	public static function value() {
		if (self::$value)
			return self::$value;
		self::$value = new SpondeGlobal();
		return self::$value;
	}

	/**
	 * 
	 * @param object $obj
	 * @param array $args
	 * @param object $stack
	 * @return string
	 */
	public static function include_internal($obj, $args, $stack) {
		$href = $args[0];
		$base = $stack->getBaseURI();
		$uriResolver = Sponde::getUriResolver();
		if ($uriResolver) {
			$resolve = @call_user_func($uriResolver, $href, $base);
			if ($x === null)
				return new SpondeUndefined();
			else {
				try {
					$innerSponde = new Sponde($resolve['uri']);
					$innerSponde->parseString($resolve['data']);
				} catch (ParseError $e) {
					return new SpondeUndefined();
				} catch (Exception $e) {
					return new SpondeUndefined();
				}
			}
		} else {
			$innerSponde = new Sponde($base);
			$innerSponde->parseFile($href);
			if (!$innerSponde->getTree())
				return new SpondeUndefined();
		}

		if (!isset($args[1])) {
			return $innerSponde->process();
		} else {
			return $innerSponde->process($args[1]);
		}
	}

	/**
	 * 
	 * @return string
	 */
	public static function uniqueId() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				// 32 bits for "time_low"
				mt_rand(0, 0xffff), mt_rand(0, 0xffff),
				// 16 bits for "time_mid"
				mt_rand(0, 0xffff),
				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 4
				mt_rand(0, 0x0fff) | 0x4000,
				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				mt_rand(0, 0x3fff) | 0x8000,
				// 48 bits for "node"
				mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	/**
	 * 
	 * @param object $obj
	 * @param array $args
	 * @return number
	 */
	public static function min($obj, $args) {
		$count = count($args);
		$minValue = new SpondeUndefined();
		for ($c = 0; $c < $count; $c++) {
			if (SpondeType::isNumber($args[$c]) and (
				SpondeType::isUndefined($minValue) or
				$args[$c] < $minValue
				))
				$minValue = $args[$c];
		}
		return $minValue;
	}

	/**
	 * 
	 * @param object $obj
	 * @param array $args
	 * @return number
	 */
	public static function max($obj, $args) {
		$count = count($args);
		$minValue = new SpondeUndefined();
		for ($c = 0; $c < $count; $c++) {
			if (SpondeType::isNumber($args[$c]) and (
				SpondeType::isUndefined($minValue) or
				$args[$c] > $minValue
				))
				$minValue = $args[$c];
		}
		return $minValue;
	}

	/**
	 * 
	 * @param array $args
	 * @param object $stack
	 * @return array
	 */
	private static function _loadFile($args, $stack) {
		$href = array_shift($args);
		$baseURI = $stack->getBaseURI();
		if (is_file($baseURI))
			$baseURI = dirname($baseURI);
		$UriResolver = Sponde::getUriResolver();
		if (is_callable($UriResolver))
			$result = call_user_func($UriResolver, $href, $baseURI, $args);
		else {
			if (file_exists($href))
				$path = $href;
			elseif (file_exists(rtrim($baseURI, '/') . '/' . $href))
				$path = (rtrim($baseURI, '/') . '/' . $href);
			else
				return null;

			$result['uri'] = $path;
			$result['data'] = file_get_contents($path);
		}
		return $result;
	}

	/**
	 * 
	 * @param object $obj
	 * @param array $args
	 * @param object $stack
	 * @return array
	 */
	public static function loadJSON($obj, $args = array(), $stack = null) {
		$result = self::_loadFile($args, $stack);
		if (isset($result['data'])) {
			$result1 = json_decode($result['data']);
			if ($result1 === null) {
				return new SpondeUndefined();
			}
			return json_decode($result['data'], true);
		}
		return new SpondeUndefined();
	}

	/**
	 * 
	 * @param object $obj
	 * @param array $args
	 * @param object $stack
	 * @return array
	 */
	public static function loadText($obj, $args, $stack = null) {
		$result = self::_loadFile($args, $stack);

		return $result === null ? new SpondeUndefined() : (($obj instanceof SpondeGlobal) ? $result['data'] : $result);
	}

	/**
	 * 
	 * @param object $obj
	 * @param array $args
	 * @return number
	 */
	public function dayOfWeek($obj, $args = array()) {
		if (!is_array($args) || count($args) < 3)
			return new SpondeUndefined();
		$y = str_pad((int) $args[0], 4, '0', STR_PAD_LEFT);
		$m = str_pad((int) $args[1], 2, '0', STR_PAD_LEFT);
		$d = str_pad((int) $args[2], 2, '0', STR_PAD_LEFT);
		if ($y < 0)
			return new SpondeUndefined();
		if ($m > 12 || $m < 1)
			return new SpondeUndefined();
		if ($d > self::daysInMonth(array($y, $m)) || $d < 1)
			return new SpondeUndefined();

		$time = strtotime("$m/$d/$y");
		$new_dt = date("m/d/Y", $time);
		if ($new_dt !== "$m/$d/$y")
			return new SpondeUndefined();
		$day = jddayofweek(GregorianToJD($m, $d, $y));
		if ($day === 0)
			$day = 7;
		return $day;
	}

	/**
	 * 
	 * @param object $obj
	 * @param array $args
	 * @return number
	 */
	public static function daysInMonth($obj, $args = array()) {
		if (!is_array($args) || count($args) < 2)
			return new SpondeUndefined();
		$y = str_pad((int) $args[0], 4, '0', STR_PAD_LEFT);
		$m = str_pad((int) $args[1], 2, '0', STR_PAD_LEFT);
		if ($y < 0)
			return new SpondeUndefined();
		if ($m > 12 || $m < 1)
			return new SpondeUndefined();
		$new_dt = cal_days_in_month(CAL_GREGORIAN, $m, $y);
		return (int) $new_dt > 0 ? (int) $new_dt : new SpondeUndefined();
	}

	/**
	 * 
	 * @param object $obj
	 * @param array $args
	 * @return array
	 */
	public static function range($obj, $args = array()) {
		if (!is_array($args) || count($args) < 2 || preg_match('/[^\d-]/', $args[0]) || preg_match('/[^\d-]/', $args[1]))
			return new SpondeUndefined();

		$first = (int) $args[0];
		$last = (int) $args[1];
		if ($first === $last)
			return array($first);
		$result = array();
		if ($first < $last) {
			while ($first <= $last)
				$result[] = $first++;
		} else {
			while ($first >= $last)
				$result[] = $first--;
		}
		return $result;
	}

	/**
	 * 
	 * @param object $obj
	 * @param array $args
	 * @return string
	 */
	public static function weekDayNameShort($obj, $args) {
		if (isset($args[0]) && isset(self::$WEEK_DAYS_SHORT[(int) $args[0]]))
			return self::$WEEK_DAYS_SHORT[(int) $args[0]];
		else
			return new SpondeUndefined();
	}

	/**
	 * 
	 * @param object $obj
	 * @param array $args
	 * @return string
	 */
	public static function weekDayNameLong($obj, $args) {
		if (isset($args[0]) && isset(self::$WEEK_DAYS_LONG[(int) $args[0]]))
			return self::$WEEK_DAYS_LONG[(int) $args[0]];
		else
			return new SpondeUndefined();
	}

	/**
	 * 
	 * @param object $obj
	 * @param array $args
	 * @return string
	 */
	public static function monthNameShort($obj, $args) {
		if (isset($args[0]) && isset(self::$MONTH_NAMES_SHORT[(int) $args[0]]))
			return self::$MONTH_NAMES_SHORT[(int) $args[0]];
		else
			return new SpondeUndefined();
	}

	/**
	 * 
	 * @param object $obj
	 * @param array $args
	 * @return string
	 */
	public static function monthNameLong($obj, $args) {
		if (isset($args[0]) && isset(self::$MONTH_NAMES_LONG[(int) $args[0]]))
			return self::$MONTH_NAMES_LONG[(int) $args[0]];
		else
			return new SpondeUndefined();
	}

	/**
	 * 
	 * @return boolean
	 */
	public static function isMap() {
		return true;
	}

}

