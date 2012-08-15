<?php

/**
 * Project Histone
 * 
 * @package HistoneClasses
 */
require_once('Parser.class.php');
require_once('Runtime.class.php');
require_once('CallStack.class.php');

/**
 * class SpondeError
 * 
 * @package HistoneClasses
 */
class SpondeError extends Exception {

	/**
	 * 
	 * @param string $message
	 */
	public function __construct($message) {
		parent::__construct();
		$this->message = __CLASS__ . ': ' . $message;
	}

}

/**
 * class Sponde
 * 
 * @package HistoneClasses
 */
class Sponde {

	private $baseURI = null;
	private $template = null;
	private static $uriResolver = null;

	/**
	 * @param string $baseURI
	 */
	public function __construct($baseURI = '') {
		$this->baseURI = trim($baseURI);
	}

	/**
	 * @param string $filename
	 */
	public static function registerExternalFunction($filename) {
		if (is_file($filename)) {
			$res = include_once $filename;
		} elseif (is_dir($filename)) {
			$files = scandir(trim($filename, '/\\'));
			foreach ($files as $file) {
				if (strpos($file, '.php') !== false) {
					$res = include_once $filename . '/' . $file;
				}
			}
		}
	}

	/**
	 * @param string $template
	 */
	public function parseString($template = null) {
		if ($template && is_string($template)) {
			try {
				$template = Parser::parse($template, $this->baseURI);
			} catch (ParseError $e) {
				throw $e;
			} catch (Exception $e) {
				self::internalError($e->getMessage());
			}
		} elseif (!is_array($template)) {
			$template = SpondeType::toString($template);
			self::internalError('"' . $template . '" is not a string');
		}
		$this->template = $template;
	}

	/**
	 * 
	 * @param string $fileName
	 * @return string
	 */
	public function parseFile($fileName = '') {
		if (!$fileName)
			self::internalError('filename  is empty string');

		try {
			if (is_file($this->baseURI))
				$dir = dirname($this->baseURI);
			else
				$dir = $this->baseURI;
			if (file_exists($fileName)) {
				$this->baseURI = $fileName;
				$path = $fileName;
			} elseif (file_exists(rtrim($dir, '/') . '/' . $fileName)) {
				$path = rtrim($dir, '/') . '/' . $fileName;
				$this->baseURI = $path;
			} else {
				$this->template = '';
				return;
			}
			$template = file_get_contents($path);
			if ($template) {
				$this->parseString($template);
			} else {
				$this->template = '';
			}
			return;
		} catch (ParseError $e) {
			$this->template = '';
		} catch (Exception $e) {
			$this->template = '';
		}
	}

	/**
	 * 
	 * @param string $message
	 * @throws SpondeError
	 */
	public static function internalError($message) {
		throw new SpondeError($message);
	}

	/**
	 * 
	 * @param number $value
	 * @return boolean
	 */
	private static function is_number($value) {
		return (is_int($value) || is_float($value));
	}

	/**
	 * 
	 * @param mixed $node
	 * @return string
	 */
	private static function getNodeClass($node) {
		if (is_string($node))
			return 'SpondeString';
		if (is_null($node))
			return 'SpondeNull';
		if (is_bool($node))
			return 'SpondeBoolean';
		if (self::is_number($node))
			return 'SpondeNumber';
		if (is_array($node))
			return 'SpondeMap';
		if (is_object($node))
			return get_class($node);
		return '';
	}

	/**
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	private static function nodeToBoolean($value) {
		if (is_bool($value))
			return $value;
		if (is_null($value))
			return false;
		if (self::is_number($value))
			return ($value !== 0);
		if (is_string($value))
			return (strlen($value) > 0);
		if (is_array($value))
			return true;
		return false;
	}

	/**
	 * 
	 * @param array $items
	 * @param object $stack
	 * @return array
	 */
	private static function processMap($items, $stack) {
		$result = array();
		foreach ($items as $item) {
			$key = $item[0];
			$value = self::processNode($item[1], $stack);
			if ($key === null) {
				$result[] = $value;
			} else {
				$result[$key] = $value;
			}
		}
		return $result;
	}

	/**
	 * 
	 * @param mixed $value
	 * @param object $stack
	 * @return boolean
	 */
	private static function processNot($value, $stack) {
		$value = self::processNode($value, $stack);
		return (!self::nodeToBoolean($value));
	}

	/**
	 * 
	 * @param mixed $left
	 * @param mixed $right
	 * @param object $stack
	 * @return mixed
	 */
	private static function processOr($left, $right, $stack) {
		$left = self::processNode($left, $stack);
		if (self::nodeToBoolean($left))
			return $left;
		return self::processNode($right, $stack);
	}

	/**
	 * 
	 * @param mixed $left
	 * @param mixed $right
	 * @param object $stack
	 * @return mixed
	 */
	private static function processAnd($left, $right, $stack) {
		$left = self::processNode($left, $stack);
		if (!self::nodeToBoolean($left))
			return $left;
		return self::processNode($right, $stack);
	}

	/**
	 * 
	 * @param mixed $condition
	 * @param mixed $left
	 * @param mixed $right
	 * @param object $stack
	 * @return mixed
	 */
	private static function processTernary($condition, $left, $right, $stack) {
		$condition = self::processNode($condition, $stack);
		if (self::nodeToBoolean($condition)) {
			return self::processNode($left, $stack);
		} elseif ($right) {
			return self::processNode($right, $stack);
		} else
			return new SpondeUndefined();
	}

	/**
	 * 
	 * @param mixed $left
	 * @param mixed $right
	 * @param object $stack
	 * @return boolean
	 */
	private static function processEquality($left, $right, $stack) {
		$left = self::processNode($left, $stack);
		$right = self::processNode($right, $stack);
		if (is_string($left) and self::is_number($right)) {
			if (is_numeric($left)) {
				$left = floatval($left);
			} else
				$right = (string) $right;
		} elseif (self::is_number($left) and is_string($right)) {
			if (is_numeric($right)) {
				$right = floatval($right);
			} else
				$left = (string) $left;
		}
		if (!(is_string($left) and is_string($right))) {
			if (self::is_number($left) and self::is_number($right)) {
				$left = floatval($left);
				$right = floatval($right);
			} else {
				$left = self::nodeToBoolean($left);
				$right = self::nodeToBoolean($right);
			}
		}
		return ($left === $right);
	}

	/**
	 * 
	 * @param number $nodeType
	 * @param mixed $left
	 * @param mixed $right
	 * @param object $stack
	 * @return boolean
	 */
	private static function processRelational($nodeType, $left, $right, $stack) {
		$left = self::processNode($left, $stack);
		$right = self::processNode($right, $stack);
		if (is_string($left) and self::is_number($right)) {
			if (is_numeric($left)) {
				$left = floatval($left);
			}
			else
				$right = (string) $right;
		} elseif (self::is_number($left) and is_string($right)) {
			if (is_numeric($right)) {
				$right = floatval($right);
			} else
				$left = (string) $left;
		}
		if (!(self::is_number($left) and self::is_number($right))) {
			if (is_string($left) and is_string($right)) {
				$left = strlen($left);
				$right = strlen($right);
			} else {
				$left = self::nodeToBoolean($left);
				$right = self::nodeToBoolean($right);
			}
		}
		switch ($nodeType) {
			case Parser::$TN_LESS_OR_EQUAL: return ($left <= $right);
			case Parser::$TN_LESS_THAN: return ($left < $right);
			case Parser::$TN_GREATER_OR_EQUAL: return ($left >= $right);
			case Parser::$TN_GREATER_THAN: return ($left > $right);
		}
	}

	/**
	 * 
	 * @param mixed $left
	 * @param mixed $right
	 * @param object $stack
	 * @return mixed
	 */
	private static function processAddition($left, $right, $stack) {
		$left = self::processNode($left, $stack);
		$right = self::processNode($right, $stack);
		if (!(is_string($left) or is_string($right))) {
			if (is_numeric($left) or is_numeric($right)) {
				if (is_numeric($left))
					$left = floatval($left);
				if (!self::is_number($left))
					return new SpondeUndefined();
				if (is_numeric($right))
					$right = floatval($right);
				if (!self::is_number($right))
					return new SpondeUndefined();
				return $left + $right;
			}
			if (is_array($left) && is_array($right)) {
				return array_merge($left, $right);
			}
		}
		$left = SpondeType::toString($left);
		$right = SpondeType::toString($right);
		return ($left . $right);
	}

	/**
	 * 
	 * @param number $nodeType
	 * @param mixed $left
	 * @param mixed $right
	 * @param mixed $stack
	 * @return number
	 */
	private static function processArithmetical($nodeType, $left, $right, $stack) {
		$left = self::processNode($left, $stack);
		if (is_numeric($left))
			$left = floatval($left);
		if (!self::is_number($left))
			return new SpondeUndefined();
		if ($nodeType === Parser::$TN_NEGATE)
			return (-$left);
		$right = self::processNode($right, $stack);
		if (is_numeric($right))
			$right = floatval($right);
		if (!self::is_number($right))
			return new SpondeUndefined();
		switch ($nodeType) {
			case Parser::$TN_ADD: return ($left + $right);
			case Parser::$TN_SUB: return ($left - $right);
			case Parser::$TN_MUL: return ($left * $right);
			case Parser::$TN_DIV: return ($left / $right);
			case Parser::$TN_MOD: return ($left % $right);
		}
	}

	/**
	 * 
	 * @param object $subject
	 * @param string $selector
	 * @param object $stack
	 * @return mixed
	 */
	private static function evalSelector($subject, $selector, $stack) {
		for ($c = 0; $c < sizeof($selector); $c++) {
			$prevSubj = $subject;
			$fragment = self::processNode($selector[$c], $stack);
			if (is_string($subject) && is_numeric($fragment)) {
				$length = strlen($subject);
				$index = floatval($fragment);
				if ($index < 0)
					$index = $length + $index;
				if ($index % 1 !== 0 || $index < 0 || $index >= $length) {
					return new SpondeUndefined();
				} else
					$subject = $subject[$index];
			}
			elseif (is_array($subject) && @array_key_exists($fragment, $subject)) {
				$subject = $subject[$fragment];
			} elseif (is_array($subject) && is_numeric($fragment)) {
				$length = count($subject);
				if ($fragment < 0) {
					if ($length + $fragment >= 0)
						$subject = $subject[$length + $fragment];
					else
						return new SpondeUndefined();
				}
				elseif ($fragment > 0) {
					return new SpondeUndefined();
				}
			} else if ($fragment instanceof SpondeUndefined) {
				return new SpondeUndefined();
			} else {
				$typeName = self::getNodeClass($subject);
				if (method_exists($typeName, $fragment)) {
					$subject = call_user_func(
						Array($typeName, $fragment), $prevSubj, $stack
					);
				} elseif (property_exists($typeName, $fragment)) {
					$subject = call_user_func(
						Array($typeName, $fragment), $prevSubj, $stack
					);
				}
				if (($subject instanceof SpondeUndefined) || ($subject === false))
					return new SpondeUndefined();
			}
		}
		return $subject;
	}

	/**
	 * 
	 * @param array $path
	 * @param object $stack
	 * @return mixed
	 */
	private static function processSelector($path, $stack) {
		$selector = array_slice($path, 1);
		$fragment = $path[0];
		if (!is_string($fragment)) {
			$fragment = self::processNode($fragment, $stack);
			return self::evalSelector($fragment, $selector, $stack);
		}
		if ($fragment === 'global') {
			return self::evalSelector(
					SpondeGlobal::value(), $selector, $stack
			);
		}
		if ($fragment === 'this') {
			return self::evalSelector(
					$stack->getContext(), $selector, $stack
			);
		}
		$value = $stack->getVar($fragment);
		if (!SpondeType::isUndefined($value)) {
			return self::evalSelector(
					$value, $selector, $stack
			);
		}
		if (method_exists('SpondeGlobal', $fragment) ||
			property_exists('SpondeGlobal', $fragment)) {
			return self::evalSelector(
					SpondeGlobal::value(), array_merge(array($fragment), $selector), $stack
			);
		}

		if (is_array($context = $stack->getContext()) &&
			array_key_exists($fragment, $context)) {
			return self::evalSelector(
					$context, array_merge(array($fragment), $selector), $stack
			);
		}
		return new SpondeUndefined();
	}

	/**
	 * 
	 * @param object $target
	 * @param string $name
	 * @param array $args
	 * @param object $stack
	 * @return mixed
	 */
	public static function processCall($target, $name, $args, $stack) {
		$name = self::processNode($name, $stack);
		for ($c = 0; $c < sizeof($args); $c++) {
			$args[$c] = self::processNode($args[$c], $stack);
		}
		if ($target === null) {
			if ($handler = $stack->getMacro($name)) {
				$macroArgs = $handler[0];
				$macroBody = $handler[1];
				$newBaseURI = $handler[2];
				$oldBaseURI = $stack->getBaseURI();
				$stack->save();
				$stack->setBaseURI($newBaseURI);
				$stack->putVar('self', array('arguments' => $args));
				$arity = min(sizeof($args), sizeof($macroArgs));
				for ($c = 0; $c < $arity; $c++) {
					$stack->putVar($macroArgs[$c], $args[$c]);
				}
				$result = self::processNodes($macroBody, $stack);
				$stack->setBaseURI($oldBaseURI);
				$stack->restore();
				return $result;
			}

			if (method_exists('SpondeGlobal', $name) ||
				method_exists('SpondeGlobal', $name . '_internal') ||
				property_exists('SpondeGlobal', $name) ||
				method_exists('Outer\\SpondeGlobal', $name) ||
				method_exists('Outer\\SpondeGlobal', $name . '_external')
			) {
				$target = SpondeGlobal::value();
			} else {
				return new SpondeUndefined();
			}
		} else {
			$target = self::processNode($target, $stack);
		}

		$typeName = self::getNodeClass($target);
		$typeNameOuter = 'Outer\\' . $typeName;
		if (method_exists($typeNameOuter, $name)) {
			return call_user_func(Array($typeNameOuter, $name), $target, $args, $stack);
		} elseif (method_exists($typeNameOuter, $name . '_external')) {
			return call_user_func(Array($typeNameOuter, $name . '_external'), $target, $args, $stack);
		} elseif (method_exists($typeName, $name)) {
			return call_user_func(Array($typeName, $name), $target, $args, $stack);
		} elseif (method_exists($typeName, $name . '_internal')) {
			return call_user_func(Array($typeName, $name . '_internal'), $target, $args, $stack);
		} elseif (property_exists($typeName, $name)) {
			return $typeName::$$name;
		} else {
			return new SpondeUndefined();
		}
	}

	/**
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @param object $stack
	 * @return void
	 */
	private static function processVar($name, $value, $stack) {
		$stack->save();
		$value = self::processNode($value, $stack);
		$stack->restore();
		$stack->putVar($name, $value);
		return '';
	}

	/**
	 * 
	 * @param array $conditions
	 * @param object $stack
	 * @return mixed
	 */
	private static function processIf($conditions, $stack) {
		$result = '';
		for ($c = 0; $c < count($conditions); $c++) {
			$condition = $conditions[$c];
			if (self::nodeToBoolean(self::processNode($condition[0], $stack))) {
				$stack->save();
				$result = self::processNodes($condition[1], $stack);
				$stack->restore();
				break;
			}
		}
		return $result;
	}

	/**
	 * 
	 * @param array $iterator
	 * @param array $collection
	 * @param array $statements
	 * @param object $stack
	 * @return mixed
	 */
	private static function processFor($iterator, $collection, $statements, $stack) {
		$result = '';
		$index = 0;
		$last = 0;
		$collection = self::processNode($collection, $stack);
		if (is_array($collection) && count($collection)) {
			$last = count($collection) - 1;
			foreach ($collection as $key => $value) {
				$stack->save();
				$stack->putVar($iterator[0], $collection[$key]);
				$stack->putVar('self', array('last' => $last, 'index' => $index++));
				if (array_key_exists(1, $iterator))
					$stack->putVar($iterator[1], $key);
				$result .= self::processNodes($statements[0], $stack);
				$stack->restore();
			}
		} elseif (array_key_exists(1, $statements)) {
			$stack->save();
			$result .= self::processNodes($statements[1], $stack);
			$stack->restore();
		}
		return $result;
	}

	/**
	 * 
	 * @param string $name
	 * @param array $args
	 * @param array $statements
	 * @param object $stack
	 * @return void
	 */
	private static function processMacro($name, $args, $statements, $stack) {
		$stack->putMacro($name, $args, $statements, $stack->getBaseURI());
		return '';
	}

	/**
	 * 
	 * @param string $importURI
	 * @param object $stack
	 * @return string
	 */
	public static function processImport($importURI, $stack) {
		if (!isset($stack->imports))
			$stack->imports = array();
		if (in_array($importURI, $stack->imports))
			return '';

		$stack->imports[] = $importURI;
		$uriResolver = self::getUriResolver();
		$baseURI = $stack->getBaseURI();
		if (is_callable($uriResolver)) {
			$resolve = call_user_func($uriResolver, $importURI, $baseURI);
		} else {
			$args = array($importURI, $baseURI);
			$resolve = SpondeGlobal::loadText(null, $args, $stack);
		}

		if ($resolve === null || $resolve instanceof SpondeUndefined) {
			$result = new SpondeUndefined();
		} else {
			if ($resolve == null)
				return '';
			try {
				$innerSponde = new Sponde($resolve['uri']);
				$innerSponde->parseString($resolve['data']);
				$stack->setBaseURI($resolve['uri']);
				$result = $innerSponde->processNodes($innerSponde->getTree(), $stack);
				$stack->setBaseURI($baseURI);
			} catch (ParseError $e) {
				return '';
			} catch (Exception $e) {
				return '';
			}
		}
		return '';
	}

	/**
	 * 
	 * @param array $node
	 * @param object $stack
	 * @return string
	 */
	private static function processNode($node, $stack) {
		if (!is_array($node))
			return $node;
		$nodeType = $node[0];
		switch ($nodeType) {
			case Parser::$TN_NULL: return null;
			case Parser::$TN_TRUE: return true;
			case Parser::$TN_FALSE: return false;
			case Parser::$TN_INT: return (int) $node[1];
			case Parser::$TN_DOUBLE: return (double) $node[1];
			case Parser::$TN_STRING: return (string) $node[1];
			case Parser::$TN_MAP: return self::processMap($node[1], $stack);
			case Parser::$TN_NOT:
				return self::processNot($node[1], $stack);
			case Parser::$TN_OR:
				return self::processOr($node[1], $node[2], $stack);
			case Parser::$TN_AND:
				return self::processAnd($node[1], $node[2], $stack);
			case Parser::$TN_TERNARY:
				return self::processTernary($node[1], $node[2], @$node[3], $stack);
			case Parser::$TN_EQUAL:
			case Parser::$TN_NOT_EQUAL:
				$equals = self::processEquality($node[1], $node[2], $stack);
				return ($nodeType === Parser::$TN_EQUAL ? $equals : !$equals);
			case Parser::$TN_LESS_OR_EQUAL:
			case Parser::$TN_LESS_THAN:
			case Parser::$TN_GREATER_OR_EQUAL:
			case Parser::$TN_GREATER_THAN:
				return self::processRelational($nodeType, $node[1], $node[2], $stack);
			case Parser::$TN_ADD:
				return self::processAddition($node[1], $node[2], $stack);
			case Parser::$TN_SUB:
			case Parser::$TN_MUL:
			case Parser::$TN_DIV:
			case Parser::$TN_MOD:
			case Parser::$TN_NEGATE:
				return self::processArithmetical($nodeType, $node[1], @$node[2], $stack);
			case Parser::$TN_STATEMENTS:
				return self::processNodes($node[1], $stack);
			case Parser::$TN_SELECTOR:
				return self::processSelector($node[1], $stack);
			case Parser::$TN_VAR:
				return self::processVar($node[1], $node[2], $stack);
			case Parser::$TN_IF:
				return self::processIf($node[1], $stack);
			case Parser::$TN_FOR:
				return self::processFor($node[1], $node[2], $node[3], $stack);
			case Parser::$TN_MACRO:
				return self::processMacro($node[1], $node[2], $node[3], $stack);
			case Parser::$TN_CALL:
				return self::processCall($node[1], $node[2], $node[3], $stack);
			case Parser::$TN_IMPORT:
				return self::processImport($node[1], $stack);
			default: self::internalError(
					'unsupported template instruction "' .
					SpondeType::toString($node[0]) . '"'
				);
		}
	}

	/**
	 * 
	 * @param array $nodes
	 * @param object $stack
	 * @return string
	 */
	private static function processNodes($nodes, $stack) {
//		$m = microtime(1);
		$result = '';
		$index = 0;
		$length = count($nodes);
		while ($index < $length) {
			$node = $nodes[$index++];
			if (!is_string($node)) {
				$node = self::processNode($node, $stack);
				$result .= SpondeType::toString($node);
			} else {
				$result .= $node;
			}
		}
		return $result;
	}

	/**
	 * 
	 * @return mixed (string or array)
	 */
	public function getTree() {
		return $this->template;
	}

	/**
	 * 
	 * @param array $context
	 * @return string
	 */
	public function process($context = null) {
		$stack = new CallStack($context);
		$stack->setBaseURI($this->baseURI);
		return $this->processNodes($this->template, $stack);
	}

	/**
	 * 
	 * @param string $uriResolver
	 * @return void 
	 */
	public static function setUriResolver($uriResolver) {
		self::$uriResolver = $uriResolver;
	}

	/**
	 * 
	 * @return string
	 */
	public static function getUriResolver() {
		return self::$uriResolver;
	}

}

