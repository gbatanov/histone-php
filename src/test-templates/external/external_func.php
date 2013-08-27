<?php

namespace Outer {

	if (!defined('PHP_VERSION_ID')) {
		$version = explode('.', PHP_VERSION);
		define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
	}

	class HistoneGlobal {
		function max($x, $y) {
			return 555;
		}

		function min($x, $y) {
			return 555;
		}

		function include_external() {
			$args = func_get_args();
			$target = $args[0];
			if (is_string($target)) {
				$arg = $target;
			} else {
				$arg = array($args[1][0]);
			}
			if (PHP_VERSION_ID > 50400)
				return "include " . json_encode($arg, JSON_UNESCAPED_SLASHES) . " result";
			else
				return "include " . str_replace("\/", "/", json_encode($arg)) . " result";
		}

		function test_func1() {
			return "test function";
		}

		function test_func2() {
			return 123.45;
		}

		function test_func3() {
			$args = func_get_args();
			$args = $args[1]; // $args[0] - HistoneGlobal object, $args[2] - CallStack object
			if (!$args)
				$args = array();

			$args = json_encode($args);
			return "test function " . $args . " eof";
		}

		function test_func4() {
			$args = func_get_args();
			$args = $args[1]; // $args[0] - HistoneGlobal object, $args[2] - CallStack object
			if (!$args)
				$args = array();

			$args = json_encode($args);
			return "test function " . $args . " eof";
		}

		function test_func5() {
			$args = func_get_args();
			$args = $args[1]; // $args[0] - HistoneGlobal object, $args[2] - CallStack object
			if (!$args)
				$args = array();

			$args = json_encode($args);
			return "test function " . $args . " eof";
		}

		function test_func6() {
			return 123.45;
		}

	}

	class HistoneNumber {
		function test_func1() {
			return "test function";
		}

	}

	class HistoneString {
		// return string

		function test_func1() {
			return "test function";
		}

		//return number
		function test_func2() {
			return 123.45;
		}

		// input argument - target
		function test_func3() {
			$args = func_get_args();
			$target = $args[0];
			if (is_string($target)) {
				$sum = $target;
			} else {
				$sum = $args[1][0];
			}
			return "test function \"" . $sum . "\" eof";
		}

		//input arguments - array
		function test_func4($arg) {
			$args = func_get_args();
			$sum = '';
			if (isset($args[1]) && is_array($args[1])) {
				foreach ($args[1] as $arg) {
					if (is_numeric($arg))
						$sum.= 'number(' . $arg . ')-';
					else
						$sum .= "string(" . $arg . ")-";
				}
				$sum = rtrim($sum, '-');
			}
			else
				$sum = '';
			return "test function [" . $sum . "] eof";
		}

		function test_func5($arg) {
			$args = func_get_args();
			$argRet = '[]';
			if (isset($args[1])) {
				$argRet = json_encode($args[1]);
			}
			return 'test function ' . $argRet . ' eof';
		}

		// return number
		function f1() {
			return 123.45;
		}

		//return string
		function f2() {
			return "123.45";
		}

	}

}