<?php

namespace Outer
{

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
				$sum = $target;
			} else {
				$sum = $args[1][0];
			}

			return "include [string(" . $sum . ")] result";
		}

		function test_func1() {
			return "test function";
		}

		function test_func2() {
			return 123.45;
		}

		function test_func3() {
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

	}

	class HistoneNumber {

		function test_func1() {
			return "test number function";
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
			return "test function (string(" . $sum . ")) eof";
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