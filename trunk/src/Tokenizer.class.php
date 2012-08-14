<?php

/**
 * class Tokenizer
 * 
 * @package HistoneClasses
 */
Class Tokenizer {

	public static $T_EOF = -1;
	public static $T_FRAGMENT = 0;
	private static $T_KIND_IGNORE = 1;
	private static $T_KIND_TOKEN = 2;
	private static $T_KIND_LITERAL = 3;
	private $lastTokenId = 0;
	private $transitions = array();
	private $tokenStrings = array();
	private $tokenDefinitions = array();
	private $inputString = '';
	private $tokenOffset = 0;
	private $tokenBuffer = array();
	private $currentToken = null;
	private $currentContext = 0;

	/**
	 * 
	 * @param array $tokens
	 * @param number $kind
	 * @param array $context
	 * @return number
	 */
	private function addTokens($tokens, $kind, $context = 0) {
		if (gettype($tokens) != 'array') {
			$tokens = array($tokens);
		}
		if (!isset($this->tokenStrings[$context])) {
			$this->tokenStrings[$context] = array();
		}
		if (!isset($this->tokenDefinitions[$context])) {
			$this->tokenDefinitions[$context] = array(
				array(), array()
			);
		}
		$this->lastTokenId++;
		array_push($this->tokenStrings[$context], '(' . join('|', $tokens) . ')');
		array_push($this->tokenDefinitions[$context][1], array($kind, $this->lastTokenId));
		return $this->lastTokenId;
	}

	/**
	 * 
	 * @return void
	 */
	private function nextToken() {
		if (!count($this->tokenBuffer))
			$this->processToken();
		$this->currentToken = array_shift($this->tokenBuffer);
		$currentType = $this->currentToken['type'];
		if (!isset($this->transitions[$currentType]))
			return;
		$newContext = $this->transitions[$currentType];
		if ($this->currentContext === $newContext)
			return;
		$this->currentContext = $newContext;
	}

	/**
	 * 
	 * @return void
	 */
	private function processToken() {
		if ($this->tokenOffset !== strlen($this->inputString)) {
			$tokenDef = $this->tokenDefinitions[$this->currentContext];
			if (preg_match(
					$tokenDef[0], $this->inputString, $matches, PREG_OFFSET_CAPTURE, $this->tokenOffset
			)) {
				$matchIndex = $matches[0][1];
				if ($textLength = $matchIndex - $this->tokenOffset) {
					array_push($this->tokenBuffer, array(
						'type' => self::$T_FRAGMENT,
						'pos' => $this->tokenOffset,
						'value' => substr($this->inputString, $this->tokenOffset, $textLength)
					));
					$this->tokenOffset += $textLength;
				}
				$textData = $matches[0][0];
				$textLength = strlen($textData);
				$this->tokenOffset = ($matchIndex + strlen($textData));
				$tokenType = count($matches) - 1;
				$tokenKind = $tokenDef[1][$tokenType - 1];
				switch ($tokenKind[0]) {
					case self::$T_KIND_TOKEN:
						array_push($this->tokenBuffer, array(
							'type' => $tokenKind[1],
							'pos' => $matchIndex,
							'value' => $textData
						));
						break;
					case self::$T_KIND_LITERAL:
						array_push($this->tokenBuffer, array(
							'type' => $tokenKind[1],
							'pos' => $matchIndex,
							'len' => $textLength
						));
						break;
					default: return $this->processToken();
				}
			} elseif ($textLength = strlen($this->inputString) - $this->tokenOffset) {
				array_push($this->tokenBuffer, array(
					'type' => self::$T_FRAGMENT,
					'pos' => $this->tokenOffset,
					'value' => substr($this->inputString, $this->tokenOffset, $textLength)
				));
				$this->tokenOffset += $textLength;
			}
		} else {
			array_push($this->tokenBuffer, array(
				'type' => self::$T_EOF,
				'value' => 'EOF',
				'pos' => strlen($this->inputString)
			));
		}
	}

	/**
	 * 
	 * @param string $input
	 * @param number $context
	 */
	public function tokenize($input, $context = 0) {
		$this->tokenOffset = 0;
		$this->tokenBuffer = array();
		$this->currentToken = null;
		$this->inputString = $input;
		$this->currentContext = $context;
		foreach ($this->tokenDefinitions as $context => $definition) {
			$this->tokenDefinitions[$context][0] = (
				'/' . join('|', $this->tokenStrings[$context]) . '/'
				);
		}
		$this->nextToken();
	}

	/**
	 * 
	 * @return string
	 */
	public function getFragment() {
		return (
			isset($this->currentToken['value']) ?
				$this->currentToken['value'] : substr(
					$this->inputString, $this->currentToken['pos'], $this->currentToken['len']
				)
			);
	}

	/**
	 * 
	 * @return number
	 */
	public function getLineNumber() {
		$pos = -1;
		$lineNumber = 1;
		$currentTokenPos = $this->currentToken['pos'];
		while (++$pos < $currentTokenPos) {
			$code = ord(substr($this->inputString, $pos, 1));
			if ($code === 10 or $code === 13)
				$lineNumber++;
		}
		return $lineNumber;
	}

	/**
	 * 
	 * @param number $token
	 * @param number $context
	 */
	public function addTransition($token, $context = 0) {
		$this->transitions[$token] = $context;
	}

	/**
	 * 
	 * @param array $tokens
	 * @param number $context
	 * @return array
	 */
	public function addToken($tokens, $context = 0) {
		return $this->addTokens($tokens, self::$T_KIND_TOKEN, $context);
	}

	/**
	 * 
	 * @param array $tokens
	 * @param number $context
	 * @return array
	 */
	public function addLiteral($tokens, $context = 0) {
		return $this->addTokens($tokens, self::$T_KIND_LITERAL, $context);
	}

	/**
	 * 
	 * @param array $tokens
	 * @param number $context
	 * @return array
	 */
	public function addIgnore($tokens, $context = 0) {
		return $this->addTokens($tokens, self::$T_KIND_IGNORE, $context);
	}

	/**
	 * 
	 * @param array $tokenType
	 * @return boolean
	 */
	public function test($tokenType = null) {
		return ($this->currentToken['type'] === $tokenType);
	}

	/**
	 * 
	 * @param array $tokenType
	 * @return number
	 */
	public function next($tokenType = null) {
		if ($tokenType === null or $this->test($tokenType)) {
			$token = $this->currentToken;
			$this->nextToken();
			return $token;
		}
	}

}

