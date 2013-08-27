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
require_once('Tokenizer.class.php');

/**
 * class ParseError
 * 
 * @package HistoneClasses
 */
class ParseError extends Exception {

	public $line = '';
	public $found = '';
	public $expected = '';

	/**
	 * 
	 * @param integer $line
	 * @param string $expected
	 * @param string $found
	 * @param string $file
	 */
	public function __construct($line, $expected, $found, $file) {
		parent::__construct();

		$this->line = $line;
		$this->expected = $expected;
		$this->found = $found;
		if ($file)
			$this->file = $file;
	}

	public function __toString() {
		return ($this->file . '(' .
			$this->line .
			') Syntax error, "' .
			$this->expected .
			'" expected but "' .
			$this->found .
			'" found');
	}

	public function getExpected() {
		return strval($this->expected);
	}

	public function getFound() {
		return strval($this->found);
	}

}

/**
 * class Parser
 * 
 * @package HistoneClasses
 */
class Parser {

	private static
		$T_CTX_TPL = 0,
		$T_CTX_EXP = 1,
		$T_CTX_CMT = 2,
		$T_CTX_LIT = 3;
	private static
		$T_BLOCK_START, $T_BLOCK_END,
		$T_COMMENT_START, $T_COMMENT_END,
		$T_LITERAL_START, $T_LITERAL_END,
		$T_IS, $T_OR, $T_AND, $T_NOT, $T_MOD, $T_NULL, $T_THIS, $T_SELF, $T_GLOBAL,
		$T_TRUE, $T_FALSE, $T_CALL, /*$T_ARRAY, $T_OBJECT,*/ $T_IF,
		$T_ELSEIF, $T_ELSE, $T_FOR, $T_IN, $T_VAR, $T_MACRO,
		$T_DOUBLE, $T_INT, $T_NOT_EQUAL, $T_LESS_OR_EQUAL,
		$T_GREATER_OR_EQUAL, $T_LESS_THAN, $T_GREATER_THAN, $T_LBRACKET,
		$T_RBRACKET, $T_LPAREN, $T_RPAREN, $T_QUERY, $T_EQUAL, $T_COLON,
		$T_COMMA, $T_DOT, $T_ADD, $T_SUB, $T_MUL, $T_DIV, $T_STRING, $T_ID,
		$T_IGNORE, $T_IMPORT;
	private static $file = null;
	private static $tokenizer = null;
	private static $escapeSequence =
		'/\\\\(t|b|n|r|f|\'|\"|\\\\|x[0-9A-F]{2}|u[0-9A-F]{4})/';
	public static
		$TN_OR = 1, $TN_AND = 2, $TN_EQUAL = 3, $TN_NOT_EQUAL = 4,
		$TN_LESS_OR_EQUAL = 5, $TN_LESS_THAN = 6, $TN_GREATER_OR_EQUAL = 7,
		$TN_GREATER_THAN = 8, $TN_ADD = 9, $TN_SUB = 10, $TN_MUL = 11,
		$TN_DIV = 12, $TN_MOD = 13, $TN_NEGATE = 14, $TN_NOT = 15,
		$TN_TRUE = 16, $TN_FALSE = 17, $TN_NULL = 100, $TN_INT = 101,
		$TN_DOUBLE = 102, $TN_STRING = 103, $TN_TERNARY = 104,
		$TN_SELECTOR = 105, $TN_CALL = 106, $TN_MAP = 107,
		$TN_STATEMENTS = 109, $TN_IMPORT = 110, $TN_IF = 1000,
		$TN_VAR = 1001, $TN_FOR = 1002, $TN_MACRO = 1003;

	/**
	 * 
	 * @return void
	 */
	private static function initialize() {
		if (self::$tokenizer)
			return;
		self::$tokenizer = new Tokenizer();
		// comment tokens
		self::$T_COMMENT_START = self::$tokenizer->addLiteral('{{\\*', self::$T_CTX_TPL);
		self::$T_COMMENT_END = self::$tokenizer->addLiteral('\\*}}', self::$T_CTX_CMT);
		// literal tokens
		self::$T_LITERAL_START = self::$tokenizer->addLiteral('{{%', self::$T_CTX_TPL);
		self::$T_LITERAL_END = self::$tokenizer->addLiteral('%}}', self::$T_CTX_LIT);
		// block tokens
		self::$T_BLOCK_START = self::$tokenizer->addLiteral('{{', self::$T_CTX_TPL);
		self::$T_BLOCK_END = self::$tokenizer->addLiteral('}}', self::$T_CTX_EXP);
		// operator tokens
		self::$T_IS = self::$tokenizer->addLiteral('is\\b', self::$T_CTX_EXP);
		self::$T_OR = self::$tokenizer->addLiteral('or\\b', self::$T_CTX_EXP);
		self::$T_AND = self::$tokenizer->addLiteral('and\\b', self::$T_CTX_EXP);
		self::$T_NOT = self::$tokenizer->addLiteral('not\\b', self::$T_CTX_EXP);
		self::$T_MOD = self::$tokenizer->addLiteral('mod\\b', self::$T_CTX_EXP);
		// statement tokens
		self::$T_IF = self::$tokenizer->addLiteral('if\\b', self::$T_CTX_EXP);
		self::$T_ELSEIF = self::$tokenizer->addLiteral('elseif\\b', self::$T_CTX_EXP);
		self::$T_ELSE = self::$tokenizer->addLiteral('else\\b', self::$T_CTX_EXP);
		self::$T_FOR = self::$tokenizer->addLiteral('for\\b', self::$T_CTX_EXP);
		self::$T_IN = self::$tokenizer->addLiteral('in\\b', self::$T_CTX_EXP);
		self::$T_VAR = self::$tokenizer->addLiteral('var\\b', self::$T_CTX_EXP);
		self::$T_MACRO = self::$tokenizer->addLiteral('macro\\b', self::$T_CTX_EXP);
		self::$T_CALL = self::$tokenizer->addLiteral('call\\b', self::$T_CTX_EXP);
		self::$T_IMPORT = self::$tokenizer->addLiteral('import\\b', self::$T_CTX_EXP);

		self::$T_THIS = self::$tokenizer->addLiteral('this\\b', self::$T_CTX_EXP);
		self::$T_SELF = self::$tokenizer->addLiteral('self\\b', self::$T_CTX_EXP);
		self::$T_GLOBAL = self::$tokenizer->addLiteral('global\\b', self::$T_CTX_EXP);

//		self::$T_ARRAY = self::$tokenizer->addLiteral('array\\b', self::$T_CTX_EXP);
//		self::$T_OBJECT = self::$tokenizer->addLiteral('object\\b', self::$T_CTX_EXP);

		self::$T_NULL = self::$tokenizer->addLiteral('null\\b', self::$T_CTX_EXP);
		self::$T_TRUE = self::$tokenizer->addLiteral('true\\b', self::$T_CTX_EXP);
		self::$T_FALSE = self::$tokenizer->addLiteral('false\\b', self::$T_CTX_EXP);

		self::$T_DOUBLE = self::$tokenizer->addToken(array(
			'(?:[0-9]*\\.)?[0-9]+[eE][\\+\\-]?[0-9]+', '[0-9]*\\.[0-9]+'
			), self::$T_CTX_EXP);
		self::$T_INT = self::$tokenizer->addToken('[0-9]+', self::$T_CTX_EXP);
		// relational operators
		self::$T_NOT_EQUAL = self::$tokenizer->addLiteral('isNot\\b', self::$T_CTX_EXP);
		self::$T_LESS_OR_EQUAL = self::$tokenizer->addLiteral('<=', self::$T_CTX_EXP);
		self::$T_GREATER_OR_EQUAL = self::$tokenizer->addLiteral('>=', self::$T_CTX_EXP);
		self::$T_LESS_THAN = self::$tokenizer->addLiteral('<', self::$T_CTX_EXP);
		self::$T_GREATER_THAN = self::$tokenizer->addLiteral('>', self::$T_CTX_EXP);
		// punctuators
		self::$T_LBRACKET = self::$tokenizer->addLiteral('\\[', self::$T_CTX_EXP);
		self::$T_RBRACKET = self::$tokenizer->addLiteral('\\]', self::$T_CTX_EXP);
		self::$T_LPAREN = self::$tokenizer->addLiteral('\\(', self::$T_CTX_EXP);
		self::$T_RPAREN = self::$tokenizer->addLiteral('\\)', self::$T_CTX_EXP);
		self::$T_QUERY = self::$tokenizer->addLiteral('\\?', self::$T_CTX_EXP);
		self::$T_EQUAL = self::$tokenizer->addLiteral('=', self::$T_CTX_EXP);
		self::$T_COLON = self::$tokenizer->addLiteral(':', self::$T_CTX_EXP);
		self::$T_COMMA = self::$tokenizer->addLiteral(',', self::$T_CTX_EXP);
		// mathematical operators
		self::$T_DOT = self::$tokenizer->addLiteral('\\.', self::$T_CTX_EXP);
		self::$T_ADD = self::$tokenizer->addLiteral('\\+', self::$T_CTX_EXP);
		self::$T_SUB = self::$tokenizer->addLiteral('\\-', self::$T_CTX_EXP);
		self::$T_MUL = self::$tokenizer->addLiteral('\\*', self::$T_CTX_EXP);
		self::$T_DIV = self::$tokenizer->addLiteral('\\/', self::$T_CTX_EXP);
		self::$T_STRING = self::$tokenizer->addToken(array(
			'\'(?:[^\'\\\\]|\\\\.)*\'', '\"(?:[^\"\\\\]|\\\\.)*\"',
			), self::$T_CTX_EXP);
		self::$T_ID = self::$tokenizer->addToken('[a-zA-Z_$][a-zA-Z0-9_$]*', self::$T_CTX_EXP);
		self::$T_IGNORE = self::$tokenizer->addIgnore('[\x09\x0A\x0D\x20]+', self::$T_CTX_EXP);
		// initialize transitions
		self::$tokenizer->addTransition(self::$T_COMMENT_START, self::$T_CTX_CMT);
		self::$tokenizer->addTransition(self::$T_COMMENT_END, self::$T_CTX_TPL);
		self::$tokenizer->addTransition(self::$T_LITERAL_START, self::$T_CTX_LIT);
		self::$tokenizer->addTransition(self::$T_LITERAL_END, self::$T_CTX_TPL);
		self::$tokenizer->addTransition(self::$T_BLOCK_START, self::$T_CTX_EXP);
		self::$tokenizer->addTransition(self::$T_BLOCK_END, self::$T_CTX_TPL);
	}

	/**
	 * 
	 * @param string $expected
	 * @param string $found
	 * @throws ParseError
	 */
	private static function syntaxError($expected, $found = null) {
		throw new ParseError(
			self::$tokenizer->getLineNumber(),
			$expected,
			$found ? $found : self::$tokenizer->getFragment(),
			self::$file
		);
	}

	/**
	 * 
	 * @param string $a
	 * @return string
	 */
	private static function pregReplaceCallback($a) {
		switch ($a[1][0]) {
			// unicode sequence (4 hex digits: dddd)
			case 'u': return chr(hexdec(substr($a[1], 1)));
			// hexadecimal sequence (2 digits: dd)
			case 'x': return chr(hexdec(substr($a[1], 1)));
			// backspace
			case 'b': return chr(8);
			// horizontal tab
			case 't': return chr(9);
			// new line
			case 'n': return chr(10);
			// form feed
			case 'f': return chr(12);
			// carriage return
			case 'r': return chr(13);
			// single quotation mark
			case '\'': return '\'';
			// double quotation mark
			case '"': return '"';
			// backslash
			case '\\': return '\\';
			// unknown sequence
			default: return $a[0];
		}
	}

	/**
	 * 
	 * @param string $string
	 * @return string
	 */
	private static function extractStringData($string) {
		return preg_replace_callback(
				self::$escapeSequence, array('Parser', 'pregReplaceCallback'), substr($string, 1, -1)
		);
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseMap() {
		$items = array();

		if (!self::$tokenizer->test(self::$T_RBRACKET)) {
			while (true) {

				$key = null;
				$value = self::parseExpression();

				if (self::$tokenizer->next(self::$T_COLON)) {

					if ($value[0] !== self::$TN_STRING &&
						$value[0] !== self::$TN_INT &&
						$value[0] !== self::$TN_SELECTOR ||
						count($value[1]) !== 1) {
						self::syntaxError('identifier, string, number');
					}

					if ($value[0] !== self::$TN_SELECTOR) {
						$key = $value[1];
					} else
						$key = $value[1][0];

					$key = strval($key);

					$value = self::parseExpression();
				}

				array_push($items, array($key, $value));

				if (!self::$tokenizer->next(self::$T_COMMA))
					break;
			}
		}


		if (!self::$tokenizer->next(self::$T_RBRACKET)) {
			self::syntaxError(']');
		}

		return array(self::$TN_MAP, $items);
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseSimpleExpression() {

		if (self::$tokenizer->next(self::$T_NULL)) {
			return array(self::$TN_NULL);
		} elseif (self::$tokenizer->next(self::$T_TRUE)) {
			return array(self::$TN_TRUE);
		} elseif (self::$tokenizer->next(self::$T_FALSE)) {
			return array(self::$TN_FALSE);
		} elseif (self::$tokenizer->test(self::$T_INT)) {
			$value = self::$tokenizer->next();
			$value = floatval($value['value']);
			return array(self::$TN_INT, $value);
		} elseif (self::$tokenizer->test(self::$T_DOUBLE)) {
			$value = self::$tokenizer->next();
			$value = floatval($value['value']);
			return array(self::$TN_DOUBLE, $value);
		} elseif (self::$tokenizer->test(self::$T_STRING)) {
			$value = self::$tokenizer->next();
			$value = self::extractStringData($value['value']);
			return array(self::$TN_STRING, $value);
		} elseif (self::$tokenizer->next(self::$T_LBRACKET)) {
			return self::parseMap();
		} elseif (self::$tokenizer->test(self::$T_ID)) {
			$value = self::$tokenizer->next();
			return array(self::$TN_SELECTOR, array($value['value']));
		} elseif (self::$tokenizer->next(self::$T_THIS)) {
			return array(self::$TN_SELECTOR, array('this'));
		} elseif (self::$tokenizer->next(self::$T_SELF)) {
			return array(self::$TN_SELECTOR, array('self'));
		} elseif (self::$tokenizer->next(self::$T_GLOBAL)) {
			return array(self::$TN_SELECTOR, array('global'));
		} elseif (self::$tokenizer->next(self::$T_LPAREN)) {
			if (self::$tokenizer->next(self::$T_RPAREN)) {
				self::syntaxError('expression', '()');
			}
			try {
				$expression = self::parseExpression();
			} catch (Exception $e) {
				
			}

			if (!self::$tokenizer->next(self::$T_RPAREN)) {
				self::syntaxError(')');
			}

			return $expression;
		}

		else
			self::syntaxError('expression');
	}

	/**
	 * 
	 * @return array
	 */
	private static function parsePrimaryExpression() {
		$left = (
			self::$tokenizer->next(self::$T_SUB) ?
				array(self::$TN_NEGATE, self::parseSimpleExpression()) :
				self::parseSimpleExpression()
			);
		while (true) {
			if (self::$tokenizer->next(self::$T_DOT)) {
				if (!self::$tokenizer->test(self::$T_ID)) {
					self::syntaxError('identifier');
				}
				if ($left[0] !== self::$TN_SELECTOR) {
					$left = array(self::$TN_SELECTOR, array($left));
				}
				$token = self::$tokenizer->next();
				array_push($left[1], $token['value']);
			} elseif (self::$tokenizer->next(self::$T_LBRACKET)) {
				if (self::$tokenizer->next(self::$T_RBRACKET)) {
					self::syntaxError('expression', '[]');
				}
				if ($left[0] !== self::$TN_SELECTOR) {
					$left = array(self::$TN_SELECTOR, array($left));
				}
				array_push($left[1], self::parseExpression());
				if (!self::$tokenizer->next(self::$T_RBRACKET)) {
					self::syntaxError(']');
				}
			} elseif (self::$tokenizer->next(self::$T_LPAREN)) {
				/* Левая часть может быть только типа self::$TN_SELECTOR  */
				if ($left[0] !== self::$TN_SELECTOR) {
					self::syntaxError('}}', '(');
				} else {
					$args = null;
					$name = null;
					$context = null;
					if (!self::$tokenizer->next(self::$T_RPAREN)) {
						$args = array();
						while (true) {
							array_push($args, self::parseExpression());
							if (!self::$tokenizer->next(self::$T_COMMA))
								break;
						}
						if (!self::$tokenizer->next(self::$T_RPAREN)) {
							self::syntaxError(')');
						}
					}
					if ($left[0] === self::$TN_SELECTOR) {
						$name = array_pop($left[1]);
						$context = (count($left[1]) ? $left : null);
					} else {
						$name = '';
						$context = $left;
					}
					$left = array(
						self::$TN_CALL,
						$context, $name, $args
					);
				}
			}
			else
				break;
		}
		return $left;
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseUnaryExpression() {
		if (self::$tokenizer->next(self::$T_NOT)) {
			return array(self::$TN_NOT, self::parseUnaryExpression());
		} else {
			self::$tokenizer->next(self::$T_ADD);
			return self::parsePrimaryExpression();
		}
	}

	/**
	 * 
	 * @return array 
	 */
	private static function parseMulExpression() {
		$left = self::parseUnaryExpression();
		while (true) {
			if (self::$tokenizer->next(self::$T_MUL)) {
				$left = array(self::$TN_MUL, $left, self::parseUnaryExpression());
			} elseif (self::$tokenizer->next(self::$T_DIV)) {
				$left = array(self::$TN_DIV, $left, self::parseUnaryExpression());
			} elseif (self::$tokenizer->next(self::$T_MOD)) {
				$left = array(self::$TN_MOD, $left, self::parseUnaryExpression());
			} else
				break;
		}
		return $left;
	}

	/**
	 * 
	 * @return array 
	 */
	private static function parseAddExpression() {
		$left = self::parseMulExpression();
		while (true) {
			if (self::$tokenizer->next(self::$T_ADD)) {
				$left = array(self::$TN_ADD, $left, self::parseMulExpression());
			} elseif (self::$tokenizer->next(self::$T_SUB)) {
				$left = array(self::$TN_SUB, $left, self::parseMulExpression());
			} else
				break;
		}
		return $left;
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseRelExpression() {
		$left = self::parseAddExpression();
		if (self::$tokenizer->next(self::$T_LESS_OR_EQUAL)) {
			$left = array(self::$TN_LESS_OR_EQUAL, $left, self::parseAddExpression());
		} elseif (self::$tokenizer->next(self::$T_LESS_THAN)) {
			$left = array(self::$TN_LESS_THAN, $left, self::parseAddExpression());
		} elseif (self::$tokenizer->next(self::$T_GREATER_OR_EQUAL)) {
			$left = array(self::$TN_GREATER_OR_EQUAL, $left, self::parseAddExpression());
		} elseif (self::$tokenizer->next(self::$T_GREATER_THAN)) {
			$left = array(self::$TN_GREATER_THAN, $left, self::parseAddExpression());
		}
		return $left;
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseEqExpression() {
		$left = self::parseRelExpression();
		while (true) {
			if (self::$tokenizer->next(self::$T_IS)) {
				$left = array(self::$TN_EQUAL, $left, self::parseRelExpression());
			} elseif (self::$tokenizer->next(self::$T_NOT_EQUAL)) {
				$left = array(self::$TN_NOT_EQUAL, $left, self::parseRelExpression());
			} else
				break;
		}
		return $left;
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseAndExpression() {
		$left = self::parseEqExpression();
		while (true) {
			if (self::$tokenizer->next(self::$T_AND)) {
				$left = array(self::$TN_AND, $left, self::parseEqExpression());
			} else
				break;
		}
		return $left;
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseOrExpression() {
		$left = self::parseAndExpression();
		while (true) {
			if (self::$tokenizer->next(self::$T_OR)) {
				$left = array(self::$TN_OR, $left, self::parseAndExpression());
			} else
				break;
		}
		return $left;
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseTernaryExpression() {
		$left = self::parseOrExpression();
		while (self::$tokenizer->next(self::$T_QUERY)) {
			$left = array(self::$TN_TERNARY, $left, self::parseExpression());
			if (self::$tokenizer->next(self::$T_COLON)) {
				array_push($left, self::parseExpression());
			}
		}
		return $left;
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseExpression() {
		return self::parseTernaryExpression();
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseVarStatement() {
		if (!self::$tokenizer->next(self::$T_VAR))
			return;
		$name = self::$tokenizer->next(self::$T_ID);
		if (!$name)
			self::syntaxError('identifier');
		$expression = null;
		if (!self::$tokenizer->next(self::$T_EQUAL)) {
			if (!self::$tokenizer->next(self::$T_BLOCK_END)) {
				self::syntaxError('}}');
			}

			$expression = array(
				self::$TN_STATEMENTS,
				self::parseStatements(self::$T_DIV)
			);

			if (!self::$tokenizer->next(self::$T_DIV) ||
				!self::$tokenizer->next(self::$T_VAR) ||
				!self::$tokenizer->next(self::$T_BLOCK_END)) {
				self::syntaxError('{{/var}}');
			}
		} else {
			$expression = self::parseExpression();
			if (!self::$tokenizer->next(self::$T_BLOCK_END)) {
				self::syntaxError('}}');
			}
		}
		return array(self::$TN_VAR, $name['value'], $expression);
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseIfStatement() {
		if (!self::$tokenizer->next(self::$T_IF))
			return;
		$conditions = array();
		$expression = null;
		$statements = null;
		while (true) {
			$expression = self::parseExpression();
			if (!self::$tokenizer->next(self::$T_BLOCK_END)) {
				self::syntaxError('}}');
			}
			$statements = self::parseStatements(
					self::$T_DIV, self::$T_ELSE, self::$T_ELSEIF
			);
			array_push($conditions, array($expression, $statements));
			if (!self::$tokenizer->next(self::$T_ELSEIF))
				break;
		}

		if (self::$tokenizer->next(self::$T_ELSE)) {
			if (!self::$tokenizer->next(self::$T_BLOCK_END)) {
				self::syntaxError('}}');
			}
			$statements = self::parseStatements(self::$T_DIV);
			array_push($conditions, array(array(self::$TN_TRUE), $statements));
		}

		if (!self::$tokenizer->next(self::$T_DIV) ||
			!self::$tokenizer->next(self::$T_IF) ||
			!self::$tokenizer->next(self::$T_BLOCK_END)) {
			self::syntaxError('{{/if}}');
		}

		return array(self::$TN_IF, $conditions);
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseForStatement() {
		if (!self::$tokenizer->next(self::$T_FOR))
			return;
		$iterator = self::$tokenizer->next(self::$T_ID);
		if (!$iterator)
			self::syntaxError('identifier');
		$iterator = array($iterator['value']);

		if (self::$tokenizer->next(self::$T_COLON)) {
			$key = self::$tokenizer->next(self::$T_ID);
			if (!$key)
				self::syntaxError('identifier');
			array_unshift($iterator, $key['value']);
		}

		if (!self::$tokenizer->next(self::$T_IN)) {
			self::syntaxError('in');
		}
		$expression = self::parseExpression();
		if (!self::$tokenizer->next(self::$T_BLOCK_END)) {
			self::syntaxError('}}');
		}

		$statements = array(self::parseStatements(self::$T_DIV, self::$T_ELSE));
		if (self::$tokenizer->next(self::$T_ELSE)) {
			if (!self::$tokenizer->next(self::$T_BLOCK_END)) {
				self::syntaxError('}}');
			}
			array_push($statements, self::parseStatements(self::$T_DIV));
		}

		if (!self::$tokenizer->next(self::$T_DIV) ||
			!self::$tokenizer->next(self::$T_FOR) ||
			!self::$tokenizer->next(self::$T_BLOCK_END)) {
			self::syntaxError('{{/for}}');
		}

		return array(self::$TN_FOR, $iterator, $expression, $statements);
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseMacroStatement() {
		if (!self::$tokenizer->next(self::$T_MACRO))
			return;
		$name = self::$tokenizer->next(self::$T_ID);
		if (!$name)
			self::syntaxError('identifier');
		$args = array();
		if (self::$tokenizer->next(self::$T_LPAREN)) {
			if (!self::$tokenizer->next(self::$T_RPAREN)) {
				while (true) {
					$arg = self::$tokenizer->next(self::$T_ID);
					if (!$arg)
						self::syntaxError('identifier');
					array_push($args, $arg['value']);
					if (!self::$tokenizer->next(self::$T_COMMA))
						break;
				}
				if (!self::$tokenizer->next(self::$T_RPAREN)) {
					self::syntaxError(')');
				}
			}
		}

		if (!self::$tokenizer->next(self::$T_BLOCK_END)) {
			self::syntaxError('}}');
		}

		$statements = self::parseStatements(self::$T_DIV);

		if (!self::$tokenizer->next(self::$T_DIV) ||
			!self::$tokenizer->next(self::$T_MACRO) ||
			!self::$tokenizer->next(self::$T_BLOCK_END)) {
			self::syntaxError('{{/macro}}');
		}

		return array(self::$TN_MACRO, $name['value'], $args, $statements);
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseCallStatement() {
		if (!self::$tokenizer->next(self::$T_CALL))
			return;
		$name = self::$tokenizer->next(self::$T_ID);
		if (!$name)
			self::syntaxError('identifier');

		$args = array();
		if (self::$tokenizer->next(self::$T_LPAREN)) {
			if (!self::$tokenizer->next(self::$T_RPAREN)) {
				while (true) {
					array_push($args, self::parseExpression());
					if (!self::$tokenizer->next(self::$T_COMMA))
						break;
				}
				if (!self::$tokenizer->next(self::$T_RPAREN)) {
					self::syntaxError(')');
				}
			}
		}
		if (!self::$tokenizer->next(self::$T_BLOCK_END)) {
			self::syntaxError('}}');
		}

		array_push($args, array(
			self::$TN_STATEMENTS,
			self::parseStatements(self::$T_DIV)
		));

		if (!self::$tokenizer->next(self::$T_DIV) ||
			!self::$tokenizer->next(self::$T_CALL) ||
			!self::$tokenizer->next(self::$T_BLOCK_END)) {
			self::syntaxError('{{/call}}');
		}

		return array(self::$TN_CALL, null, $name['value'], $args);
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseImportStatement() {
		if (!self::$tokenizer->next(self::$T_IMPORT))
			return;
		$pathToken = self::$tokenizer->next(self::$T_STRING);
		if (!$pathToken) {
			self::syntaxError('string');
		}
		if (!self::$tokenizer->next(self::$T_BLOCK_END)) {
			self::syntaxError('}}');
		}
		return array(self::$TN_IMPORT, preg_replace("/\'|\"/", '', $pathToken['value']));
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseExpressionStatement() {
		$expression = self::parseExpression();
		if (!self::$tokenizer->next(self::$T_BLOCK_END)) {
			self::syntaxError('}}');
		}
		return $expression;
	}

	/**
	 * 
	 * @return array
	 */
	private static function parseStatements() {
		$statements = array();
		$statement = null;
		while (!self::$tokenizer->next(Tokenizer::$T_EOF)) {
			// skip comments
			while (self::$tokenizer->next(self::$T_COMMENT_START)) {
				while (!self::$tokenizer->test(self::$T_COMMENT_END) &&
				!self::$tokenizer->next(Tokenizer::$T_EOF)) {
					self::$tokenizer->next();
				}
				if (!self::$tokenizer->next(self::$T_COMMENT_END)) {
					self::syntaxError('*}}');
				}
			}

			// parse literals
			while (self::$tokenizer->next(self::$T_LITERAL_START)) {
				$literalStr = '';
				while (!self::$tokenizer->test(self::$T_LITERAL_END) &&
				!self::$tokenizer->next(Tokenizer::$T_EOF)) {
					$token = self::$tokenizer->next();
					$literalStr .= $token['value'];
				}
				if (!self::$tokenizer->next(self::$T_LITERAL_END)) {
					self::syntaxError('%}}');
				}
				array_push($statements, $literalStr);
			}

			// parse instructions
			if (self::$tokenizer->next(self::$T_BLOCK_START)) {
				// break on following tokens
				$excludes = func_get_args();
				if (count($excludes)) {
					$isExcluded = false;
					while (count($excludes)) {
						if (self::$tokenizer->test(array_shift($excludes))) {
							$isExcluded = true;
							break;
						}
					}
					if ($isExcluded)
						break;
				}
				// skip empty instructions
				if (self::$tokenizer->next(self::$T_BLOCK_END))
					continue;

				// parse statements
				$statement = self::parseIfStatement();
				if (!$statement)
					$statement = self::parseForStatement();
				if (!$statement)
					$statement = self::parseVarStatement();
				if (!$statement)
					$statement = self::parseMacroStatement();
				if (!$statement)
					$statement = self::parseCallStatement();
				if (!$statement)
					$statement = self::parseImportStatement();
				if (!$statement)
					$statement = self::parseExpressionStatement();

				array_push($statements, $statement);
			}

			// parse text fragments
			elseif (self::$tokenizer->test(Tokenizer::$T_FRAGMENT)) {
				$token = self::$tokenizer->next();
				array_push($statements, $token['value']);
			}
		}
		return $statements;
	}

	/**
	 * 
	 * @param string $input
	 * @param string $file
	 * @return string
	 */
	public static function parse($input, $file = null) {
		self::initialize();
		self::$file = $file;
		self::$tokenizer->tokenize($input);
		$f = self::parseStatements();

		return $f;
	}

}

